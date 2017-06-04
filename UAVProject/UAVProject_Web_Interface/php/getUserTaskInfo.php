<?php
/*
This file is used for getting user's task summery information.
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project
*/

include "database.php";

session_start();
$con = connect_database();

$sql = sprintf("SELECT count(*) as Total FROM User_Info INNER JOIN Job_Info ON User_Info.User_ID = Job_Info.Submit_User_ID ".
    "Where Submit_User_ID=%d", $_SESSION['user_id']);

$status = mysqli_query($con,$sql);
$row = mysqli_fetch_array($status);

if(!$row['Total']){
    $result['Total'] = 0;
    $result['Complete'] = 0;
    $result['Uncomplete'] = 0;
}else{
    $result['Total'] =  $row['Total'];
    // count job which has finished
    $sql = sprintf("SELECT count(*) as Complete FROM User_Info INNER JOIN Job_Info ON User_Info.User_ID = Job_Info.Submit_User_ID ".
        "Where Submit_User_ID=%d".
        " GROUP BY Job_Status_ID".
        " HAVING Job_Status_ID=%d", $_SESSION['user_id'], 7);

    $status = mysqli_query($con,$sql);
    $row = mysqli_fetch_array($status);
    if(!$row['Complete']){
        $row['Complete'] = 0;
    }

    $result['Complete'] = $row['Complete'];
    $result['Uncomplete'] = $result['Total'] - $result['Complete'];
}

echo json_encode($result);




?>