<?php
// echo "HELLO<br>";
envExporter();





// if (!function_exists("envExporter")) {
function envExporter()
{
    $fileName = ".env";
    echo "hello<br>";
    if (!file_exists($fileName)) {
        return;
    }
    echo "FILE EXISTS<br>";

    $associateArray = [];

    $file = fopen($fileName, "r");
    $data = fread($file, filesize($fileName));
    $tempData = $data;

    while ($tempData !== '') {
        $line = strchr($tempData, "\n", true);
        echo "(" . $line . ")<br>";
        $tempData = substr($tempData, strlen($line));

        if ($tempData[0] = ' ')
            $tempData = substr($tempData, 1);
        echo "(" . $tempData . ")<br>";



        echo "(" . $line[strlen($line) - 1] . ")<br>";

        // if (strlen($tempData) !== 0) 
            $line[strlen($line) - 1] = "\0";
        

        echo "(" . $line . ")<br>";
        $equalsPosition = strpos($line, "=", 1);
        echo $equalsPosition;
        echo "<br>";

        $variableName = substr($line, 0, $equalsPosition);
        echo "(" . $variableName . ")<br>";
        $variableName = str_replace([" ", "-"], "_", $variableName);
        echo "(" . $variableName . ")<br>";
        $variableName = "G_" . $variableName;

        $variableValue = substr($line, $equalsPosition + 1);
        echo "(" . $variableValue . ")<br>";

        $GLOBALS[$variableName] = $variableValue;

        
        echo "(" . $tempData . ")<br>";
    }
    
    // print_r($GLOBALS);
    var_dump($GLOBALS);
}
        
// }
