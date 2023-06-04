<?php
if ($_SERVER['REQUEST_METHOD'] !== "POST" || !isset($_POST['action'])) {
    die(header("HTTP/1.1 404"));
}

require_once "./_GLOBAL.php";
header("Access-Control-Allow-Origin: *");

switch ($_POST['action']) {
    case 'allOptions':
        getAllOptions();
        break;

    case 'newOption';
        addOption();
        break;

    case 'editOption';
        editOption();
        break;

    default:
        die(json_encode(['status' => 'error', 'type' => 'wrong-request']));
        break;
}

function getAllOptions()
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_OPTIONS_TABLE;

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);


    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $query = "SELECT `id`,`name`,`code`,`status` FROM `{$G_OPTIONS_TABLE}` WHERE 1 ;";

    $result = mysqli_query($conn, $query);

    $numberOfResults = mysqli_num_rows($result);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    // print_r($data);


    die(json_encode(['status' => 'success', 'numberOfResults' => $numberOfResults, 'data' => $data]));
}


function editOption()
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_OPTIONS_TABLE;

    if (!isset($_POST['id'])  || !isset($_POST['name']) || !isset($_POST['code'])|| !isset($_POST['status'])) {
        die(json_encode(['status' => 'error', 'type' => 'parameter-not-found']));
    }

    $id = $_POST['id'];
    $name = $_POST['name'];
    $code = $_POST['code'];
    $status = $_POST['status'];

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);

    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $query = "UPDATE `{$G_OPTIONS_TABLE}` SET `name` = '$name', `code` = '$code', `status` = '$status', `last_edited_at` = current_timestamp() WHERE `id` = '$id';";

    $result = mysqli_query($conn, $query);

    if ($result === false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }


    die(json_encode(['status' => 'success']));
}


function addOption()
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_OPTIONS_TABLE;

    if (!isset($_POST['name']) || !isset($_POST['code']) ) {
        die(json_encode(['status' => 'error', 'type' => 'parameter-not-found']));
    }

    $name = $_POST['name'];
    $code = $_POST['code'];

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);

    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $query = "INSERT INTO {$G_OPTIONS_TABLE} (`name`,`code`,`created_at`,`last_edited_at`)
    VALUES ('$name','$code',current_timestamp(),current_timestamp());";

    $result = mysqli_query($conn, $query);

    if ($result === false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $query = "SELECT `id` FROM `{$G_OPTIONS_TABLE}` ORDER BY `id` DESC LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result === false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $data = mysqli_fetch_all($result);

    $firstRow = $data[0];
    $id = $firstRow[0];

    die(json_encode(['status' => 'success', 'id' => $id]));
}
