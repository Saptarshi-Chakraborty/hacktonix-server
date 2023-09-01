<?php
header("Access-Control-Allow-Origin: *", true);

if ($_SERVER['REQUEST_METHOD'] !== "POST" || !isset($_POST['action'])) {
    die(header("HTTP/1.1 404"));
}

require_once "./_GLOBAL.php";
require_once "./utils.php";

switch ($_POST['action']) {
    case 'allVoters':
        getAllVoters();
        break;

    case 'newVoter';
        addVoter();
        break;

    case 'editVoter';
        editVoter();
        break;

    default:
        die(json_encode(['status' => 'error', 'type' => 'wrong-request']));
        break;
}

function getAllVoters()
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_VOTERS_TABLE;

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);


    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $query = "SELECT `id`,`name`,`father_name`,`gender`,`dob`,`address`,`dod`,`booth`,`status` FROM `{$G_VOTERS_TABLE}` WHERE 1 ;";

    $result = mysqli_query($conn, $query);

    $numberOfResults = mysqli_num_rows($result);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    // print_r($data);


    die(json_encode(['status' => 'success', 'numberOfResults' => $numberOfResults, 'data' => $data]));
}


function editVoter()
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_VOTERS_TABLE;

    if (!isset($_POST['id'])  || !isset($_POST['name']) || !isset($_POST['father_name'])) {
        die(json_encode(['status' => 'error', 'type' => 'parameter-not-found']));
    }

    $id = $_POST['id'];
    $name = $_POST['name'];
    $father_name = $_POST['father_name'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $booth = $_POST['booth'];
    $status = $_POST['status'];

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);

    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }


    $query = "UPDATE `{$G_VOTERS_TABLE}` SET `name` = '$name', `father_name` = '$father_name', `gender` = '$gender', `dob` = '$dob', `address` = '$address', `booth` = '$booth', `status` = '$status', `last_edited_at` = current_timestamp() WHERE `id` = '$id';";

    // echo $query;
    // echo "\n";
    $result = mysqli_query($conn, $query);

    if ($result === false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $voterQrData = getVoterQrData($id, $name, $father_name, $gender, $dob);
    $voterQrData =  str_ireplace("+", "-", $voterQrData);

    die(json_encode(['status' => 'success', 'qrData' => $voterQrData]));
}


function addVoter()
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_VOTERS_TABLE;

    if (!isset($_POST['name']) || !isset($_POST['father_name'])) {
        die(json_encode(['status' => 'error', 'type' => 'parameter-not-found']));
    }

    $name = $_POST['name'];
    $father_name = $_POST['father_name'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $booth = $_POST['booth'];

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);

    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $voterImages = uploadImages();
    // concatinate all the images name with comma
    $voterImagesForDatabase = implode(",", $voterImages);

    

    $query = "INSERT INTO {$G_VOTERS_TABLE} (`name`,`father_name`,`gender`,`dob`,`address`,`booth`,`created_at`,`last_edited_at`,`images`)
    VALUES ('$name', '$father_name', '$gender', '$dob', '$address','$booth',current_timestamp(),current_timestamp(),'$voterImagesForDatabase');";

    // echo $query;
    // echo "\n";

    $result = mysqli_query($conn, $query);

    if ($result === false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $query = "SELECT `id` FROM `{$G_VOTERS_TABLE}` ORDER BY `id` DESC LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result === false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $data = mysqli_fetch_all($result);

    $firstRow = $data[0];
    $id = $firstRow[0];

    $voterQrData = getVoterQrData($id, $name, $father_name, $gender, $dob);
    $voterQrData =  str_ireplace("+", "-", $voterQrData);

    die(json_encode(['status' => 'success', 'id' => $id, 'qrData' => $voterQrData, 'images' => $voterImages]));
}

// we are getting 4 file inputes with names image1, image2, image3, image4
// now write a function that will move all the image to the img/people/ folder and return the new names of the images as an array
// if any error occurs then return null
function uploadImages()
{
    global $G_VOTER_IMAGES_FOLDER;
    
    $target_dir = $G_VOTER_IMAGES_FOLDER;

    // move all the files
    $image1 = $_FILES["image1"];
    $image2 = $_FILES["image2"];
    $image3 = $_FILES["image3"];
    $image4 = $_FILES["image4"];

    $image1Name = basename($image1["name"]);
    $image2Name = basename($image2["name"]);
    $image3Name = basename($image3["name"]);
    $image4Name = basename($image4["name"]);

    $image1FileType = strtolower(pathinfo($image1Name, PATHINFO_EXTENSION));
    $image2FileType = strtolower(pathinfo($image2Name, PATHINFO_EXTENSION));
    $image3FileType = strtolower(pathinfo($image3Name, PATHINFO_EXTENSION));
    $image4FileType = strtolower(pathinfo($image4Name, PATHINFO_EXTENSION));

    $image1TargetFile = $target_dir . uniqid() . "." . $image1FileType;
    $image2TargetFile = $target_dir . uniqid() . "." . $image2FileType;
    $image3TargetFile = $target_dir . uniqid() . "." . $image3FileType;
    $image4TargetFile = $target_dir . uniqid() . "." . $image4FileType;

    $uploadOk = 1;


    // no need to check if it is a image or not because we have already checked it in the frontend

    // Check if file already exists
    if (file_exists($image1TargetFile) || file_exists($image2TargetFile) || file_exists($image3TargetFile) || file_exists($image4TargetFile)) {
        return null;
    }

    // now move the files
    if (!move_uploaded_file($image1["tmp_name"], $image1TargetFile)) {
        return null;
    }

    if (!move_uploaded_file($image2["tmp_name"], $image2TargetFile)) {
        return null;
    }

    if (!move_uploaded_file($image3["tmp_name"], $image3TargetFile)) {
        return null;
    }

    if (!move_uploaded_file($image4["tmp_name"], $image4TargetFile)) {
        return null;
    }

    // now make the paths withouth the img/people/ part
    $image1TargetFile = str_ireplace("img/people/", "", $image1TargetFile);
    $image2TargetFile = str_ireplace("img/people/", "", $image2TargetFile);
    $image3TargetFile = str_ireplace("img/people/", "", $image3TargetFile);
    $image4TargetFile = str_ireplace("img/people/", "", $image4TargetFile);


    // write all paths in abc.txt
    // $myfile = fopen("abc.txt", "w") or die("Unable to open file!");
    // fwrite($myfile, $image1TargetFile . "\n");
    // fwrite($myfile, $image2TargetFile . "\n");
    // fwrite($myfile, $image3TargetFile . "\n");
    // fwrite($myfile, $image4TargetFile . "\n");
    // fclose($myfile);


    return [$image1TargetFile, $image2TargetFile, $image3TargetFile, $image4TargetFile];
    
}
