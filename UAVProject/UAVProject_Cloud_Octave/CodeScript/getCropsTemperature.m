function [all_partial_single_crop_info, all_single_crop_info_altered] = getCropsTemperature(all_single_crop_info, communicator,db,jobID)

	my_rank = MPI_Comm_rank(communicator);
	processor_num = MPI_Comm_size(communicator);

	crop_category_number = all_single_crop_info.("CropCategoryNumber");
	all_partial_single_crop_info.("CropCategoryNumber") = crop_category_number;

	sql = sprintf("INSERT INTO Image_Subfield_Status (Job_ID,Rank_ID,Subfield_Status_ID) VALUES (%d,%d,%d)",jobID,my_rank,2);
	db.ExecuteSQL(sql);

	for i = 1:crop_category_number
		field_name = sprintf("Crop%dMatrix", i);
		image_matrix = all_single_crop_info.(field_name);
		field_name = sprintf("Crop%dBoundaryMatrix", i);
		boundary_matrix = all_single_crop_info.(field_name);

		row_low = boundary_matrix(1,1);
		row_high = boundary_matrix(1,2);
		column_low = boundary_matrix(1,3);
		column_high = boundary_matrix(1,4);


		total_work = row_high - row_low + 1;

		chunk = floor(total_work/processor_num);
		remain_work = total_work - chunk * processor_num;

		workload_matrix = zeros(1, processor_num, "uint32");
		workload_entry_matrix = ones(1, processor_num, "uint32");

		for wordload_index = 1:processor_num
			workload_matrix(1, wordload_index) = chunk;
		end

		for remain_work_index = 1:remain_work
			workload_matrix(1, remain_work_index) += 1;
		end

		for wordload_index = 2:processor_num
			workload_entry_matrix(1, wordload_index) = workload_entry_matrix(1, wordload_index-1) + workload_matrix(1, wordload_index-1);
		end

		start_row = workload_entry_matrix(1, my_rank + 1);
		end_row = start_row + workload_matrix(1, my_rank + 1) - 1;

		partial_crop_width = column_high - column_low + 1;
		partial_crop_height = workload_matrix(1, my_rank + 1);

		field_name = sprintf("Crop%dWorkLoad", i);
		all_single_crop_info.(field_name) = workload_matrix;
		field_name = sprintf("Crop%dWorkLoadEntry", i);
		all_single_crop_info.(field_name) = workload_entry_matrix;


		partial_crop_matrix = image_matrix(start_row:end_row, :);

		disp(sprintf("EDGE PROCESSING START, my rank is %d", my_rank));




		partial_crop_edge_out_matrix = edgeDetection(partial_crop_matrix, partial_crop_width, partial_crop_height);



		disp(sprintf("EDGE PROCESSING END, my rank is %d", my_rank));

		partial_crop_temp_matrix = double((double(partial_crop_matrix) * 60.) / 65536. + 20.);
    	partial_crop_edge_out_temp_matrix = double((double(partial_crop_edge_out_matrix) * 60.) / 65536. + 20.);
    	partial_crop_edge_out_temp_matrix(partial_crop_edge_out_temp_matrix == 20) = NaN;

    	field_name = sprintf("PartialCrop%dTemperatureMatrix", i);
		all_partial_single_crop_info.(field_name) = partial_crop_temp_matrix;
    	field_name = sprintf("PartialCrop%dEdgeOutTemperatureMatrix", i);
		all_partial_single_crop_info.(field_name) = partial_crop_edge_out_temp_matrix;
		all_single_crop_info_altered = all_single_crop_info;
	end

	sql = sprintf("UPDATE Image_Subfield_Status SET Subfield_Status_ID = 3 WHERE Job_ID = %d and Rank_ID = %d",jobID, my_rank);
	db.ExecuteSQL(sql);



