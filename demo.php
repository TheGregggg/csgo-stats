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
if(! isset($demo_id)){
    header("Location: ./"); //redirect if no demo id in url
}

$request = "SELECT * FROM Demos JOIN Maps ON Demos.FK_Map = Maps.Name WHERE Demos.id='$demo_id' ;";
$req = $bdd->prepare($request);
$req->execute();
$demo_info = $req->fetchAll()[0];

$timestamp = strtotime($demo_info['Date']);

//get kill ans death infos
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

//merge them
$players = [];
foreach($kills as $kill){
    $players[$kill['name']] = $kill;
}
foreach($deaths as $death){
    $players[$death['name']]['deaths'] = $death['death'];
}

//get first round for teams  JOIN Player_in_Round AS p ON r.id = p.FK_Round
$request = "SELECT r.id
 FROM Rounds AS r
 JOIN Demos AS d ON r.FK_Demo = d.id
 WHERE d.id='$demo_id' 
 ORDER BY r.tick_start
 LIMIT 1;";
$req = $bdd->prepare($request);
$req->execute();
$first_round = $req->fetchAll();

$request = "SELECT p.FK_Player AS name, p.side AS side
 FROM Player_in_Round AS p
 WHERE p.FK_Round='". $first_round[0]['id'] ."';";
$req = $bdd->prepare($request);
$req->execute();
$players_first_round = $req->fetchAll();

$teams = [ 2 => [], 3 => []];
foreach($players_first_round as $player){
    array_push($teams[$player['side']], $player['name']);
}

//calculate totals
$game_kills = 0;
foreach($players as $player){
    $game_kills += $player['kills'];
}

$request = "SELECT COUNT(r.FK_demo) as nbr_rounds
 FROM Rounds AS r 
 JOIN Demos AS d ON r.FK_Demo = d.id
 WHERE d.id='$demo_id' AND r.winner != 0
 GROUP BY r.FK_demo";
$req = $bdd->prepare($request);
$req->execute();
$game_rounds = $req->fetchAll()[0]['nbr_rounds'];
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
            <div class="left-item">
                <h2 class="map"><?php echo $demo_info['Name']; ?></h2>
                <p class="date">Analysé le <?php echo date('d/m/Y', $timestamp); ?></p>
            </div>
            <div class="right-item">
                <span class="kill"><?php echo $game_kills; ?> Victims</span> 
                <span class="rounds"><?php echo $game_rounds; ?> Rounds</span>
            </div>
        </div>
        
        <?php $team_nbr = 1; foreach($teams as $player_in_team){ ?>
            <div class="card scores">
                <ul>
                    <li class="team">
                        <span class="left-item">Equipe <?php echo $team_nbr; ?></span> 
                        <span class="right-item">
                            <span class="kill">Victims</span> 
                            <span class="death">Morts</span>
                            <span class="kd">V/M</span>
                        </span> 
                    </li>
                    <?php foreach($player_in_team as $player_name){ 
                        $player = $players[$player_name]
                        ?>
                        <li>
                            <span class="left-item name"><?php echo $player['name']; ?></span> 
                            <span class="right-item">
                                <span class="kill"><?php echo $player['kills']; ?></span> 
                                <span class="death"><?php echo $player['deaths']; ?></span>
                                <span class="kd">
                                    <?php 
                                    $kd = round($player['kills']/$player['deaths'], 1);
                                    if(intval($kd) == $kd){
                                        $kd = number_format((float)$kd, 1, '.', '');
                                    }
                                    echo $kd; ?>
                                </span>
                            </span>
                        </li>
                    <?php } $team_nbr += 1;?>
                </ul>
            </div>
        <?php } ?>
                
           
        <div class="card rounds"></div>
    </main>

    <?php include './components/footer.php'; ?>
    <?php include './components/scripts.php'; ?>
</body>
</html>