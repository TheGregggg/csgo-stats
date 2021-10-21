<html>
<head>
	<title> Installation de la base de données </title>
</head>
<body>
<?php

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
	
	echo "<h1>Creation des tables</h1>";
	// Suppresion des tables existantes
	$requete = "
        DROP TABLE IF EXISTS 
        $dbBase.Player_in_Round,
        $dbBase.Player_in_Demo, 
        $dbBase.Damages,
        $dbBase.Weapon, 
        $dbBase.Rounds, 
        $dbBase.Demos, 
        $dbBase.Players, 
        $dbBase.Map; ";
	$bdd->prepare($requete)->execute();
	
	echo "Tables existantes effacées (si elles existaient)<br/>";
	
	// Création de la table players
	$requete = "CREATE TABLE $dbBase.Players(
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        Name VARCHAR(255) NOT NULL
	) ENGINE = InnoDB;";
	
	$bdd->prepare($requete)->execute();
	echo "Table 'players' créée<br>";

    // Création de la table map
	$requete = "CREATE TABLE $dbBase.Map(
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        Name VARCHAR(255) NOT NULL,
        Image VARCHAR(255) NOT NULL,
        Dimensions_x INT NOT NULL,
        Dimensions_y INT NOT NULL
    ) ENGINE = InnoDB;";
	
	$bdd->prepare($requete)->execute();
	echo "Table 'map' créée<br>";

    // Création de la table weapon
	$requete = "CREATE TABLE $dbBase.Weapon(
        id INT UNSIGNED NOT NULL PRIMARY KEY,
        Name VARCHAR(255) NOT NULL,
        Description TEXT,
        Magazine_size INT,
        Damage_per_bullet INT,
        Bullet_per_seconde FLOAT
    ) ENGINE = InnoDB;";
	
	$bdd->prepare($requete)->execute();
	echo "Table 'weapon' créée<br>";

    // Création de la table demos
	$requete = "CREATE TABLE $dbBase.Demos(
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        Date DATE NOT NULL,
        FK_Map INT UNSIGNED NOT NULL,
        FOREIGN KEY (FK_Map) REFERENCES Map(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE = InnoDB;";
	
	$bdd->prepare($requete)->execute();
	echo "Table 'demos' créée<br>";

    // Création de la table rounds
	$requete = "CREATE TABLE $dbBase.Rounds(
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tick_start INT NOT NULL,
        winner VARCHAR(2) NOT NULL,
        FK_demo INT UNSIGNED NOT NULL,
    
        FOREIGN KEY(FK_demo) REFERENCES Demos(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE = InnoDB;";
	
	$bdd->prepare($requete)->execute();
	echo "Table 'rounds' créée<br>";
    
    // Création de la table Player_in_Demo
	$requete = "CREATE TABLE $dbBase.Player_in_Demo(
        FK_Player INT UNSIGNED NOT NULL,
        FK_Demo INT UNSIGNED NOT NULL,
        PRIMARY KEY (FK_Player, FK_Demo),
    
        FOREIGN KEY(FK_Player) REFERENCES Players(id)
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
        FK_Round INT UNSIGNED NOT NULL,
        FK_Player INT UNSIGNED NOT NULL,
        PRIMARY KEY (FK_Round, FK_Player),
    
        FOREIGN KEY(FK_Round) REFERENCES Rounds(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
        FOREIGN KEY(FK_Player) REFERENCES Players(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE = InnoDB;";
	
	$bdd->prepare($requete)->execute();
	echo "Table 'Player_in_Round' créée<br>";

    // Création de la table Damages
	$requete = "CREATE TABLE $dbBase.Damages(
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tick INT NOT NULL,
        HS TINYINT(1) NOT NULL,
        damages INT NOT NULL,
        killed TINYINT(1) NOT NULL,
        k_pos_x INT NOT NULL,
        k_pos_y INT NOT NULL,
        v_pos_x INT NOT NULL,
        v_pos_y INT NOT NULL,
        FK_round INT UNSIGNED NOT NULL,
        FK_killer INT UNSIGNED NOT NULL,
        FK_Killed_with_weapon INT UNSIGNED NOT NULL,
        FK_victim INT UNSIGNED NOT NULL,
    
        FOREIGN KEY(FK_round) REFERENCES Rounds(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
        FOREIGN KEY(FK_Killed_with_weapon) REFERENCES Weapon(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
        FOREIGN KEY(FK_killer) REFERENCES Players(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
        FOREIGN KEY(FK_victim) REFERENCES Players(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE = InnoDB;";
	
	$bdd->prepare($requete)->execute();
	echo "Table 'Damages' créée<br>";
	
	echo "<h1>Ajout des Valeurs</h1>";

    

	$request = $bdd->prepare("INSERT INTO $dbBase.Weapon (id, Name, Description, Magazine_size, Damage_per_bullet, Bullet_per_seconde) VALUES (?,?,?,?,?,?)");
	try {
		$bdd->beginTransaction();

        $weapon_json_file = file_get_contents("weapon.json");
        $weapon_json = json_decode($weapon_json_file, true);
        foreach($weapon_json as $weapon_name=>$weapon_stats){
            $magazine_size = 0;
            if($weapon_stats['magazine_size'] !== ""){
                $magazine_size = intval($weapon_stats['magazine_size']);
            }

            $Damage_per_bullet = 0;
            if($weapon_stats['Damage_per_bullet'] !== ""){
                $Damage_per_bullet = intval($weapon_stats['Damage_per_bullet']);
            }

            $Bullet_per_Sec = 0;
            if($weapon_stats['Bullet_per_Sec'] !== ""){
                $Bullet_per_Sec = 1/floatval($weapon_stats['Bullet_per_Sec']);
            }
            $val = [$weapon_stats['id'], $weapon_name, $weapon_stats['description'], $magazine_size, $Damage_per_bullet, $Bullet_per_Sec];
            $request->execute($val);
        }

		$bdd->commit(); // Valide la modification de la base de données
		echo "Valeurs de la table groupe ajoutées.<br>";
	}catch (Exception $e){
		$bdd->rollback(); // en cas d'érreur, annule les modifications.
		throw $e;
	}
	
	echo "Redirection vers la page d'accueil dans 3 secondes...";
	header('Refresh:3; url=./');
	exit();
	
} catch ( PDOException $e ) {
	echo 'Échec connexion PDO : ' . $e->getMessage() . "<br>\n";	
}
?>
</body>
</html>
