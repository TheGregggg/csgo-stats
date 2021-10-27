<?php
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

    
    // code for each event like kill or end round
    foreach($demo['ticks'] as $tick_obj){
        $start_round_event = get_event_in_tick($tick_obj, "round_started");
        if( $start_round_event ){
            //start round logic
            $request = $bdd->prepare("INSERT INTO $dbBase.Rounds (tick_start, winner, FK_Demo) VALUES (?, ?, ?)");
            try {
                $bdd->beginTransaction();
                $request->execute([$tick_obj['nr'], 0, $demo_id]);
                $round_id = $bdd->lastInsertId();
                $bdd->commit(); // Valide la modification de la base de données          
            }catch (Exception $e){
                $bdd->rollback(); // en cas d'érreur, annule les modifications.
                throw $e;
            }

            //add player in round for this round
            foreach($players as $player_id=>$player_name){
                $tick = intval( $tick_obj['nr']/( $demo_tickrate/$freq_demo_parsed ));

                //verify if not already in, beacause csgo demo are broken
                $request = "SELECT * FROM $dbBase.Player_in_Round WHERE FK_Player='$player_name' AND FK_Round='$round_id' ;";
                $req = $bdd->prepare($request);
                $req->execute();
                if(count($req->fetchAll()) == 0){
                    $request = $bdd->prepare("INSERT INTO $dbBase.Player_in_Round (FK_Player, FK_Round, side) VALUES (?, ?, ?)");
                    try {
                        $bdd->beginTransaction();
                        $player_side = get_last_player_side($demo, $tick, $player_id);
                        $request->execute([$player_name, $round_id, $player_side]);
                        $bdd->commit(); // Valide la modification de la base de données               
                    }catch (Exception $e){
                        $bdd->rollback(); // en cas d'érreur, annule les modifications.
                        throw $e;
                    }
                }
            }
        }
        $end_round_event = get_event_in_tick($tick_obj, "round_ended");
        if( $end_round_event ){
            //end round logic
            $winner = get_attr_in_event($end_round_event, 'winner')['numVal'];

            $request = $bdd->prepare("UPDATE $dbBase.Rounds SET winner=$winner WHERE id=$round_id");
            try {
                $bdd->beginTransaction();
                $request->execute();
                $bdd->commit(); // Valide la modification de la base de données          
            }catch (Exception $e){
                $bdd->rollback(); // en cas d'érreur, annule les modifications.
                throw $e;
            }
        }
        $kill_event = get_event_in_tick($tick_obj, "kill");
        if( $kill_event and isset($round_id) ){
            //kill logic
            $killer_id = get_attr_in_event($kill_event, 'killer');
            if($killer_id){
                $killer_id = $killer_id['numVal'];
                $victim_id = get_attr_in_event($kill_event, 'victim')['numVal'];
                $weapon_id = get_attr_in_event($kill_event, 'weapon')['numVal'];

                $tick = intval( $tick_obj['nr']/( $demo_tickrate/$freq_demo_parsed ));
                $killer_pos = get_last_player_pos($demo, $tick, $killer_id);
                $victim_pos = get_last_player_pos($demo, $tick, $killer_id);

                $request = $bdd->prepare("INSERT INTO $dbBase.Kills (tick, k_pos_x, k_pos_y, v_pos_x, v_pos_y, FK_round, FK_killer, FK_Killed_with_weapon, FK_victim) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                try {
                    $bdd->beginTransaction();
                    $request->execute([$tick_obj['nr'], $killer_pos['x'], $killer_pos['y'], $victim_pos['x'], $victim_pos['y'], $round_id, $players[$killer_id], $weapon_id, $players[$victim_id]]);
                    $bdd->commit(); // Valide la modification de la base de données          
                }catch (Exception $e){
                    $bdd->rollback(); // en cas d'érreur, annule les modifications.
                    throw $e;
                }
            }
        }
    }

    
    return $demo_id;
}
?>