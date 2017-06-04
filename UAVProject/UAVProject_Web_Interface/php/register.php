<?php
/*
This file is used for register new users.
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project
*/
include "database.php";
// obtainning user info from front-end
$username = $_REQUEST['username'];
$password = $_REQUEST['password'];
$firstname = $_REQUEST['firstname'];
$lastname = $_REQUEST['lastname'];

// check whether this user has been created
$con = conncet_database();
$sql = sprintf("SELECT * FROM User_Info where AccountName = '%s'", $username);
$user_info = mysqli_query($con,$sql);

if (mysqli_fetch_array($user_info)) {
    echo 'failed';
}else{
    //if it is legal, create new user account in database
    $sql = sprintf("INSERT INTO User_Info (AccountName,AccountPassword,FirstName,LastName) VALUES ('%s','%s','%s','%s')", $username,$password,$firstname,$lastname);
    mysqli_query($con,$sql);
    echo 'success';
}



?>