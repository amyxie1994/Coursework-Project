<?php
/*
This is the main control process file of the whole web interface.
Once the user has submit their job, this file will be resonsible for submitting the job to process in remote platforms.

Based on platforms chosen by users, the job will be submitted to different platforms.

Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project 
*/

//This file is in charge of the processing upload files.
include "uploadfileProcess.php";
include "spartan.php"; //This file contatins the function of operations on spartan.
include "cloud.php"; //This file contains operations on cloud
include "database.php";  //Contains functions of database operation.

//Check the safty of this page, prevent illeagal visiting of this file.
session_start();

if (isset($_SESSION['AccountName'])) {
    echo "Not Permitted";
} else {

    //get platform info and 
    $platForm = $_POST["platform"];
    $user_id = $_SESSION['user_id'];
    $image = getImage();
    $specie_info = get_Info();
    
    //based on the platform, do different operations
    //First create a record of this job in database, then submit this job to remote platform and receive submit job id, and then updata to database.
    switch ($platForm) {
        case 'Spartan':

        //for spartan user, get username ,pwd, core num
            $coreNum = $_POST["coreNum"];
            $userName = $_POST["spartanUsername"];
            $password = $_POST["spartanPwd"];
            $min_temp = $_POST["min_temp"];
            $max_temp = $_POST["max_temp"];
           

            $nodeNum =1;
            $slurm_id = "";
            $job_id = create_job($image, $user_id, 'S', $userName, $password,$slurm_id);
            $slurm_id = runSpartan($coreNum, $image, $userName, $password, $min_temp, $max_temp, $specie_info,$job_id);
            
            $sql = sprintf("UPDATE Job_Info SET Slurm_ID = '%s' WHERE Job_ID = %d",$slurm_id,$job_id);

        
            $conn = conncet_mysql("115.146.89.41", "root", "1314", "UAVProject_DB");
            $result = mysqli_query($conn, $sql);
            header('Location: ../index.php');

            break;

        case 'Cloud' :
        //For cloud part, get core number and node number 
            $coreNum = $_POST["cloud_coreNum"];
            $nodeNum = $_POST["cloud_nodeNum"];
        
            $slurm_id = "";
            $job_id = create_job($image, $user_id, 'C', "", "",$slurm_id);
            $slurm_id = runCloud($image, $specie_info, $nodeNum, $coreNum,$job_id);
            $sql = sprintf("UPDATE Job_Info SET Slurm_ID = '%s' WHERE Job_ID = %d",$slurm_id,$job_id);

            $conn = conncet_mysql("115.146.89.41", "root", "1314", "UAVProject_DB");
            $result = mysqli_query($conn, $sql);
            
            header('Location: ../index.php');
            break;
//If user want to process on both platforms, first submit to spartan and then to cloud, the system will create two job records in the database.
        case 'Both' :
           $coreNum = $_POST["coreNum"];
            $userName = $_POST["spartanUsername"];
            $password = $_POST["spartanPwd"];
            $min_temp = $_POST["min_temp"];
            $max_temp = $_POST["max_temp"];
           

            $nodeNum =1;
            $slurm_id = "";
            $job_id = create_job($image, $user_id, 'S', $userName, $password,$slurm_id);
            $slurm_id = runSpartan($coreNum, $image, $userName, $password, $min_temp, $max_temp, $specie_info,$job_id);
            
            $sql = sprintf("UPDATE Job_Info SET Slurm_ID = '%s' WHERE Job_ID = %d",$slurm_id,$job_id);

        
            $conn = conncet_mysql("115.146.89.41", "root", "1314", "UAVProject_DB");
            $result = mysqli_query($conn, $sql);

            $coreNum = $_POST["cloud_coreNum"];
            $nodeNum = $_POST["cloud_nodeNum"];
        
            $slurm_id = "";
            $job_id = create_job($image, $user_id, 'C', "", "",$slurm_id);
            $slurm_id = runCloud($image, $specie_info, $nodeNum, $coreNum,$job_id);
            $sql = sprintf("UPDATE Job_Info SET Slurm_ID = '%s' WHERE Job_ID = %d",$slurm_id,$job_id);

            $conn = conncet_mysql("115.146.89.41", "root", "1314", "UAVProject_DB");
            $result = mysqli_query($conn, $sql);
            
            header('Location: ../index.php');
            break;
        default:
            break;
    }

}






?>