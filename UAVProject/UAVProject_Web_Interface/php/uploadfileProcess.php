<?php
/*
This file is charge of processing the uploading file.
Created by :
Hongzhen Xie -773383
Gaojie Sun -741368
Software Development of COMP90055
Course Work Project 
*/

//This function is used for processing uploading boundry information file.
function get_Info()
{
    $target_dir = "uploadfile/";
    $target_file = $target_dir . basename($_FILES["speciefileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

    // Check if image file is a actual image or fake image
    // Check if file already exists
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your csv file was not uploaded.";
        // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["speciefileToUpload"]["tmp_name"], $target_file)) {
            echo "The file " . basename($_FILES["speciefileToUpload"]["name"]) . " has been uploaded.\r\n";
            return basename($_FILES["speciefileToUpload"]["name"]);
        } else {
            echo "Sorry, there was an error uploading your file.\r\n";
        }
    }
}

//This file is used for processing uploading image file.
function getImage()
{
    $target_dir = "uploadfile/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
    // Check if image file is a actual image or fake image
    // Check if file already exists
    if (file_exists($target_file)) {
        echo nl2br("Sorry, file already exists." . "\n");
        $uploadOk = 0;
    }
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if ($check !== false) {
            echo nl2br("File is an image - " . $check["mime"] . ".");
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded.\r\n";
            return basename($_FILES["fileToUpload"]["name"]);
        } else {
            echo "Sorry, there was an error uploading your file.\r\n";
        }
    }
}


?>