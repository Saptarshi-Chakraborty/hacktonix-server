<?php
if ($_SERVER['REQUEST_METHOD'] !== "POST" || !isset($_POST['action'])) {
    die(header("HTTP/1.1 404"));
}

require_once "./_GLOBAL.php";
require_once "./utils.php";

header("Access-Control-Allow-Origin: *");
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

    $query = "INSERT INTO {$G_VOTERS_TABLE} (`name`,`father_name`,`gender`,`dob`,`address`,`booth`,`created_at`,`last_edited_at`)
    VALUES ('$name', '$father_name', '$gender', '$dob', '$address','$booth',current_timestamp(),current_timestamp());";

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

    die(json_encode(['status' => 'success', 'id' => $id, 'qrData' => $voterQrData]));
}
