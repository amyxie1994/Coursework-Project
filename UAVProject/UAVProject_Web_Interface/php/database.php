<?php
/*
This function contains function used for database operations. including connecting, creating job record.
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project
*/


//connect to database
function connect_database()
{
		$servername = "115.146.89.41";
		$username= "root";
		$password = "1314";
		$db_name = "UAVProject_DB";
    
    	$conn = new mysqli($servername, $username, $password,$db_name);
   		// Check connection
		if ($conn->connect_error) {
    		die("Connection failed: " . $conn->connect_error);
		} 
		//echo "Connected successfully";
		return $conn;
}


//create job record in database
function create_job($image, $user_id, $platform, $SpartanUserName, $password)
{

    $conn = connect_database();
    //$userId= get_user_id($conn,$username);
    //$statusId = get_status_id($conn,"Unprocessed");
    //$imageUrl = "uploadfile/" . $image;

    $sql = sprintf("INSERT INTO Job_Info(Submit_User_ID,Job_Status_ID,Image_URL,Platform,Spartan_UserName,Spartan_Password,Submit_Time) VALUES (%d,%d,'%s','%s','%s','%s',now())", $user_id, 1, $image, $platform, $SpartanUserName, $password);
    echo $sql;

    $result = mysqli_query($conn, $sql);
    $job_id = mysqli_insert_id($conn);
    if ($result)
        echo nl2br("\nsuccesfully create job");
    else
        echo nl2br("\ncreate job fail");
    mysqli_close($conn);
    //sleep(5);
    //header('Location: ../index.html');
    return $job_id;
}

// getting job information based on job_id
function get_job_info($job_id)
{
	$connection = connect_database();
	$sql = "SELECT * FROM `Job_Info` WHERE Job_ID ='$job_id'; ";
	$result = mysqli_query($connection, $sql);
	$row = mysqli_fetch_assoc($result);
	mysqli_close($connection);
	return $row;

}

// deleting job record in database
function db_delete_job($job_id)
{
	$connection = connect_database();

    $sql = sprintf("DELETE FROM `Image_Subfield_Status` WHERE Job_ID =%d; ",$job_id);
    $result = mysqli_query($connection, $sql);
    $sql = sprintf("DELETE FROM `Job_Info` WHERE Job_ID =%d; ",$job_id);
	$result = mysqli_query($connection, $sql);

	//return $result;
}

//get processor status of one job.
function get_processor_status($conn,$job_id)
{		
		//$job_id=3;
		$sql = "SELECT Subfield_Status_ID FROM `Image_Subfield_Status` WHERE Job_ID ='$job_id' ORDER BY Rank_ID;";
		$result = mysqli_query($conn, $sql);
		$arr = array();
		$i = 0;
		while($row = mysqli_fetch_assoc($result))
		{
			$arr[$i]=$row['Subfield_Status_ID'];
			$i++;
		}
		return $arr;
}

//getting job status in database
function get_status_id($conn,$status)
{
		$sql = "SELECT Status_ID FROM `JobStatusLookupTable` WHERE Status_Desc ='$status';";
		$result = mysqli_query($conn, $sql);
		$row = mysqli_fetch_assoc($result);
		return $row["Status_ID"];
}
?>