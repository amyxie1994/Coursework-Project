<?php
/*
This file is used for getting result image in database.
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project

*/
include "database.php";

$job_id = $_POST['jobID'];
$conn = connect_database();
$sql = sprintf("SELECT * from Job_Info WHERE Job_ID = %d",$job_id);
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
echo json_encode($row);

?>