<?php
/*
*  This file is userd for deleting jobs in spartan and cloud.
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project
*/
include "cloud.php";
include "spartan.php";
include "database.php";


$job_id = $_POST["job_id"];
//$job_id="802839";
$submit_id = $_POST["slurm_id"];
$platform = $_POST["platform"];

$job_info = get_job_info($job_id);


// based on different platforms, having different operations
// first get slurm id, and then delete in remote platform and database
if($platform=="C")
{	//for cloud users
	$connection = ssh2_connect('115.146.89.41', 22);

	if(cloud_authentication($connection))
	{
		$slurm_id = $job_info["Slurm_ID"];
		ssh_delete_job($connection,$slurm_id);
		db_delete_job($job_id);
		echo json_encode("success");
	}
	else
	{
		die('Public Key Authentication Failed');
	}
}
else
{	

	db_delete_job($job_id);
		echo json_encode("success");

	$slurm_id = $job_info["Slurm_ID"];
	$username = $job_info["Spartan_UserName"];
	$password = $job_info["Spartan_Password"];

	$connection = server_connect();
	
	//echo json_encode("success");
	if(user_authentication($connection,$username,$password))
	{
		
		ssh_delete_job($connection,$slurm_id);
		db_delete_job($job_id);
		echo json_encode("success");
	}
	else
	{
		die('Spartan Authentication Failed');
	}

	
}



// delete job in remote platform
function ssh_delete_job($Connection,$submit_id)
{
	$command ='scancel '.$submit_id;
	ssh2_exec($Connection, $command);
}


?>