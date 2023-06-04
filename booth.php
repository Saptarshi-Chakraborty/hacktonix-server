<?php
if ($_SERVER['REQUEST_METHOD'] !== "POST" || !isset($_POST['action'])) {
    die(header("HTTP/1.1 404"));
}

require_once "./_GLOBAL.php";
header("Access-Control-Allow-Origin: *");

switch ($_POST['action']) {
    case 'allBooth':
        getAllBooth();
        break;

    case 'newBooth';
        addBooth();
        break;

    case 'editBooth';
        editBooth();
        break;

    case 'allActiveBooths';
        getAllActiveBooths();
        break;

    default:
        die(json_encode(['status' => 'error', 'type' => 'wrong-request']));
        break;
}

function getAllBooth()
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_BOOTH_TABLE;

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);


    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $query = "SELECT `id`,`name`,`code`,`area`,`options`,`status` FROM `{$G_BOOTH_TABLE}` WHERE 1 ;";

    $result = mysqli_query($conn, $query);

    $numberOfResults = mysqli_num_rows($result);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    // print_r($data);


    die(json_encode(['status' => 'success', 'numberOfResults'    => $numberOfResults, 'data' => $data]));
    // die(json_encode(['status' => 'success', 'numberOfResults' => 0, 'data' => $data]));
}


function editBooth()
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_BOOTH_TABLE;

    if (!isset($_POST['id'])  || !isset($_POST['name']) || !isset($_POST['code'])) {
        die(json_encode(['status' => 'error', 'type' => 'parameter-not-found']));
    }

    $id = $_POST['id'];
    $name = $_POST['name'];
    $code = $_POST['code'];
    $area = $_POST['area'];
    $options = $_POST['options'];
    $status = $_POST['status'];

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);

    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $query = "UPDATE `{$G_BOOTH_TABLE}` SET `name` = '$name', `code` = '$code', `area` = '$area', `options` = '$options', `status` = '$status', `last_edited_at` = current_timestamp() WHERE `id` = '$id';";

    $result = mysqli_query($conn, $query);

    if ($result === false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }


    die(json_encode(['status' => 'success']));
}


function addBooth()
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_BOOTH_TABLE;

    if (!isset($_POST['name']) || !isset($_POST['code']) || !isset($_POST['options'])) {
        die(json_encode(['status' => 'error', 'type' => 'parameter-not-found']));
    }

    $name = $_POST['name'];
    $code = $_POST['code'];
    $area = $_POST['area'];
    $options = $_POST['options'];

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);

    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $query = "INSERT INTO {$G_BOOTH_TABLE} (`name`,`code`,`area`,`options`,`created_at`,`last_edited_at`)
    VALUES ('$name', '$code', '$area', '$options',current_timestamp(),current_timestamp());";

    $result = mysqli_query($conn, $query);

    if ($result === false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $query = "SELECT `id` FROM `{$G_BOOTH_TABLE}` ORDER BY `id` DESC LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result === false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $data = mysqli_fetch_all($result);

    $firstRow = $data[0];
    $id = $firstRow[0];

    die(json_encode(['status' => 'success', 'id' => $id]));
}

function getAllActiveBooths()
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_BOOTH_TABLE;

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);


    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $query = "SELECT `name`,`code`,`area` FROM `{$G_BOOTH_TABLE}` WHERE `status`='active' ;";

    $result = mysqli_query($conn, $query);

    $numberOfResults = mysqli_num_rows($result);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    // print_r($data);


    die(json_encode(['status' => 'success', 'numberOfResults'    => $numberOfResults, 'data' => $data]));
}
