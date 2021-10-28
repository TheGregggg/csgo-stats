<?php

include './components/demo_parser.php';

$demo_path = $_FILES['demo']['tmp_name'];
$freq_demo_parsed = 16;

//crée dossier si il n'existe pas
if (!file_exists('temp')) {
    mkdir('temp');
}

// DEMO VERS JSON
//detect l'os pour l'utilisation du script de conversion
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { //its windows
    exec('powershell.exe -executionpolicy bypass -NoProfile -Command "./bin/windows/csminify -demo '. $demo_path .' -freq '. $freq_demo_parsed .' -out ./temp/demo.json"');
}else{ //assuming its linux
    exec('./bin/linux/csminify -demo '. $demo_path .' -freq '. $freq_demo_parsed .' -out ./temp/demo.json');
}

// parse la démo avec la fonction dans ./components/demo_parser.php et récupère l'id de la démo créée renvoyé
$demo_id = parse_demo("./temp/demo.json", $freq_demo_parsed);

//redirige vers la page de cette nouvelle démo
header("Refresh:0; url=./partie?id=$demo_id");
exit();

?>