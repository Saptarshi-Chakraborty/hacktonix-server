<?php
if ($_SERVER['REQUEST_METHOD'] !== "POST" || !isset($_POST['data'])) {
    die(header("HTTP/1.1 404"));
}

require_once "./_GLOBAL.php";
require_once "./utils.php";
header("Access-Control-Allow-Origin: *", true);

$data = $_POST['data'];

try {
    $voterDetails = validateQrData($data);
    // var_dump($voterDetails);

    if ($voterDetails === null) {
        die(json_encode(['status' => 'error', 'type' => 'encryption-error']));
    }


    $voterDataArray = explode(',', $voterDetails); //seperates a string by (,) and make an array with each separated part

    if (count($voterDataArray) != 6)
        die(json_encode(['status' => 'error', 'type' => 'encryption-error']));
} catch (\Throwable $th) {
    //throw $th;
    die(json_encode(['status' => 'error', 'type' => 'encryption-error']));
}

// Seperate all data from the voterDataArray to different variables
$voterId = $voterDataArray[0];
$voterName = $voterDataArray[1];
$fatherName = $voterDataArray[2];
$voterGender = $voterDataArray[3];
$voterDob = $voterDataArray[4];
$voterUniqueKey = $voterDataArray[5];

$isOldVoterCard = false;

// Now fetch one by one data for the voter
$voter = getVoterDetails($voterId);
// print_r($voter);
// print_r($voterId);

// If Voter is not active 
if ($voter['status'] != 'active') {
    die(json_encode([
        'status' => 'success',
        'id' => $voterId,
        'name' => $voterName,
        'fatherName' => $fatherName,
        'gender' => $voterGender,
        'dob' => $voterDob,
        'uniquekey' => $voterUniqueKey,
        'isOldVoterCard' => $isOldVoterCard,
        'voterStatus' => $voter['status']
    ]));
}

// Check if all the data of voter card is up to date with server
if ($voterName != $voter['name'] || $fatherName != $voter['father_name'] || $voterGender != $voter['gender'] || $voterDob != $voter['dob']) {
    $isOldVoterCard = true;
    $voterName = $voter['name'];
    $fatherName = $voter['father_name'];
    $voterGender = $voter['gender'];
    $voterDob = $voter['dob'];
}

$votingDetails = getVotingDetails($voterId, $voter['booth']);
// echo json_encode($votingDetails);

die(json_encode([
    'status' => 'success',
    'id' => $voterId,
    'name' => $voterName,
    'fatherName' => $fatherName,
    'gender' => $voterGender,
    'dob' => $voterDob,
    'uniquekey' => $voterUniqueKey,
    'isOldVoterCard' => $isOldVoterCard,
    'voterStatus' => $voter['status'],
    'boothName' => $votingDetails['boothName'],
    'boothCode' => $voter['booth'],
    'boothArea' => $votingDetails['boothArea'],
    'boothStatus' => $votingDetails['status'],
    'options' => $votingDetails['options']
]));




function getVoterDetails(string $id)
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_VOTERS_TABLE, $G_BOOTH_TABLE;

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);

    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    // Get Voter Booth
    $query = "SELECT `name`,`father_name`,`gender`,`dob`,`booth`,`status` from `$G_VOTERS_TABLE` WHERE `id` = '$id';";
    $result = mysqli_query($conn, $query);

    if ($result === false || mysqli_num_rows($result) != 1) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    return (mysqli_fetch_all($result, MYSQLI_ASSOC)[0]);
}


function getVotingDetails($id, $booth)
{
    global $G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME, $G_BOOTH_TABLE, $G_OPTIONS_TABLE;

    $conn = mysqli_connect($G_DATABSE_HOSTNAME, $G_DATABSE_USERNAME, $G_DATABSE_PASSWORD, $G_DATABSE_DATABASE_NAME);

    if ($conn == false) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    // Get Booth Details
    $query = "SELECT `name`,`area`,`options`,`status` from `$G_BOOTH_TABLE` WHERE `code` = '$booth';";
    $result = mysqli_query($conn, $query);

    if ($result === false || mysqli_num_rows($result) != 1) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $booth = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
    // print_r($booth);

    if ($booth['status'] != 'active') {
        return ['status' => $booth['status'], 'boothName' => $booth['name'], 'boothArea' => $booth['area'], 'options' => null];
    }

    $allOptionCodes = explode(",", $booth['options']);
    $numberOfOptions = count($allOptionCodes);
    // echo $numberOfOptions;

    $optionsQueryString = "";
    for ($i = 0; $i < $numberOfOptions; $i++) {
        $optionsQueryString .= '"' . $allOptionCodes[$i] . '"';

        if ($i !== $numberOfOptions - 1)
            $optionsQueryString .= ",";
    }

    // Get Options Details
    $query = "SELECT `name` FROM $G_OPTIONS_TABLE WHERE `options`.`code` IN ($optionsQueryString);";
    // echo $query;

    $result = mysqli_query($conn, $query);
    if ($result === false || mysqli_num_rows($result) != $numberOfOptions) {
        die(json_encode(['status' => 'error', 'type' => 'database']));
    }

    $dbValues = mysqli_fetch_all($result, MYSQLI_ASSOC);
    // var_dump($dbValues);
    // die;
    $allOptions = [];
    for ($i = 0; $i < $numberOfOptions; $i++) {
        $newOption = ['name' => $dbValues[$i]['name'], 'code' => $allOptionCodes[$i]];

        array_push($allOptions, $newOption);
    }

    return ['status' => $booth['status'], 'boothName' => $booth['name'], 'boothArea' => $booth['area'], 'options' => $allOptions];
}
