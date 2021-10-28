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

$new_name = $_POST['demo-name'];
if(! isset($new_name)){
    header("Location: ../"); //redirect if no new name in post
}

$request = "UPDATE Demos SET Name = '$new_name' WHERE id='$demo_id';";
$req = $bdd->prepare($request);
$req->execute();

header("Location: ../demo?id=$demo_id"); //redirect if no new name in post
?>