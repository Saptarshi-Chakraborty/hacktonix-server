<?php
require_once "./_GLOBAL.php";

function getVoterQrData(string $id, string $name, string $father_name, string $gender, string $dob)
{
    global $G_ENCRYPTION_ALGO, $G_ENCRYPTION_PASSPHRASE, $G_ENCRYPTION_IV;

    $data = $id . "," . $name . "," . $father_name . "," . $gender . "," . $dob . ",";
    // echo $data . "\n";

    // Generate voter's unique-key for voting with their ID and ENCRYPTION_PASSPHRASE
    $keyData = $id . "-" . $G_ENCRYPTION_PASSPHRASE;

    $uniqueKey = hash("sha1", $keyData);
    $data .= $uniqueKey;

    // $data = $data . "," . hash("sha256", $G_ENCRYPTION_PASSPHRASE);

    $encryptedData = null;
    try {
        $encryptedData = openssl_encrypt($data, $G_ENCRYPTION_ALGO, $G_ENCRYPTION_PASSPHRASE, 0, $G_ENCRYPTION_IV);
        if ($encryptedData == false)
            return null;
    } catch (\Throwable $error) {
        $ds = $error;
    }
    return $encryptedData;
}


function validateQrData(string $data)
{
    global $G_ENCRYPTION_ALGO, $G_ENCRYPTION_PASSPHRASE, $G_ENCRYPTION_IV;
    $data =  str_ireplace("-", "+", $data);
    $encryptedData = null;
    try {
        $encryptedData = openssl_decrypt($data, $G_ENCRYPTION_ALGO, $G_ENCRYPTION_PASSPHRASE, 0, $G_ENCRYPTION_IV);

        if ($encryptedData === false || strchr($encryptedData, ",") == false)
            return null;
    } catch (\Throwable $error) {
        $ds = $error;
    }
    return $encryptedData;
}
