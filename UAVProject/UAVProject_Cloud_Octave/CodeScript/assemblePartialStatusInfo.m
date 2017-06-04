function all_status_info = assemblePartialStatusInfo(partial_status_info, all_single_crop_info,communicator)
 	my_rank = MPI_Comm_rank(communicator);
	processor_num = MPI_Comm_size(communicator);

	crop_category_number = all_single_crop_info.("CropCategoryNumber");
    for category_index = 1:crop_category_number
        field_name = sprintf("Crop%dWidth", category_index);
        width = all_single_crop_info.(field_name); 
        all_status_info.(field_name) = width;
        field_name = sprintf("Crop%dHeight", category_index);
        height = all_single_crop_info.(field_name);
        all_status_info.(field_name) = height;
       
        field_name = sprintf("Crop%dStatusMatrix", category_index);
        all_status_info.(field_name) = zeros(height, width, "double");
        field_name = sprintf("Crop%dSWPMatrix", category_index);
        all_status_info.(field_name) = zeros(height, width, "double");
        all_status_info.("CropCategoryNumber") = crop_category_number;
    end

    if (my_rank != 0)
        rankvect = 0;
        for i = 1:crop_category_number
            TAG = 100*my_rank*crop_category_number;

            field_name = sprintf("Crop%dStatusMatrix", i);
            partial_status_matrix = partial_status_info.(field_name);
            [info] = MPI_Send (partial_status_matrix, rankvect, TAG, communicator);
            TAG += 1;

            field_name = sprintf("Crop%dSWPMatrix", i);
            partial_SWP_matrix = partial_status_info.(field_name);
            [info] = MPI_Send (partial_SWP_matrix, rankvect, TAG, communicator);
        end
    endif

    if (my_rank == 0)
    	for rank_index = 0:processor_num-1
    		source_node = rank_index;
    		for category_index = 1:crop_category_number
                
                message = sprintf("ASSEMBLE PARTIAL STATUS DATA CATEGORIES:%dth RECEVING FROM PROCESS %d!", category_index, rank_index);
                disp(message);
                
                if (source_node == 0)
                    field_name = sprintf("Crop%dStatusMatrix", category_index);
                    partial_status_matrix = partial_status_info.(field_name);
                    field_name = sprintf("Crop%dSWPMatrix", category_index);
                    partial_SWP_matrix = partial_status_info.(field_name);
                else
                    TAG = 100*rank_index*crop_category_number;
    			    [partial_status_matrix, info] = MPI_Recv (source_node, TAG, communicator);
                    TAG += 1;
                    [partial_SWP_matrix, info] = MPI_Recv (source_node, TAG, communicator);

                endif

                field_name = sprintf("Crop%dWorkLoad", category_index);
                workload_matrix = all_single_crop_info.(field_name);
                field_name = sprintf("Crop%dWorkLoadEntry", category_index);
                workload_entry_matrix = all_single_crop_info.(field_name);

                start_row = workload_entry_matrix(1, rank_index + 1);
                end_row = start_row + workload_matrix(1, rank_index + 1) - 1;

                field_name = sprintf("Crop%dStatusMatrix", category_index);
                all_status_info.(field_name)(start_row:end_row, :) = partial_status_matrix;
                field_name = sprintf("Crop%dSWPMatrix", category_index);
                all_status_info.(field_name)(start_row:end_row, :) = partial_SWP_matrix;                
    		end
    	end
    endif

    %disp(sprintf("get away from assemble part, I'm rank %d",my_rank));
