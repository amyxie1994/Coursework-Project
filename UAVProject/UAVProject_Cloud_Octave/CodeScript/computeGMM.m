function models_info = computeGMM(all_single_crop_temp_info, communicator,db,jobID)
	
	my_rank = MPI_Comm_rank(communicator);
	processor_num = MPI_Comm_size(communicator);
	crop_category_num = all_single_crop_temp_info.("CropCategoryNumber");
	
	
	
	work_processor_num = processor_num;
	if (crop_category_num < processor_num)
		work_processor_num = crop_category_num;
	endif

	workload_matrix = zeros(1,work_processor_num,"uint32");
	workload_entry_matrix = ones(1,work_processor_num,"uint32");

	total_work = crop_category_num;
	chunk = floor(total_work / work_processor_num);
	remain_work = total_work - chunk * work_processor_num;


	for wordload_index = 1:work_processor_num
		workload_matrix(1, wordload_index) = chunk;
	end

	for remain_work_index = 1:remain_work
		workload_matrix(1, remain_work_index) += 1;
	end

	for wordload_index = 2:work_processor_num
		workload_entry_matrix(1, wordload_index) = workload_entry_matrix(1, wordload_index-1) + workload_matrix(1, wordload_index-1);
	end

	if (my_rank == 0)
		
		for rank_index = 1:work_processor_num-1
			TAG = 100 + workload_entry_matrix(1, rank_index + 1) - 1;
			rank_work = workload_matrix(1, rank_index + 1);
			start_crop_index = workload_entry_matrix(1, rank_index + 1);
			end_crop_index = start_crop_index + workload_matrix(1, rank_index + 1) - 1;
			for work_index = start_crop_index:end_crop_index
				field_name = sprintf("Crop%dEdgeOutTemperatureMatrx", work_index);
				crop_edge_out_temperature_matrix = all_single_crop_temp_info.(field_name);
				[info] = MPI_Send (crop_edge_out_temperature_matrix, rank_index, TAG, communicator);
				TAG += 1;
			end
		end
	endif
	
	
	
	if (my_rank < work_processor_num)

		sql = sprintf("UPDATE Image_Subfield_Status SET Subfield_Status_ID = 4 WHERE Job_ID = %d and Rank_ID = %d", jobID, my_rank);
        db.ExecuteSQL(sql);

		source_node = 0;
		TAG = 100 + workload_entry_matrix(1, my_rank + 1) - 1;
		rank_work = workload_matrix(1, my_rank + 1);
		for work_index = 1:rank_work
			if (my_rank != 0)
				[crop_edge_out_temperature_matrix, info] = MPI_Recv (source_node, TAG, communicator);
			else
				field_name = sprintf("Crop%dEdgeOutTemperatureMatrx", work_index);
				crop_edge_out_temperature_matrix = all_single_crop_temp_info.(field_name);
			endif
		
			field_name = sprintf("Crop%dEdgeOutTemperatureMatrix", work_index);
			local_crop_edge_out_info.(field_name) = crop_edge_out_temperature_matrix;
			TAG += 1;
		end

		for work_index = 1:rank_work
			field_name = sprintf("Crop%dEdgeOutTemperatureMatrix", work_index);
			local_crop_edge_out_matrix = local_crop_edge_out_info.(field_name);

			%disp(sprintf("my rank is %d, the matrix is %d * %d ", my_rank, rows(local_crop_edge_out_matrix),columns(local_crop_edge_out_matrix)));
			local_crop_edge_out_matrix_one_row = local_crop_edge_out_matrix(:);
			local_crop_edge_out_matrix_one_column = local_crop_edge_out_matrix_one_row';
			Dimension = 2;
			[mu, Sigma] = fitgmdist(local_crop_edge_out_matrix_one_column',Dimension);

			model_select = 1;
			if (mu(1,1) > mu(2,1))
				model_select = 2;
			endif

			temperature_wet = mu(model_select,1) - 2.58* sqrt(Sigma(1,1,model_select));
			temperature_dry = mu(model_select,1) + 2.58 * sqrt(Sigma(1,1,model_select));

			temperature_matrix = zeros(1,2,"double");
			temperature_matrix(1,1) = temperature_wet;
			temperature_matrix(1,2) = temperature_dry;

			crop_id = workload_entry_matrix(1, my_rank + 1) + work_index - 1;
			field_name = sprintf("Crop%dTemperatureMatrix", crop_id);
			models_info.(field_name) = temperature_matrix;

			disp(sprintf("COMPUTE GMM FOR %dth CATEGORIES DONE, TOTAL WORK:%d!", crop_id, total_work));
		end
	
		if (my_rank != 0)
			TAG = 100 + workload_entry_matrix(1, my_rank + 1) - 1;
			for work_index = 1:rank_work
				crop_id = workload_entry_matrix(1, my_rank + 1) + work_index - 1;
				field_name = sprintf("Crop%dTemperatureMatrix", crop_id);
				temperature_matrix = models_info.(field_name);
				rank_vect = 0;
				MPI_Send (temperature_matrix, rank_vect, TAG, communicator);
				TAG += 1;
			end
		endif

		if (my_rank == 0)
			models_info.("Crop1TemperatureMatrix") = temperature_matrix;
			%disp(sprintf("Receive %dth crop temperature info. Temperature info is: %f %f", 1, temperature_matrix(1,1), temperature_matrix(1,2)));
					
			for rank_index = 1:work_processor_num - 1
				rank_work = workload_matrix(1, rank_index + 1);
				TAG = 100 + workload_entry_matrix(1, rank_index + 1) - 1;
				for work_index = 1:rank_work
					[temperature_matrix, info] = MPI_Recv (rank_index, TAG, communicator);
					crop_id = workload_entry_matrix(1, rank_index + 1) + work_index - 1;
					disp(sprintf("Receive %dth crop temperature info. Temperature info is: %f %f", crop_id, temperature_matrix(1,1), temperature_matrix(1,2)));
					field_name = sprintf("Crop%dTemperatureMatrix", crop_id);
					models_info.(field_name) = temperature_matrix;
					TAG += 1;
				end
			end
		endif
	else
		sql = sprintf("UPDATE Image_Subfield_Status SET Subfield_Status_ID = 9 WHERE Job_ID = %d and Rank_ID = %d", jobID, my_rank);
        db.ExecuteSQL(sql);
		models_info.("empty") = 1;
	endif










