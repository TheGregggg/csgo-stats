<?php
include './components/parse_demo_functions.php';

function parse_demo($demo_path, $freq_demo_parsed){   
    /* fonction qui analyse une demo au format json et ajoute
    les données dans la base de données
    Parametres:
        le chemin de la demo.json
        la frequence à laquel elle a été convertit
    Renvoie l'id de l'entité demo franchement ajouté à la BDD
    */

    include '_bdd_info.php';
    // connexion à la BDD
    try {
        $bdd = new PDO("mysql:dbname=$dbBase;host=$dbHost", $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $bdd->exec('SET NAMES utf8');
    } catch ( PDOException $e ) {
        echo 'Échec connexion PDO : ' . $e->getMessage() . "<br>\n";	
    }

    // Ouvre le fichier json et le décode
    $string = file_get_contents($demo_path);
    $demo = json_decode($string, true);

    $demo_map = $demo['header']['map'];
    $demo_tickrate = $demo['header']['tickRate'];

    // Ajoute la demo dans la BDD
    $request = $bdd->prepare("INSERT INTO $dbBase.Demos (Date, FK_Map) VALUES (?, ?)");
    try {
        $bdd->beginTransaction();
        $request->execute([date("Y-m-d"), $demo_map]);
        $demo_id = $bdd->lastInsertId();
        $bdd->commit();               
    }catch (Exception $e){
        $bdd->rollback();
        throw $e;
    }

    // Ajoute les joueurs dans la BDD si ils ne sont pas deja dedans,
    // il y a la possibilité que la demo soit corronpu et que deux entité dans la partie soit le meme joueur
    // on verifie donc pour pas ajouté deux fois le meme joueur.
    // De plus si cest la deuxieme partie d'un joueur ajouté à la base, il est deja dedans.
    $players = []; // players[id dans la partie] -> nom dans la BDD
    foreach ($demo['entities'] as $player){
        if( ! array_key_exists("isNpc", $player)){ // n'ajoute pas les spéctateur à la BDD
            $player_ingame_id = $player['id'];
            $player_name = $player['name'];

            //fait une requete avec le nom du joueur
            $request = "SELECT * FROM $dbBase.Players WHERE Name='$player_name' ;";
            $req = $bdd->prepare($request);
            $req->execute();
            //si il y a aucun résultat alors on l'ajoute
            if(count($req->fetchAll()) == 0){
                $request = $bdd->prepare("INSERT INTO $dbBase.Players (Name) VALUES (?)");
                try {
                    $bdd->beginTransaction();
                    $request->execute([$player_name]);
                    $bdd->commit();             
                }catch (Exception $e){
                    $bdd->rollback();
                    throw $e;
                }
            }

            $players[$player_ingame_id] = $player_name;
            
            // Ajoute le joueur dans la table de relation Joueur_in_Demo 
            // pour indique que ce joueur joue dans cette partie
            // la aussi, on vérifi si il n'est pas deja dedans à cause des bugs de demo
            $request = "SELECT * FROM $dbBase.Player_in_Demo WHERE FK_Player='$player_name' AND FK_Demo='$demo_id' ;";
            $req = $bdd->prepare($request);
            $req->execute();
            //si pas de résultat alors on l'ajoute
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

    // On va boucler pour chaque tick de la partie et ragarder si il ce passe 
    // un évènement à chaque tick et réagir en conséquences
    foreach($demo['ticks'] as $tick_obj){
        // regarde si il existe un évènement "round_started" dans le tick actuel
        $start_round_event = get_event_in_tick($tick_obj, "round_started");
        if( $start_round_event ){ 
            //si oui, on créée un round dans la BDD lié à cette demo
            $request = $bdd->prepare("INSERT INTO $dbBase.Rounds (tick_start, winner, FK_Demo) VALUES (?, ?, ?)");
            try {
                $bdd->beginTransaction();
                $request->execute([$tick_obj['nr'], 0, $demo_id]); //$tick_obj['nr'] correspond au tick en jeu de obj tick que l'on etudie
                $round_id = $bdd->lastInsertId(); //récupère l'id du round fraichement créé
                $bdd->commit();        
            }catch (Exception $e){
                $bdd->rollback();
                throw $e;
            }

            //Ajoute les joueurs ainsi que leur side sur ce round
            foreach($players as $player_id=>$player_name){
                /*
                Pour récupérer les informations sur un joueur, on va regardé dans les snapshots de la parties
                Ces snapshots sont pris à interval régulière, ici cest $freq_demo_parsed
                Le tick actuel est celui en jeu et on aimerait avoir l'info du tick le plus proche de celui ci par les snapshots
                On calcul donc le ratio entre la freq de la demo initial et converti (freq des snapshots), ici $demo_tickrate/$freq_demo_parsed
                Et on divise le tick actuel par ce ratio, on le transform ensuit en entier
                Comme les snapshots est une liste on peut directement appelé la snapshot 
                au tick voulu avec le nombre calculé precedement

                Ce calcul est réalisé plusieurs fois, et c'est pour la même chose à chaque fois
                */

                $tick = intval( $tick_obj['nr']/( $demo_tickrate/$freq_demo_parsed ));

                //La encore on vérifie si le joueur n'est pas dedans pour les mêmes raisons de bugs
                $request = "SELECT * FROM $dbBase.Player_in_Round WHERE FK_Player='$player_name' AND FK_Round='$round_id' ;";
                $req = $bdd->prepare($request);
                $req->execute();
                if(count($req->fetchAll()) == 0){
                    $request = $bdd->prepare("INSERT INTO $dbBase.Player_in_Round (FK_Player, FK_Round, side) VALUES (?, ?, ?)");
                    try {
                        $bdd->beginTransaction();
                        $player_side = get_last_player_side($demo, $tick, $player_id); // récupère le side du joueur
                        $request->execute([$player_name, $round_id, $player_side]);
                        $bdd->commit();             
                    }catch (Exception $e){
                        $bdd->rollback();
                        throw $e;
                    }
                }
            }
        }

        // regarde si il existe un évènement "round_ended" dans le tick actuel
        $end_round_event = get_event_in_tick($tick_obj, "round_ended");
        if( $end_round_event ){
            //si oui, on récupère le gagnant et on modifie le round actuel pour y mettre le gagnant
            $winner = get_attr_in_event($end_round_event, 'winner')['numVal'];

            $request = $bdd->prepare("UPDATE $dbBase.Rounds SET winner=$winner WHERE id=$round_id");
            try {
                $bdd->beginTransaction();
                $request->execute();
                $bdd->commit();        
            }catch (Exception $e){
                $bdd->rollback();
                throw $e;
            }
        }

        // regarde si il existe un évènement "kill" dans le tick actuel
        $kill_event = get_event_in_tick($tick_obj, "kill");
        if( $kill_event and isset($round_id) ){
            //si oui et que un round à deja été créé (la encore possible avec les bugs de démo)
            //récupère l'id du tueur
            $killer_id = get_attr_in_event($kill_event, 'killer');
            if($killer_id){
                // si il y a un id alors, la on fait la logique pour les évènement 'kill'
                // il est possible que il y est un évènement kill sans tueur
                $killer_id = $killer_id['numVal'];
                $victim_id = get_attr_in_event($kill_event, 'victim')['numVal'];
                $weapon_id = get_attr_in_event($kill_event, 'weapon')['numVal'];

                $tick = intval( $tick_obj['nr']/( $demo_tickrate/$freq_demo_parsed ));
                $killer_pos = get_last_player_pos($demo, $tick, $killer_id);
                $victim_pos = get_last_player_pos($demo, $tick, $killer_id);

                //on ajoute le kill dans la BDD avec les infos correspondantes
                $request = $bdd->prepare("INSERT INTO $dbBase.Kills (tick, k_pos_x, k_pos_y, v_pos_x, v_pos_y, FK_round, FK_killer, FK_Killed_with_weapon, FK_victim) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                try {
                    $bdd->beginTransaction();
                    $request->execute([$tick_obj['nr'], $killer_pos['x'], $killer_pos['y'], $victim_pos['x'], $victim_pos['y'], $round_id, $players[$killer_id], $weapon_id, $players[$victim_id]]);
                    $bdd->commit();     
                }catch (Exception $e){
                    $bdd->rollback();
                    throw $e;
                }
            }
        }
    }

    // analyse terminé, renvoie l'id de la demo
    return $demo_id;
}
?>