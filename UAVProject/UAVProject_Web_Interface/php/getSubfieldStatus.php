<?php
/**
 This file is used for getting subfieldstatus of one job.
 Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project
 */

include "database.php";

$job_id = $_POST['job_id'];
$conn = connect_database();


$sql = sprintf("SELECT * FROM `Image_Subfield_Status` WHERE Job_ID =%d; ",$job_id);
$result = mysqli_query($conn, $sql);

$arr=array();
for($i =0;$row = mysqli_fetch_assoc($result);$i++)
{
    $arr[$i] = $row;
}

//echo $sql;
echo json_encode($arr);


?>
