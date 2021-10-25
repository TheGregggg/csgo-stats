<?php
include '_bdd_info.php'; //Informations de connexion à la base de données
include './components/parse_demo_functions.php';

function parse_demo($demo_path, $freq_demo_parsed){
    
    include '_bdd_info.php';
    // connexion to db
    try {
        // Changement de la chaine de connexion : on précise la base de données
        $bdd = new PDO("mysql:dbname=$dbBase;host=$dbHost", $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $bdd->exec('SET NAMES utf8');
    } catch ( PDOException $e ) {
        echo 'Échec connexion PDO : ' . $e->getMessage() . "<br>\n";	
    }

    // JSON Data validation
    $string = file_get_contents($demo_path);
    $demo = json_decode($string, true);

    $demo_map = $demo['header']['map'];
    $demo_tickrate = $demo['header']['tickRate'];

    // Create demo entries in db
    $request = $bdd->prepare("INSERT INTO $dbBase.Demos (Date, FK_Map) VALUES (?, ?)");
    try {
        $bdd->beginTransaction();
        $request->execute([date("Y-m-d"), $demo_map]);
        $demo_id = $bdd->lastInsertId();
        $bdd->commit(); // Valide la modification de la base de données               
    }catch (Exception $e){
        $bdd->rollback(); // en cas d'érreur, annule les modifications.
        throw $e;
    }

    // Add player entries to database if there not in 
    $players = []; // player[id in game] -> name in db
    foreach ($demo['entities'] as $player){
        if( ! array_key_exists("isNpc", $player)){ // dont add non playable character to db
            $player_ingame_id = $player['id'];
            $player_name = $player['name'];

            $request = "SELECT * FROM $dbBase.Players WHERE Name='$player_name' ;";
            $req = $bdd->prepare($request);
            $req->execute();
            if(count($req->fetchAll()) == 0){
                // create player entrie in database
                $request = $bdd->prepare("INSERT INTO $dbBase.Players (Name) VALUES (?)");
                try {
                    $bdd->beginTransaction();
                    $request->execute([$player_name]);
                    $bdd->commit(); // Valide la modification de la base de données               
                }catch (Exception $e){
                    $bdd->rollback(); // en cas d'érreur, annule les modifications.
                    throw $e;
                }
            }

            $players[$player_ingame_id] = $player_name;
            
            //add player in player_in_demo table
            //verify if not already in, beacause csgo demo are broken
            $request = "SELECT * FROM $dbBase.Player_in_Demo WHERE FK_Player='$player_name' AND FK_Demo='$demo_id' ;";
            $req = $bdd->prepare($request);
            $req->execute();
            if(count($req->fetchAll()) == 0){
                $request = $bdd->prepare("INSERT INTO $dbBase.Player_in_Demo (FK_Player, FK_Demo) VALUES (?, ?)");
                try {
                    $bdd->beginTransaction();
                    $request->execute([$player_name, $demo_id]);
                    $bdd->commit(); // Valide la modification de la base de données               
                }catch (Exception $e){
                    $bdd->rollback(); // en cas d'érreur, annule les modifications.
                    throw $e;
                }
            }
        }
    }
}
?>