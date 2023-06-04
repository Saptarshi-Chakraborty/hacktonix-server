<?php
if ($_SERVER['REQUEST_METHOD'] !== "POST" || !isset($_POST['data'])) {
    die(header("HTTP/1.1 404"));
}

require_once "./_GLOBAL.php";
require_once "./utils.php";
header("Access-Control-Allow-Origin: *");

$data = $_POST['data'];

$voterDetails = validateQrData($data);

if ($voterDetails === null)
    die(json_encode(['status' => 'error', 'type' => 'encryption-error']));
else {
    $voterData = explode(',', $voterDetails);

    $voterId = $voterData[0];
    $voterName = $voterData[1];
    $fatherName = $voterData[2];
    $voterGender = "";

    switch ($voterData[3]) {
        case 'm':
            $voterGender = "Male";
            break;
        case 'f':
            $voterGender = "Female";
            break;
        case 't':
            $voterGender = "Transgender";
            break;
        case 'o':
            $voterGender = "Other";
            break;

        default:
            $voterGender = "Other";
            break;
    }

    $voterDob = $voterData[4];
    $voterUniqueKey = $voterData[5];


    $votingOptions = getVotingDetails($voterId);
    // die(json_encode(['status' => 'success', 'data' => $voterDetails, 'votingOptions' => $votingOptions]));
    die(json_encode(['status' => 'success', 'id' => $voterId, 'name' => $voterName, 'fatherName' => $fatherName, 'gender' => $voterGender, 'dob' => $voterDob, 'uniquekey' => $voterUniqueKey, 'booth' => $votingOptions['booth'], 'options' => $votingOptions['options']]));
}


function getVotingDetails($id)
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_VOTERS_TABLE, $G_BOOTH_TABLE;

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);

    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    // Get Voter Booth
    $query = "SELECT `booth` from `$G_VOTERS_TABLE` WHERE `id` = '$id';";
    $result = mysqli_query($conn, $query);

    if ($result === false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }
    $booth = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['booth'];
    // var_dump($booth);

    // Get Booth Options
    $query = "SELECT `options` from `$G_BOOTH_TABLE` WHERE `code` = '$booth';";
    $result = mysqli_query($conn, $query);

    if ($result === false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $optionString = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['options'];
    $options = explode(',', $optionString);

    return ['booth' => $booth, 'options' => $options];
}
