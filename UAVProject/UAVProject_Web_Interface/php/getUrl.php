<?php
/**
This file is used for getting result image url based in job id.
 Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project
 */

include "database.php";

$job_id = $_POST['job_id'];
$conn = connect_database();

$sql = sprintf("SELECT Result_URL FROM `Job_Info` WHERE Job_ID =%d; ",$job_id);
$result = mysqli_query($conn, $sql);

$row = mysqli_fetch_assoc($result);
echo json_encode($row);

?>
