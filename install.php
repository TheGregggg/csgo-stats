<html>
<head>
    <?php include './components/header_tags.php'; ?>
	<title> Installation de la base de données </title>
</head>
<body>
    <?php include './components/header.php'; ?>

    <main class="container">
        <div class="card">
        <?php

        include './components/demo_parser.php';

        //Informations de conenxtion à la base de données
        include '_bdd_info.php';

        //Connexion à la base de données
        try {
            //Connexion au serveur de base de données
            $bdd = new PDO("mysql:host=$dbHost", $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $bdd->exec('SET NAMES utf8');
            
            // création de la base de données
            echo "<h1>Création de la base de données</h1>";
            $request = "CREATE DATABASE IF NOT EXISTS ".$dbBase." DEFAULT CHARACTER SET utf8";
            //echo "Connexion possible avec PDO <br>\n";
            $bdd->prepare($request)->execute();
            echo "Base de données créée. <br>";
            
            // Changement de la chaine de connexion : on précise la base de données
            $bdd = new PDO("mysql:dbname=$dbBase;host=$dbHost", $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $bdd->exec('SET NAMES utf8');
            
            echo "<br>";
            echo "<h3>Creation des tables</h3>";
            // Suppresion des tables existantes
            $requete = "
                DROP TABLE IF EXISTS 
                $dbBase.Player_in_Round,
                $dbBase.Player_in_Demo, 
                $dbBase.Kills,
                $dbBase.Weapons, 
                $dbBase.Rounds, 
                $dbBase.Demos, 
                $dbBase.Players, 
                $dbBase.Maps; ";
            $bdd->prepare($requete)->execute();
            
            echo "Tables existantes effacées (si elles existaient)<br/>";
            
            // Création de la table players
            $requete = "CREATE TABLE $dbBase.Players(
                Name VARCHAR(255) NOT NULL PRIMARY KEY
            ) ENGINE = InnoDB;";
            
            $bdd->prepare($requete)->execute();
            echo "Table 'players' créée<br>";

            // Création de la table map
            $requete = "CREATE TABLE $dbBase.Maps(
                Name VARCHAR(255) NOT NULL PRIMARY KEY,
                Image VARCHAR(255) NOT NULL,
                Img_ref_x INT NOT NULL,
                Img_ref_y INT NOT NULL,
                Map_ref_x INT NOT NULL,
                Map_ref_y INT NOT NULL,
                Img_origin_x INT NOT NULL,
                Img_origin_y INT NOT NULL
            ) ENGINE = InnoDB;";
            
            $bdd->prepare($requete)->execute();
            echo "Table 'map' créée<br>";

            // Création de la table weapon
            $requete = "CREATE TABLE $dbBase.Weapons(
                id INT UNSIGNED NOT NULL PRIMARY KEY,
                Name VARCHAR(255) NOT NULL,
                Description TEXT,
                Magazine_size INT,
                Damage_per_bullet INT,
                Bullet_per_seconde FLOAT
            ) ENGINE = InnoDB;";
            
            $bdd->prepare($requete)->execute();
            echo "Table 'weapons' créée<br>";

            // Création de la table demos
            $requete = "CREATE TABLE $dbBase.Demos(
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                Date DATE NOT NULL,
                FK_Map VARCHAR(255) NOT NULL,
                FOREIGN KEY (FK_Map) REFERENCES Maps(Name)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = InnoDB;";
            
            $bdd->prepare($requete)->execute();
            echo "Table 'demos' créée<br>";

            // Création de la table rounds
            $requete = "CREATE TABLE $dbBase.Rounds(
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                tick_start INT NOT NULL,
                winner INT NOT NULL,
                FK_demo INT UNSIGNED NOT NULL,
            
                FOREIGN KEY(FK_demo) REFERENCES Demos(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = InnoDB;";
            
            $bdd->prepare($requete)->execute();
            echo "Table 'rounds' créée<br>";
            
            // Création de la table Player_in_Demo
            $requete = "CREATE TABLE $dbBase.Player_in_Demo(
                FK_Player VARCHAR(255) NOT NULL,
                FK_Demo INT UNSIGNED NOT NULL,
                PRIMARY KEY (FK_Player, FK_Demo),
            
                FOREIGN KEY(FK_Player) REFERENCES Players(Name)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                FOREIGN KEY(FK_Demo) REFERENCES Demos(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = InnoDB;";
            
            $bdd->prepare($requete)->execute();
            echo "Table 'Player_in_Demo' créée<br>";

            // Création de la table Player_in_Round
            $requete = "CREATE TABLE $dbBase.Player_in_Round(
                FK_Player VARCHAR(255) NOT NULL,
                FK_Round INT UNSIGNED NOT NULL,
                side INT NOT NULL,
                PRIMARY KEY (FK_Round, FK_Player),
            
                FOREIGN KEY(FK_Round) REFERENCES Rounds(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                FOREIGN KEY(FK_Player) REFERENCES Players(Name)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = InnoDB;";
            
            $bdd->prepare($requete)->execute();
            echo "Table 'Player_in_Round' créée<br>";

            // Création de la table Damages
            $requete = "CREATE TABLE $dbBase.Kills(
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                tick INT NOT NULL,
                k_pos_x INT NOT NULL,
                k_pos_y INT NOT NULL,
                v_pos_x INT NOT NULL,
                v_pos_y INT NOT NULL,
                FK_round INT UNSIGNED NOT NULL,
                FK_killer VARCHAR(255) NOT NULL,
                FK_Killed_with_weapon INT UNSIGNED NOT NULL,
                FK_victim VARCHAR(255) NOT NULL,
            
                FOREIGN KEY(FK_round) REFERENCES Rounds(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                FOREIGN KEY(FK_Killed_with_weapon) REFERENCES Weapons(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                FOREIGN KEY(FK_killer) REFERENCES Players(Name)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                FOREIGN KEY(FK_victim) REFERENCES Players(Name)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = InnoDB;";
            
            $bdd->prepare($requete)->execute();
            echo "Table 'kills' créée<br>";
            
            echo "<br>";
            echo "<h3>Ajout des Valeurs</h3>";
            
            $request = $bdd->prepare("INSERT INTO $dbBase.Weapons (id, Name, Description, Magazine_size, Damage_per_bullet, Bullet_per_seconde) VALUES (?,?,?,?,?,?)");
            try {
                $bdd->beginTransaction();

                $weapons_json_file = file_get_contents("db/weapons.json");
                $weapons_json = json_decode($weapons_json_file, true);
                foreach($weapons_json as $weapon_name=>$weapon_stats){
                    $Bullet_per_Sec = $weapon_stats['Bullet_per_Sec'];
                    if($Bullet_per_Sec != 0){ $Bullet_per_Sec = 1/$Bullet_per_Sec; }

                    $val = [$weapon_stats['id'], $weapon_name, $weapon_stats['description'], $weapon_stats['magazine_size'], $weapon_stats['Damage_per_bullet'], $Bullet_per_Sec];
                    $request->execute($val);
                }

                $bdd->commit(); // Valide la modification de la base de données
                echo "Valeurs de la table 'Weapons' ajoutées.<br>";

            }catch (Exception $e){
                $bdd->rollback(); // en cas d'érreur, annule les modifications.
                throw $e;
            }

            $request = $bdd->prepare("INSERT INTO $dbBase.Maps (Name, Image, Img_ref_x, Img_ref_y, Map_ref_x, Map_ref_y, Img_origin_x, Img_origin_y) VALUES (?,?,?,?,?,?,?,?)");
            try {
                $bdd->beginTransaction();

                $maps_json_file = file_get_contents("db/maps.json");
                $maps_json = json_decode($maps_json_file, true);
                foreach($maps_json as $map_name=>$map_info){
                    $val = [$map_name, $map_info['Image'], $map_info['Img_ref_x'], $map_info['Img_ref_y'], $map_info['Map_ref_x'], $map_info['Map_ref_y'], $map_info['Img_origin_x'], $map_info['Img_origin_y']];
                    $request->execute($val);
                }

                $bdd->commit(); // Valide la modification de la base de données
                echo "Valeurs de la table 'Maps' ajoutées.<br>";

            }catch (Exception $e){
                $bdd->rollback(); // en cas d'érreur, annule les modifications.
                throw $e;
            }
            
            for ($i=1; $i < 7; $i++) { 
                parse_demo("./db/games/demo$i.json", 16);
                echo "Partie $i ajoutées.<br>";
            }
            

            echo "<br>";
            echo "Redirection vers la page d'accueil dans 3 secondes...";
            header('Refresh:3; url=./');
            
        } catch ( PDOException $e ) {
            echo 'Échec connexion PDO : ' . $e->getMessage() . "<br>\n";	
        } ?>
        </div> 
    </main>
    <?php include './components/footer.php'; ?>
    <?php include './components/scripts.php'; ?>
</body>
</html>
