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

$new_name = $_POST['demo-name'];
if(! isset($new_name)){
    header("Location: ../"); //redirige si pas de nom dans le post
}

//modifie le nom de la démo avec son id
$request = "UPDATE Demos SET Name = '$new_name' WHERE id='$demo_id';";
$req = $bdd->prepare($request);
$req->execute();

header("Location: ../partie?id=$demo_id"); //redirige vers la page de la demo
?>