<?php
/*
This file is used for verifying log in users.
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project
*/

include "database.php"


// Getting username and password of user and check the infor in database
$username = $_REQUEST['username'];
$password = $_REQUEST['password'];

//Check in database and return result
$con = conncet_database();
$sql = sprintf("SELECT * FROM User_Info where AccountName = '%s' AND AccountPassword = '%s'", $username, $password);

$user_info = mysqli_query($con,$sql);
$row = mysqli_fetch_array($user_info);

if (!$row) {
    echo 'failed';
}else{
    session_start();


    // Store Session Data
    $_SESSION['login_user']= $username;
    $_SESSION['firstname'] = $row['FirstName'];
    $_SESSION['lastname'] = $row['LastName'];
    $_SESSION['user_id'] = $row['User_ID'];

    // Initializing Session with value of PHP Variable
    echo 'success';

}



?>