function [image_info, all_single_crop_info, CropCategoryNumber] = getImageInformation(pathToImage, pathToBoundaryFile)

	
	info = imfinfo(pathToImage);
	boundary_matrix  = csvread (pathToBoundaryFile);
	[row, column]    = size(boundary_matrix);
	CropCategoryNumber = row;

	image_info.("ImageMatrix") = imread(pathToImage);
    image_info.("Width") = getfield(info, "Width");
    image_info.("Height") = getfield(info, "Height");
	image_info.("CropCategoryNumber") = CropCategoryNumber;
	all_single_crop_info.("CropCategoryNumber") = CropCategoryNumber;

	image_matrix = image_info.("ImageMatrix");
	for i = 1:CropCategoryNumber
		
		local_row_low     = boundary_matrix(i,1);
		local_row_high    = boundary_matrix(i,2);
		local_column_low  = boundary_matrix(i,3);
		local_column_high = boundary_matrix(i,4);
		
		single_crop_boundary_matrix = zeros(1,4); 
		single_crop_boundary_matrix(1,1) = boundary_matrix(i,1);
		single_crop_boundary_matrix(1,2) = boundary_matrix(i,2);
		single_crop_boundary_matrix(1,3) = boundary_matrix(i,3);
		single_crop_boundary_matrix(1,4) = boundary_matrix(i,4);

		field_name = sprintf("Crop%dMatrix", i);
		all_single_crop_info.(field_name) = image_matrix(local_row_low:local_row_high, local_column_low:local_column_high);
		field_name = sprintf("Crop%dBoundaryMatrix", i);
		all_single_crop_info.(field_name) = single_crop_boundary_matrix;

		field_name = sprintf("Crop%dWidth", i);
		all_single_crop_info.(field_name) = local_column_high - local_column_low + 1;
		field_name = sprintf("Crop%dHeight", i);
		all_single_crop_info.(field_name) = local_row_high - local_row_low + 1;
	end

