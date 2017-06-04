<?php
/*
This file contain functions of operation on SPARTAN part.
Including submitting job, creating slurm file, etc.
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project
*/

//First define the host and port info of SPARTAN,
const Host = 'spartan2.hpc.unimelb.edu.au';
const Port = 22;




//This is the main control function of submitting job to spartan,  first it will connect to server and authenticate, once successful, 
function runSpartan($coreNum,$image,$username,$password,$min_temp,$max_temp,$specie_info)
{

    $connection =server_connect();
    if($connection)
    {
        $auth_connection = user_authentication($connection,$username,$password);

        if($auth_connection)
        {
            //echo nl2br("\nAuthentication Successful!");
            return execute($connection,$image,$coreNum,$specie_info,$min_temp,$max_temp);
        }
        else
        {

            echo '<script language="javascript">alert("authentication failed");</script>';
        }
    }
    else
    {
        echo '<script language="javascript">alert("connection failed");</script>';
    }

}

//This function is used for creating slurmfile used for run on spartan.

function create_slurmfile($coreNum,$specie_info,$image,$min_temp,$max_temp)
{
    $nodeNum = 1;
    $myfile = fopen("exe.slurm", "w") or die("Unable to open file!");
    $com = "#!/bin/bash\n#SBATCH -p physical\n#SBATCH --time=00:05:00\n";
    fwrite($myfile, $com);
    $com = "#SBATCH --nodes=".$nodeNum."\n#SBATCH --cpus-per-task=".$coreNum."\n";
    fwrite($myfile, $com);
    $com = "module load MATLAB/2016a\nsrun matlab -nodesktop -r \"UAV_Project('".$specie_info."','".$image."','".$min_temp."','".$max_temp."',1);\"";
    fwrite($myfile, $com);
    fclose($myfile);
}



//This function is used for sending file to SPARTAN.
function submit_file($connection,$image,$specie_info)
{
    $local_path = 'uploadfile/'.$image;
    $local_specie_file = 'uploadfile/'.$specie_info;
    //$remote_path = $image;
    $remote_exe_path = 'exe.slurm';
    ssh2_scp_send($connection,'UAV_Project.m','UAV_Project.m');
    ssh2_scp_send($connection,'exe.slurm','exe.slurm');
    ssh2_scp_send($connection,$local_specie_file,$specie_info);
    ssh2_scp_send($connection,$local_path,$image);
}

//This function is used for execute command on SPARTAN.
function execute($connection,$image,$coreNum,$specie_info,$min_temp,$max_temp)
{
    echo nl2br("\n".$image);
    create_slurmfile($coreNum,$specie_info,$image,$min_temp,$max_temp);
    submit_file($connection,$image,$specie_info);
    //ssh2_exec($connection, 'cd mproject && sbatch exe.slurm');
    $command = 'sbatch exe.slurm';
    $stdout_stream = ssh2_exec($connection, $command);
    stream_set_blocking($stdout_stream, true);
    $err_stream = ssh2_fetch_stream($stdout_stream, SSH2_STREAM_STDERR);

    $dio_stream = ssh2_fetch_stream($stdout_stream,SSH2_STREAM_STDIO);

    stream_set_blocking($err_stream, true);
    stream_set_blocking($dio_stream, true);

    $content = stream_get_contents($dio_stream);
    $array = explode(' ',$content);
    $slurm_id = end($array);

    return $slurm_id;

}

//This function is just used for testing.
function get_result($connection)
{
    $command = 'cd mproject && ls Adap_CWSI_ortho_all_GMM.png';
    while(true)
    {
        $stdout_stream = ssh2_exec($connection, $command);
        stream_set_blocking($stdout_stream, true);
        $err_stream = ssh2_fetch_stream($stdout_stream, SSH2_STREAM_STDERR);

        $dio_stream = ssh2_fetch_stream($stdout_stream,SSH2_STREAM_STDIO);

        stream_set_blocking($err_stream, true);
        stream_set_blocking($dio_stream, true);

        $result_err = stream_get_contents($err_stream);
        $result_dio = stream_get_contents($dio_stream);
        echo $result_dio;

        if(!empty($result_dio))
        {
            ssh2_scp_recv($connection, 'mproject/Adap_CWSI_ortho_all_GMM.png', 'result.png');
            download_result('result.png');
            break;
        }

    }

}

//This function is used for connecting server

function server_connect()
{
    return ssh2_connect(Host,Port);
}


//authenticate user
function user_authentication($connection,$username,$password)
{

    return ssh2_auth_password($connection, $username,$password);
}

?>