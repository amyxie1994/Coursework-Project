function result_image_name = ImageProcessing(pathToImage, pathToBoundary, jobID)

    pkg load image;
    pkg load statistics;
    pkg load mpi;

    MPI_Init();
    communicator = MPI_Comm_Load("NEWWORLD");
    my_rank = MPI_Comm_rank(communicator);
    processor_num = MPI_Comm_size(communicator);


    javaaddpath ('../JavaLib/Database.jar');
	javaaddpath ('../JavaLib/mysql-connector-java-5.1.42-bin.jar');
	db = javaObject("Database","115.146.89.41",3306,"UAVProject_DB","root","1314");
    
    if(my_rank == 0)
        sql = sprintf("UPDATE Job_Info SET Core_Num = %d, Submit_Time = NOW() WHERE Job_ID = %d",processor_num,jobID);
        db.ExecuteSQL(sql); 
        sql=sprintf("UPDATE Job_Info SET Job_Status_ID = 8 WHERE Job_ID = %d",jobID);
        disp(sql);
        db.ExecuteSQL(sql);
    endif

    [image_info, all_single_crop_info, crop_category_number] = getImageInformation(pathToImage, pathToBoundary);
    disp(sprintf("Rank: %d, GET IMAGE INFORMATION DONE!", my_rank));

    if(my_rank == 0)
        sql = sprintf("UPDATE Job_Info SET Image_Width = %d, Image_Height = %d WHERE Job_ID = %d",image_info.("Width"),image_info.("Height"), jobID);
        db.ExecuteSQL(sql); 
    endif

    [all_partial_single_crop_info, all_single_crop_info]= getCropsTemperature(all_single_crop_info, communicator,db,jobID);
    disp(sprintf("Rank: %d, GET TEMPEMPERATURE DONE!", my_rank));

    MPI_Barrier(communicator);

    if(my_rank == 0)
        disp("START TO ASSEMBLE PARTIAL TEMPERATURE MATRIX!");
    endif
    all_single_crop_temp_info = assemblePartialTemperatureInfo(all_partial_single_crop_info, all_single_crop_info,communicator);

    if(my_rank == 0)
        disp("ASSEMBLE PARTIAL TEMPERATURE MATRIX DONE!");
    endif

    MPI_Barrier(communicator);


    if(my_rank == 0)
        disp("START TO COMPUTE GMM!");
    endif

    models_info = computeGMM(all_single_crop_temp_info, communicator, db, jobID);


    if (my_rank == 0)
        disp(models_info);
        TAG = 100;
        rank_vect = [1:processor_num - 1];
        for crop_index = 1:crop_category_number
            field_name = sprintf("Crop%dTemperatureMatrix", crop_index);
            temperature_matrix = models_info.(field_name);
            MPI_Send (temperature_matrix, rank_vect, TAG, communicator);
            TAG += 1;
        end
    endif

    if (my_rank != 0)
        TAG = 100;
        for crop_index = 1:crop_category_number
            [temperature_matrix, info] = MPI_Recv (0, TAG, communicator);
            field_name = sprintf("Crop%dTemperatureMatrix", crop_index);
            models_info.(field_name) = temperature_matrix;
            TAG += 1;
        end
    endif


    partial_status_info = computeStatusInfo(all_partial_single_crop_info, models_info, communicator,jobID,db);
    MPI_Barrier(communicator);
    all_status_info = assemblePartialStatusInfo(partial_status_info, all_single_crop_info, communicator);


    MPI_Barrier(communicator);

    disp("Prepare to generate result image!");
    if(my_rank != 0)
        sql = sprintf("UPDATE Image_Subfield_Status SET Subfield_Status_ID = 9 WHERE Job_ID = %d and Rank_ID = %d", jobID, my_rank);
        db.ExecuteSQL(sql);
    endif

    if (my_rank == 0)
        sql = sprintf("UPDATE Image_Subfield_Status SET Subfield_Status_ID = 6 WHERE Job_ID = %d and Rank_ID = 0", jobID);
        db.ExecuteSQL(sql);

        width = image_info.("Width");
        height = image_info.("Height");
        image_matrix = zeros(height, width, "double");
        image_matrix(image_matrix == 0) = 1.1;

        for i = 1:crop_category_number

            field_name = sprintf("Crop%dBoundaryMatrix", i);
            boundary_matrix = all_single_crop_info.(field_name);

            local_row_low     = boundary_matrix(1, 1);
            local_row_high    = boundary_matrix(1, 2);
            local_column_low  = boundary_matrix(1, 3);
            local_column_high = boundary_matrix(1, 4);

            field_name = sprintf("Crop%dStatusMatrix", i);
            matrix = all_status_info.(field_name);

            image_matrix(local_row_low:local_row_high, local_column_low:local_column_high) = matrix;

        end


        disp("Start writing image data!");

        figure;axis equal;axis image;colorbar;map = colormap(jet);result = imagesc(image_matrix);
        caxis([0,1]);
        title('Adaptive CWSI from GMM');

        names = strsplit (pathToImage, "/");
        name = names{1,length(names)};

        newFileName = strcat("result",name);
        saveas(result,newFileName,'png');
        disp("Write image data done!");
        result_image_name = newFileName;

        pathToDestination = strcat("php/result/",newFileName);
        
        sql = sprintf("UPDATE Image_Subfield_Status SET Subfield_Status_ID = 7 WHERE Job_ID = %d", jobID);
        db.ExecuteSQL(sql);

        sql=sprintf("UPDATE Job_Info SET Result_URL = '%s' WHERE Job_ID = %d", pathToDestination, jobID);
        db.ExecuteSQL(sql);

        pathToDestination = strcat("/opt/lampp/htdocs/UAVProject/php/result/",newFileName);

        sql=sprintf("UPDATE Job_Info SET Job_Status_ID = 7, Finish_Time = NOW() WHERE Job_ID = %d",jobID);
        db.ExecuteSQL(sql);
        
        sql=sprintf("UPDATE Job_Info SET Duration_Time=TIMESTAMPDIFF(SECOND, Submit_Time,Finish_Time) WHERE Job_ID = %d",jobID);
        db.ExecuteSQL(sql);
        

        command = sprintf("scp -i uavproject.key %s ubuntu@115.146.89.41:%s",newFileName,pathToDestination);
        disp(command);
        system(command);

    endif
    result_image_name = "";
    MPI_Finalize();