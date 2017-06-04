<?php
/*
This function is used for getting job status in database
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project
*/
include "database.php";

$connection = connect_database();
//$username = $_SESSION("AccountName");

session_start();

$UserID = $_SESSION['user_id'];
$sql = "SELECT * FROM `Job_Info` INNER JOIN `JobStatusLookupTable` ON Job_Status_ID = Status_ID WHERE Submit_User_ID ='$UserID'; ";
$result = mysqli_query($connection, $sql);

$arr=array();
for($i =0;$row = mysqli_fetch_assoc($result);$i++)
{
        $arr[$i] = $row;
}

echo json_encode($arr);


?>