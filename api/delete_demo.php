<?php

include '../_bdd_info.php';
//Connexion à la base de données
try {
	$bdd = new PDO("mysql:dbname=$dbBase;host=$dbHost", $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	$bdd->exec('SET NAMES utf8');
} catch ( PDOException $e ) {
	echo 'Échec connexion PDO : ' . $e->getMessage() . "<br>\n";
}

$demo_id = $_GET['id'];
if(! isset($demo_id)){
    header("Location: ../"); //redirige si pas d'id dans l'url
}

//supprime la relation joueurs - demo pour cette demo
$request = "DELETE FROM Player_in_Demo WHERE FK_demo='$demo_id';";
$req = $bdd->prepare($request);
$req->execute();

//supprime la relation joueurs - round où le round est lié à cette demo
$request = "DELETE pr.* FROM Player_in_Round AS pr JOIN Rounds AS r ON pr.FK_Round = r.id WHERE r.FK_demo='$demo_id';";
$req = $bdd->prepare($request);
$req->execute();

//supprime les kills lié aux round lié à cette demo
$request = "DELETE k.* FROM Kills AS k JOIN Rounds AS r ON k.FK_Round = r.id WHERE r.FK_demo='$demo_id';";
$req = $bdd->prepare($request);
$req->execute();

//supprime les rounds lié à cette demo
$request = "DELETE FROM Rounds WHERE FK_demo='$demo_id';";
$req = $bdd->prepare($request);
$req->execute();

//supprime la demo
$request = "DELETE FROM Demos WHERE id='$demo_id';";
$req = $bdd->prepare($request);
$req->execute();

//supprime les joueurs qui non plus aucune demo dans la bdd
$request = "DELETE p.* 
FROM Players AS p 
LEFT JOIN Player_in_Demo AS pd ON p.Name = pd.FK_player
WHERE pd.FK_player IS NULL;";
$req = $bdd->prepare($request);
$req->execute();

header("Location: ../parties"); //redirige vers la page des parties
?>