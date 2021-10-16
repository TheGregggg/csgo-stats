<?php

$demo_path = $_FILES['demo']['tmp_name'];

$freq_demo_parsed = 16;

// DEMO TO JSON PARSING
//detect the os for cmd execution
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { //its windows
    exec('powershell.exe -executionpolicy bypass -NoProfile -Command "./bin/windows/csminify -demo '. $demo_path .' -freq '. $freq_demo_parsed .' -out ./temp/demo.json"');
}else{ //assuming its linux
    exec('./bin/linux/csminify -demo '. $demo_path .' -freq '. $freq_demo_parsed .' -out ./temp/demo.json');
}

// JSON Data validation
$string = file_get_contents("./temp/demo.json");
$demo = json_decode($string, true);

$players = [];
foreach ($demo['entities'] as $value){
    $players[$value['id']] = $value['name'];
}
print_r($players)
?>