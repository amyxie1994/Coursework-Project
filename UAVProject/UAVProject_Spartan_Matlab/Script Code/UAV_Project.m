%Course Work Project - COMP90055
%Created by Hongzhen Xie -773383
%Software Development
%


%this programme is used for processing image, there are 3 steps in this
%programme 1: edge processing 2: building gmm model 3: adaptive gwsi 

function UAV_Project(file_path,speciesInfo_path,temp_min,temp_max,job_ID)
    
    tic;
 
 
    image_data = imread(file_path);
    
    %image_data = image_data(:,1:5597);
    %get length and width of the image

    [data_length ,data_width] = size(image_data);
    
    
    %read species information into a table 
    speciesInfo = readtable(speciesInfo_path);
    %disp(speciesInfo);
    
    
    %get min temperature and max temperature
    min_temp = str2double(temp_min);
    max_temp = str2double(temp_max);
    
    %udpate database
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    javaaddpath ('Database.jar');
    javaaddpath ('mysql-connector-java-5.1.42-bin.jar');
    db = javaObject('Database','115.146.89.41',3306,'UAVProject_DB','root','1314');
   
    %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    job_id = str2num(job_ID);
    sql=sprintf('UPDATE Job_Info SET Job_Status_ID = 8 WHERE Job_ID = %d',job_id);
    disp(sql);
    db.ExecuteSQL(sql);
    db.CloseSession();
    %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    %stage1: remove edge of image
    result = edge_detecting(image_data,data_length,data_width,job_id);
    edgeout_image = result{1};

    
    %transfer to temp 66536
    Temp = image_temp(image_data,data_width,data_length,min_temp,max_temp);
    Temp_edgeout_crop = image_temp_crop_line(edgeout_image,data_width,data_length,min_temp,max_temp,job_id);
  
    %stage2 & stage3
    %%%%%%%%%%%%%%%%%%%%%%%
    gmm_adaptive_cwsi(Temp,Temp_edgeout_crop,speciesInfo,data_length,data_width,job_id);
    %%%%%%%%%%%%%%%%%%%%%%%

    toc;
end

%get temperature infor of this image
function Temp_edgeout_crop = image_temp(image,width,length,min_temp,max_temp)
    %Temp_edgeout = get_temp_image_edgeoout(edge_remove_image);
    diff_temp = max_temp-min_temp;
    disp(diff_temp);

    spmd
        

        times = floor(width/numlabs);
    
        if labindex < numlabs
             local_data = image(:,(labindex-1)*times+1:times*labindex);
        else
             local_data = image(:,(labindex-1)*times+1:end);
        end
            
        local_data_crop=double((double(local_data)*diff_temp)/65536. + min_temp); 
            
        if labindex > 1
            labSend(local_data_crop,1);
        else
            re = zeros(length,width);
            re(:,labindex:labindex*times) = local_data_crop;
            for te=2:(numlabs-1)
                tem = labReceive(te);
                re(:,(te-1)*times+1:te*times) = tem;
            end
            re(:,(numlabs-1)*times+1:end) = labReceive(numlabs);
        end
    end
    Temp_edgeout_crop = re{1};
    
    %Temp_edgeout_crop(Temp_edgeout_crop==20) = NaN;
    %Temp_edgeout_crop_line = Temp_edgeout_crop(:);
end

% this is function for processing stage 2 and stage 3
function gmm_adaptive_cwsi(Temp,Temp_crop,speciesInfo,data_length,data_width,job_id)
    

    speciesNum = height(speciesInfo);
    %speciesInfoColumn = width(speciesInfo);
   
    Adap_CWSI_ortho_all_GMM = Temp_crop;
    
    % for each specie, first build gaussian mixture model of this image
    % then build adaptive index over the image
    for x = 1:speciesNum
        
        %get specie info from boundry information file
        row_start = speciesInfo.row_start(x);
        row_end = speciesInfo.row_end(x);
        column_start = speciesInfo.column_start(x);
        column_end = speciesInfo.column_end(x);

        %get species info from image
        specie_temp = Temp_crop(row_start:row_end,column_start:column_end);
        specie_temp2 = Temp(row_start:row_end,column_start:column_end);
        
        % linerize the data change 2 Dimension to 1 Dimension
        Temp_edgeout_crop_line = specie_temp(:);

        %build gmm model of this speice
        gmm_model = build_model(Temp_edgeout_crop_line,job_id);
        
      % Twet = gmm_model.mu-2.58*sqrt(gmm_model.Sigma);
      %  Tdry = gmm_model.mu+2.58*sqrt(gmm_model.Sigma);

      % get wet temperature and dry temperature of this specie
        [Twet,Tdry] = get_specie_Temp(gmm_model);
        
        specie_width = column_end-column_start+1;
        specie_length = row_end-row_start+1;

        %build index of this specie
        CWSI_GMM=adaptive_cwsi(specie_temp2,specie_width,specie_length,Tdry,Twet,job_id);
        
        
        Adap_CWSI_ortho_all_GMM(row_start:row_end,column_start:column_end) = CWSI_GMM;
        
        %Temp_specie = image[]
        
    end

    Adap_CWSI_ortho_all_GMMCopy = scale_result(Adap_CWSI_ortho_all_GMM,data_length,data_width,job_id);
    
    
   %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    javaaddpath ('Database.jar');
    javaaddpath ('mysql-connector-java-5.1.42-bin.jar');
    db = javaObject('Database','115.146.89.41',3306,'UAVProject_DB','root','1314');
   
 %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%      
  sql = sprintf('UPDATE Image_Subfield_Status SET Subfield_Status_ID = 6 WHERE Job_ID = %d and Rank_ID = %d',job_id,1);
  db.ExecuteSQL(sql);
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% 
    figure;imagesc(Adap_CWSI_ortho_all_GMMCopy);axis equal;axis image;colorbar;colormap(jet);
    caxis([0,1]);
    title('Adaptive CWSI from GMM');
    
    newFileName = strcat(job_id,'Adap_CWSI_ortho_all_GMM.png');
    %img_double = Adap_CWSI_ortho_all_GMMCopy;
    saveas(gcf,newFileName);
    
    
   pathToDestination = strcat('/opt/lampp/htdocs/UAVProject/php/result/',newFileName);
    command = sprintf('scp -i uavproject.key %s ubuntu@115.146.89.41:%s',newFileName,pathToDestination);
    
    system(command);   
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%      
  sql = sprintf('UPDATE Image_Subfield_Status SET Subfield_Status_ID = 9 WHERE Job_ID = %d and Rank_ID = %d',job_id,1);
  db.ExecuteSQL(sql);
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%             
   
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
   sql=sprintf('UPDATE Job_Info SET Job_Status_ID = 7, Result_URL = %s WHERE Job_ID = %d',job_id,newFileName);
    %disp(sql);
    db.ExecuteSQL(sql);
    db.CloseSession();
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
end


function Temp_edgeout_crop = image_temp_crop_line(image,width,length,min_temp,max_temp,job_id)
    %Temp_edgeout = get_temp_image_edgeoout(edge_remove_image);
    diff_temp = max_temp-min_temp;
    disp(diff_temp);

    spmd
        times = floor(width/numlabs);
    
        if labindex < numlabs
             local_data = image(:,(labindex-1)*times+1:times*labindex);
        else
             local_data = image(:,(labindex-1)*times+1:end);
        end
            
        local_data_crop=double((double(local_data)*diff_temp)/65536. + min_temp); 
        local_data_crop(local_data_crop==min_temp) = NaN;
            
        if labindex > 1
            labSend(local_data_crop,1);
        else
            re = zeros(length,width);
            re(:,labindex:labindex*times) = local_data_crop;
            for te=2:(numlabs-1)
                tem = labReceive(te);
                re(:,(te-1)*times+1:te*times) = tem;
            end
            re(:,(numlabs-1)*times+1:end) = labReceive(numlabs);
        end
        
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
        javaaddpath ('Database.jar');
        javaaddpath ('mysql-connector-java-5.1.42-bin.jar');
        db = javaObject('Database','115.146.89.41',3306,'UAVProject_DB','root','1314');
        %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
        sql = sprintf('UPDATE Image_Subfield_Status SET Subfield_Status_ID = 9 WHERE Job_ID = %d and Rank_ID = %d',job_id,labindex);
       
        db.ExecuteSQL(sql);
        db.CloseSession();
        %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    end
    Temp_edgeout_crop = re{1};
    
    %Temp_edgeout_crop(Temp_edgeout_crop==20) = NaN;
    %Temp_edgeout_crop_line = Temp_edgeout_crop(:);
end


%build adaptive index of this specie
function  CWSI_ortho_GMM = adaptive_cwsi(specie_temp,width,length,Tdry,Twet,job_id)
    
        
        Tdiff = Tdry - Twet;
        spmd
            times = floor(width/numlabs);
            
 %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  javaaddpath ('Database.jar');
    javaaddpath ('mysql-connector-java-5.1.42-bin.jar');
 db = javaObject('Database','115.146.89.41',3306,'UAVProject_DB','root','1314');
    
  sql = sprintf('UPDATE Image_Subfield_Status SET Subfield_Status_ID = 5 WHERE Job_ID = %d and Rank_ID = %d',job_id,labindex);
  db.ExecuteSQL(sql);
  db.CloseSession();
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    
            if labindex < numlabs
                local_data = specie_temp(:,(labindex-1)*times+1:times*labindex);
            else
                local_data = specie_temp(:,(labindex-1)*times+1:end);
            end
            
            local_CWSI_ortho_GMM = (local_data-Twet)/Tdiff;
            
            if labindex > 1
                 labSend(local_CWSI_ortho_GMM,1);
            else
                 re = zeros(length,width);
                 re(:,labindex:labindex*times) = local_CWSI_ortho_GMM;
                 for te=2:(numlabs-1)
                    tem = labReceive(te);
                    re(:,(te-1)*times+1:te*times) = tem;
                 end
                 re(:,(numlabs-1)*times+1:end) = labReceive(numlabs);
            end
            
        end
        
        CWSI_ortho_GMM= re{1};
        figure;imagesc(CWSI_ortho_GMM);axis equal;axis image;colorbar;colormap(jet);
        caxis([0,1]);
        save('CWSI_ortho_GMM.mat','CWSI_ortho_GMM');
        
             
end

%get wet and dry temperature based on the gaussian model
function [Twet,Tdry] = get_specie_Temp(gmm_model)
        
      mu1 = gmm_model.mu(1);
      mu2 = gmm_model.mu(2);
      
      if mu1>mu2
          sigma = gmm_model.Sigma(2);
          mu = mu2;
      else
          sigma = gmm_model.Sigma(1);
          mu = mu1;
      end
      
     Twet = mu-2.58*sqrt(sigma);
     Tdry = mu+2.58*sqrt(sigma);
end


%scale up the result
function Adap_CWSI_ortho_all_GMMCopy = scale_result(Adap_CWSI_ortho_all_GMM,length,width,job_id)

    spmd
        
    
        times = floor(width/numlabs);
    
        if labindex < numlabs
             local_data = Adap_CWSI_ortho_all_GMM(:,(labindex-1)*times+1:times*labindex);
        else
             local_data = Adap_CWSI_ortho_all_GMM(:,(labindex-1)*times+1:end);
        end
            
        local_data(local_data>1.0) = 1.1; 
            
        if labindex > 1
            labSend(local_data,1);
        else
            re = zeros(length,width);
            re(:,labindex:labindex*times) = local_data;
            for te=2:(numlabs-1)
                tem = labReceive(te);
                re(:,(te-1)*times+1:te*times) = tem;
            end
            re(:,(numlabs-1)*times+1:end) = labReceive(numlabs);
            figure;imagesc(re);axis equal;axis image;colorbar;colormap(jet);
            caxis([0,1]);
            title('Adaptive CWSI from GMM');
            newFileName =strcat(job_id, '.png');
            saveas(gcf,newFileName);
    

           % newFileName = 'Adap_CWSI_ortho_all_GMM.png';
            %img_double = Adap_CWSI_ortho_all_GMMCopy;
            %saveas(gcf,newFileName);
        end
        
        
 %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    javaaddpath ('Database.jar');
    javaaddpath ('mysql-connector-java-5.1.42-bin.jar');
    db = javaObject('Database','115.146.89.41',3306,'UAVProject_DB','root','1314');
   
 %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%   
  sql = sprintf('UPDATE Image_Subfield_Status SET Subfield_Status_ID = 9 WHERE Job_ID = %d and Rank_ID = %d',job_id,labindex);
  db.ExecuteSQL(sql);  
    db.CloseSession();
   %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
 
 
    end   
    Adap_CWSI_ortho_all_GMMCopy = re{1};
      
end


%this function is used for edge detecting part, two kinds of edge detecting
%functions are used here, this part is implemented under parallel
function re = edge_detecting(image_data,data_length,data_width,job_id)

 spmd   
            
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
 javaaddpath ('Database.jar');
 javaaddpath ('mysql-connector-java-5.1.42-bin.jar');
db = javaObject('Database','115.146.89.41',3306,'UAVProject_DB','root','1314');
  sql = sprintf('INSERT INTO Image_Subfield_Status(Job_ID,Rank_ID,Subfield_Status_ID)VALUES (%d,%d,%d)',job_id,labindex,2);
  db.ExecuteSQL(sql);
db.CloseSession();
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
      % get local data of the image based on the number of workers 
       times = floor(data_width/numlabs);
    
        if labindex < numlabs
            local_data = image_data(:,(labindex-1)*times+1:times*labindex);
        else
            local_data = image_data(:,(labindex-1)*times+1:end);
        end
        
        %edge detection using two kind of functions
        sobel_detection= edge(local_data,'sobel');
        canny_detection= edge(local_data,'canny');
        %edge detection
    
        %edge dilating
        image_detection = sobel_detection+canny_detection;
        image_imdilate = imdilate(image_detection,strel('square',5));
  
        temp = image_imdilate;
        local_data_width = size(local_data,2);
        
        disp(local_data_width);

        for x=1:data_length
            for y=1:local_data_width
            z1 = temp(x,y);
            if(z1>0)
                temp(x,y) = 0;
            else
                temp(x,y) = 1;
            end
            end
         end

    %edge removing
    image_edge_remove = (double(local_data)).*(double(temp)); 


    if labindex > 1
        labSend(image_edge_remove,1);
    else
        re = zeros(data_length,data_width);
        re(:,labindex:labindex*times) = image_edge_remove;
        for te=2:(numlabs-1)
            tem = labReceive(te);
            re(:,(te-1)*times+1:te*times) = tem;
        end
        re(:,(numlabs-1)*times+1:end) = labReceive(numlabs);
    end
    

    
   end
end

% this function is used for building the gmm model of this image
% and display the model
function model = build_model(Temp_edgeout_crop_line,job_id)
 

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
 javaaddpath ('Database.jar');
    javaaddpath ('mysql-connector-java-5.1.42-bin.jar');
db = javaObject('Database','115.146.89.41',3306,'UAVProject_DB','root','1314');
  sql = sprintf('UPDATE Image_Subfield_Status SET Subfield_Status_ID = 4 WHERE Job_ID = %d and Rank_ID = %d',job_id,1);
  db.ExecuteSQL(sql);
   db.CloseSession();
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    rng(1);
    x = Temp_edgeout_crop_line; % whole image
    xi1 = x'; 
    Dimension = 2;
    model = fitgmdist(xi1',Dimension);
end

