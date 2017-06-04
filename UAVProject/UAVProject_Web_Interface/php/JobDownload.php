<?php
/*
This file is used for downloading result of submitted job.
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project
*/

include "database.php";

$job_id = $_GET['job_id'];
$job_info = get_job_info($job_id);
$result_url = $job_info["Result_URL"];
$temp = substr($result_url,4);

echo "<script>console.log( 'Debug Objects: " . $temp . "' );</script>";
//$result_url = strcat($job_id,".png");
download_remote_result($temp);

//download file to local folder
function download_remote_result($result)
{
    ob_end_clean();
    header("Content-Type: image/png");
    header('Content-Disposition: attachment; filename="'.$result.'"');
    header('Content-Length: '.filesize($result));
    readfile($result);


}


?>