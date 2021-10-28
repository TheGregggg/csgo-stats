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
    header("Location: ../"); //redirect if no demo id in url
}

$request = "DELETE FROM Player_in_Demo WHERE FK_demo='$demo_id';";
$req = $bdd->prepare($request);
$req->execute();

$request = "DELETE pr.* FROM Player_in_Round AS pr JOIN Rounds AS r ON pr.FK_Round = r.id WHERE r.FK_demo='$demo_id';";
$req = $bdd->prepare($request);
$req->execute();

$request = "DELETE k.* FROM Kills AS k JOIN Rounds AS r ON k.FK_Round = r.id WHERE r.FK_demo='$demo_id';";
$req = $bdd->prepare($request);
$req->execute();

$request = "DELETE FROM Rounds WHERE FK_demo='$demo_id';";
$req = $bdd->prepare($request);
$req->execute();

$request = "DELETE FROM Demos WHERE id='$demo_id';";
$req = $bdd->prepare($request);
$req->execute();

header("Location: ../parties"); //redirect if no new name in post
?>