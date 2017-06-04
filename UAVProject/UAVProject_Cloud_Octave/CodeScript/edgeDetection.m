%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%%  Purpose: This function is used for edge detection operations.            %%
%%                                                                           %%
%%  Input:                                                                   %%
%%       (1) imageMatrix: image piexl matrix.                                %%
%%  Output:                                                                  %%
%%       (1) edgeDetect_binaryImage: the binary piexl image matrix after    %%
%%           edge detection and binarization.                                %%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

function edge_out_matrix = edgeDetection(image_matrix, width, height)
    
   edge_detect_sobel = edge(image_matrix,'sobel');
   edge_detect_canny = edge(image_matrix,'canny');
   edge_detect_matrix= edge_detect_sobel + edge_detect_canny;

   edge_length = 5;
   edgedetect_binary_matrix = imdilate(edge_detect_matrix,strel('square',edge_length));
   
   for x=1:height
       for y=1:width
           value = edgedetect_binary_matrix(x,y);
           if(value > 0)
               edgedetect_binary_matrix(x,y) = 0;
           else
               edgedetect_binary_matrix(x,y) = 1;
           end
       end
   end

   edge_out_matrix = times(double(image_matrix), double(edgedetect_binary_matrix));