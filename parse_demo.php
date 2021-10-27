<?php

include './components/demo_parser.php';

$demo_path = $_FILES['demo']['tmp_name'];
$freq_demo_parsed = 16;

if (!file_exists('temp')) {
    mkdir('temp');
}

// DEMO TO JSON PARSING
//detect the os for cmd execution
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { //its windows
    exec('powershell.exe -executionpolicy bypass -NoProfile -Command "./bin/windows/csminify -demo '. $demo_path .' -freq '. $freq_demo_parsed .' -out ./temp/demo.json"');
}else{ //assuming its linux
    exec('./bin/linux/csminify -demo '. $demo_path .' -freq '. $freq_demo_parsed .' -out ./temp/demo.json');
}

$demo_id = parse_demo("./temp/demo.json", $freq_demo_parsed);

header("Refresh:0; url=./demo?id=$demo_id");
exit();

?>