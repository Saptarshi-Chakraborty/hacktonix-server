<?php
if (!isset($_GET['image'])) {
    header("HTTP/1.0 404 Not Found", true);
    die;
}

header("Access-Control-Allow-Origin: *", true);

// Get the requested image filename from the URL
$imageFilename = $_GET['image'];


// Define the path to the folder containing the images
$imageFolderPath = './img/people/';

// Check if the requested image exists
if (file_exists($imageFolderPath . $imageFilename)) {
    // Get the image's MIME type
    $imageMimeType = mime_content_type($imageFolderPath . $imageFilename);

    // Set the appropriate Content-Type header
    header("Content-Type: $imageMimeType");
    // header("Access-Control-Allow-Origin: *", true);

    // Output the image content
    readfile($imageFolderPath . $imageFilename);
} else {
    // If the image doesn't exist, you could serve a default image or show an error message
    header("HTTP/1.0 404 Not Found", true);
    echo "Image not found";
}
