function partial_status_info = computeStatusInfo(all_partial_single_crop_info, models_info, communicator,jobID,db)
	
	my_rank = MPI_Comm_rank(communicator);

	crop_category_number = all_partial_single_crop_info.("CropCategoryNumber");
	
	sql = sprintf("UPDATE Image_Subfield_Status SET Subfield_Status_ID = 5 WHERE Job_ID = %d and Rank_ID = %d", jobID,my_rank);
    db.ExecuteSQL(sql);

	for crop_index = 1:crop_category_number



		disp(sprintf("COMPUTE WET STATUS INFO! Rank:%d  Category: %dth  Total Work: %d", my_rank, crop_index, crop_category_number));
	
		field_name = sprintf("Crop%dTemperatureMatrix", crop_index);

		temperature_wet = models_info.(field_name)(1,1);
		temperature_dry = models_info.(field_name)(1,2);

		field_name = sprintf("PartialCrop%dTemperatureMatrix", crop_index);
		local_crop_temp_matrix = all_partial_single_crop_info.(field_name);

		field_name = sprintf("Crop%dStatusMatrix", crop_index);
    	matrix =  (local_crop_temp_matrix - temperature_wet) / (temperature_dry - temperature_wet);
    	matrix(matrix>1.0) = 1.1;
    	partial_status_info.(field_name)  = matrix;


    	a_SWP = -3.5966;
		b_SWP = -0.1093;
		SWP_Map = matrix*a_SWP + b_SWP;

		%For deviding SWP values to 4 levels
		SWP_MapCopy = SWP_Map;
		SWP_MapCopy(SWP_MapCopy>-1) = -0.5;
		SWP_MapCopy( SWP_MapCopy<-1 & SWP_MapCopy>-2) = -1.5;
		SWP_MapCopy( SWP_MapCopy<-2 & SWP_MapCopy>-3) = -2.5;
		SWP_MapCopy( SWP_MapCopy<-3 & SWP_MapCopy>-4) = -3.5;

		field_name = sprintf("Crop%dSWPMatrix", crop_index);
		partial_status_info.(field_name) = SWP_Map;
	end

	