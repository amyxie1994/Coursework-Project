<?php
/*
This file is used for getting resources by executing "sinfo" command on cloud and spartan.
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project
*/


include "cloud.php";
include "spartan.php";


$spartanResource=getSpartanResource();
$cloudResource=getCloudResource();
$result_array = array();
$result_array[] = $cloudResource;
$result_array[] = $spartanResource;
echo json_encode($result_array);



function getCloudResource()
{
    $connection = ssh2_connect('115.146.89.41', 22);

    if(cloud_authentication($connection))
    {

        $result = get_Resource($connection);
        $json_result['Platform_ID'] = 1;
        $json_result['Allocated_Resources'] = $result[0];
        $json_result['Idle_Resources'] = $result[1];
        return $json_result;
    }
    else
    {
        echo '<script language="javascript">alert("authntication failed");</script>';
    }

}

function getSpartanResource()
{
    $host = 'spartan2.hpc.unimelb.edu.au';
    $username = 'hongzhenx';
    $password = 'crab1994';


   $connection = ssh2_connect($host,22);
 
   if(ssh2_auth_password($connection,$username,$password))
   {

       $result =get_Resource($connection);

       $json_result['Platform_ID'] = 2;
       $json_result['Allocated_Resources'] = $result[0];
       $json_result['Idle_Resources'] = $result[1];
       $json_result['Other_Resources'] = $result[2];
       //return $json_result;

   }
   else
   {
    echo "error in getting spartan resources.";
   }
   
}


//getting resources command
function get_Resource($connection)
{
    $command ='sinfo -o "%all %C %c"';
    $stdout_stream=ssh2_exec($connection, $command);
    stream_set_blocking($stdout_stream, true);
    $err_stream = ssh2_fetch_stream($stdout_stream, SSH2_STREAM_STDERR);

    $dio_stream = ssh2_fetch_stream($stdout_stream,SSH2_STREAM_STDIO);

    stream_set_blocking($err_stream, true);
    stream_set_blocking($dio_stream, true);

    $result_err = stream_get_contents($err_stream);
    $result_dio = stream_get_contents($dio_stream);


    $result = split_content($result_dio);
    //$connection->close();
    return $result;


}

//split result from SPARTAN and Cloud

function split_content($result_dio)
{
    $arr = preg_split("/\n/", $result_dio);

    $row=$arr[1];
    $temp=substr($row,4,-2);
    $te=explode('/', $temp);
    $alloc_node =intval($te[0]);
    $idle_node =intval($te[1]);
    $other_node =intval($te[2]);
    $total_node =intval($te[3]);
    $result=[$alloc_node,$idle_node,$other_node,$total_node];
    //print_r( $result);
    return $result;

}


?>