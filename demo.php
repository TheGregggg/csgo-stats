<?php 
include '_bdd_info.php';
//Connexion à la base de données
try {
	$bdd = new PDO("mysql:dbname=$dbBase;host=$dbHost", $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	$bdd->exec('SET NAMES utf8');
} catch ( PDOException $e ) {
	echo 'Échec connexion PDO : ' . $e->getMessage() . "<br>\n";
}
$demo_id = $_GET['id'];
$request = "SELECT * FROM Demos JOIN Maps ON Demos.FK_Map = Maps.Name WHERE Demos.id='$demo_id' ;";
$req = $bdd->prepare($request);
$req->execute();
$demo_info = $req->fetchAll()[0];

$timestamp = strtotime($demo_info['Date']);

$request = "SELECT k.FK_killer as name, COUNT(k.FK_victim) as kills
 FROM Kills AS k 
 JOIN Rounds AS r ON k.FK_Round = r.id 
 JOIN Demos AS d ON r.FK_Demo = d.id 
 JOIN Weapons AS w ON k.FK_Killed_with_weapon = w.id
 WHERE d.id='$demo_id' 
 GROUP BY k.FK_killer
 ORDER BY k.FK_killer;";
$req = $bdd->prepare($request);
$req->execute();
$kills = $req->fetchAll();

$request = "SELECT k.FK_victim as name, COUNT(k.FK_killer) as death
 FROM Kills AS k 
 JOIN Rounds AS r ON k.FK_Round = r.id 
 JOIN Demos AS d ON r.FK_Demo = d.id 
 JOIN Weapons AS w ON k.FK_Killed_with_weapon = w.id
 WHERE d.id='$demo_id' 
 GROUP BY k.FK_victim
 ORDER BY k.FK_victim;";
$req = $bdd->prepare($request);
$req->execute();
$deaths = $req->fetchAll();

$players = [];
foreach($kills as $kill){
    $players[$kill['name']] = $kill;
}
foreach($deaths as $death){
    $players[$death['name']]['deaths'] = $death['death'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include './components/header_tags.php'; ?>
    <title>CSGO Stats</title>
</head>
<body>
    <?php include './components/header.php'; ?>

    <main class="container" id="demo">
        <div class="card game-info">
            <h2 class="map"><?php echo $demo_info['Name']; ?></h2>
            <p class="date">Analysé le <?php echo date('d/m/Y', $timestamp); ?></p>
        </div>
        <div class="card scores">
            <ul>
                <li>
                    <span class="left-item">Joueur</span> 
                    <span class="right-item">
                        <span class="kill">Kills</span> 
                        <span class="death">Morts</span>
                    </span> 
                </li>
                <?php foreach($players as $player){ ?>
                    <li>
                        <span class="left-item name"><?php echo $player['name']; ?></span> 
                        <span class="right-item">
                            <span class="kill"><?php echo $player['kills']; ?></span> 
                            <span class="death"><?php echo $player['deaths']; ?></span>
                        </span>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div class="card rounds"></div>
    </main>

    <?php include './components/footer.php'; ?>
    <?php include './components/scripts.php'; ?>
</body>
</html>