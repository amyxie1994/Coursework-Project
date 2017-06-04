<?php
/*
This function contains the operations on Cloud.
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project
*/

//Main control of the whole processing on Ckoud part
function runCloud($image,$specie_info,$node_num,$core_num,$job_id)
{


	send_file_cloud($image,$specie_info);
	create_cloud_slurm_file($image,$specie_info,$node_num,$core_num,$job_id);
	$connection = ssh2_connect('115.146.89.41', 22);

	if(cloud_authentication($connection))
	{
		$local_path = 'cloud_exe.slurm';
		//~/UAVProject_DataStructure_Optimise/CodeScript
		$remote_path ='UAVProject_DataStructure_Optimise/CodeScript/cloud.slurm';
		ssh2_scp_send($connection,$local_path,$remote_path);
		$slrum_id = executeCloud($connection);
        return $slrum_id;
	}
	else
	{
		die('Public Key Authentication Failed');
	}

}

// create slurm file to run on cloud part
function create_cloud_slurm_file($image,$specie_info,$nodeNum,$coreNum,$job_id)
{
	$myfile = fopen("cloud_exe.slurm", "w") or die("Unable to open file!");
	$com = "#!/bin/bash\n#SBATCH --time=01:00:00\n";
	fwrite($myfile, $com);
	$com = "#SBATCH --nodes=".$nodeNum."\n#SBATCH --ntasks-per-node=".$coreNum."\n";
	fwrite($myfile, $com);
	$com = "eval `ssh-agent`\nssh-add /home/ubuntu/.ssh/uavproject.key\n";
	fwrite($myfile, $com);
	$com="mpirun -x LD_PRELOAD=libmpi.so octave -q --eval \"ImageProcessing ('../ImagesInfo/".$image."', '../ImagesInfo/".$specie_info."',".$job_id.")\"";

	fwrite($myfile, $com);
	fclose($myfile);
}

//sent file to cloud server.
function send_file_cloud($image,$specie_info)
{

	$arr_ip =['115.146.89.41','115.146.89.44','115.146.89.45','115.146.89.47'];
	
	foreach ($arr_ip as $ip_address) 
	{
    	$connection = cloud_connect($ip_address);
    	if(cloud_authentication($connection))
    	{
    		$local_path = 'uploadfile/'.$image;
			$local_specie_file = 'uploadfile/'.$specie_info;
			$remote_path ='UAVProject_DataStructure_Optimise/ImagesInfo/'.$image;
			$remote_specie_info ='UAVProject_DataStructure_Optimise/ImagesInfo/'.$specie_info;

			$stdout_stream=ssh2_scp_send($connection,$local_specie_file,$remote_specie_info);
			ssh2_scp_send($connection,$local_path,$remote_path);
			echo "send successful".$ip_address;
    	}
    	else
    	{
    		die('Public Key Authentication Failed');
    	}
	}
}

//authntication function of cloud
function cloud_authentication($connection)
{
	return ssh2_auth_pubkey_file($connection, 'ubuntu',
                          'uavproject.key.pub',
                          'uavproject.key', 'secret');

}
//connect to cloud
function cloud_connect($ip_address)
{
	return ssh2_connect($ip_address,22);
}


//execute command on cloud
function executeCloud($connection)
{
	$stdio_stream = ssh2_exec($connection, 'cd UAVProject_DataStructure_Optimise/CodeScript && sbatch cloud.slurm');
	stream_set_blocking($stdio_stream, true);
	$content = stream_get_contents($stdio_stream);

    $array = explode(' ',$content);
    $slurm_id = end($array);
	echo "SLURM ID IS :".$content;
	return $slurm_id;
}

?>