<?php

/*
This file gets processor status of one job and transfer it to the html for demonstration.
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project

*/

include "database.php"

$job_id=$_POST['job_id'];

$connection = connect_database();
$result = get_processor_status($connection,$job_id);

echo json_encode($result);




?>