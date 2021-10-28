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

$request = "SELECT d.id as id, d.Name as Name, d.Date as Date, Maps.Name as map_name FROM Demos as d JOIN Maps ON d.FK_Map = Maps.Name WHERE d.id='$demo_id' ;";
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

//get first round for teams
$request = "SELECT r.id
 FROM Rounds AS r
 JOIN Demos AS d ON r.FK_Demo = d.id
 WHERE d.id='$demo_id' AND r.winner != 0 
 ORDER BY r.tick_start;";
$req = $bdd->prepare($request);
$req->execute();
$rounds = $req->fetchAll();

$request = "SELECT p.FK_Player AS name, p.side AS side
 FROM Player_in_Round AS p
 WHERE p.FK_Round='". $rounds[0]['id'] ."';";
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
            <div class="left-item demo-info">
                <form action="./api/modify_demo_name?id=<?php echo $demo_id;?>" method="POST" >
                    <input type="text" name="demo-name" maxlength="200" id="input-demo-name" 
                    <?php 
                        if($demo_info['Name'] == null){
                            echo 'placeholder="Nom de la démo"';
                        }
                        else{
                            echo 'value="'. $demo_info['Name'] .'"';
                        }?>
                    >
                    <input type="submit" id="input-submit"value="Modifié">
                </form>
                <p>
                    <span class="map">Carte : <?php echo $demo_info['map_name']; ?></span>
                    <span class="date">Analysé le <?php echo date('d/m/Y', $timestamp); ?></span>
                </p>
            </div>
            <div class="right-item">
                <span class="kill"><?php echo $game_kills; ?> Victims</span> 
                <span class="rounds"><?php echo $game_rounds; ?> Rounds</span>
                <a class="del-btn" href="./api/delete_demo?id=<?php echo $demo_id;?>">
                    <img src="./static/trash.svg">
                </a>
            </div>
        </div>
        
        <div class="row">
        <?php $team_nbr = 1; foreach($teams as $player_in_team){ ?>
            <div class="col-6">
                <div class="card scores">
                    <ul>
                        <li class="team">
                            <span class="left-item">Equipe <?php echo $team_nbr; ?></span> 
                            <span class="right-item">
                                <span class="kill">Victi.</span> 
                                <span class="death">Morts</span>
                                <span class="kd">V/M</span>
                            </span> 
                        </li>
                        <?php foreach($player_in_team as $player_name){ 
                            $player = $players[$player_name]
                            ?>
                            <a href="./joueur?name=<?php echo $player['name']; ?>">
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
                            </a>
                        <?php } $team_nbr += 1;?>
                    </ul>
                </div>
            </div>
        <?php } ?>
        </div>

        <div class="row">
            <div class="col-12">
                <h2>Rounds : </h2>
            </div>
        </div>
        <?php $round_nbr=1; foreach($rounds as $round){
            //get first round for teams
            $request = "SELECT k.FK_killer AS killer, k_pr.side AS side, k.FK_victim AS victim, w.Name AS weapon
            FROM Kills AS k
            JOIN Weapons AS w ON k.FK_killed_with_weapon = w.id
            JOIN Player_in_Round AS k_pr ON k.FK_killer=k_pr.FK_player
            WHERE k.FK_Round='". $round['id'] ."' 
            AND k_pr.FK_round='". $round['id'] ."' 
            ORDER BY k.tick;";
            $req = $bdd->prepare($request);
            $req->execute();
            $kills_this_round = $req->fetchAll();
        ?>
            <div class="row card round">
                <div class="col-6 round-info">
                    <h4 class="col-12 col-6-sm">Rounds</h4>
                    <h4 class="col-12 col-6-sm"><?php echo $round_nbr; ?> </h4>
                </div>
                <div class="col-6">                
                    <ul>
                        <?php foreach($kills_this_round as $kill){ 
                            ?>
                            <li class="side-<?php echo $kill['side']; ?>"> 
                                <a href="./joueur?name=<?php echo $kill['killer']; ?>">
                                <?php echo $kill['killer']; ?>
                                </a>
                                <span>a tué</span>
                                <a href="./joueur?name=<?php echo $kill['victim']; ?>">
                                <?php echo $kill['victim']; ?>
                                </a>
                                <span>avec</span> 
                                <a href="./amres#<?php echo $kill['weapon']; ?>">
                                <?php echo $kill['weapon']; ?>
                                </a>
                            </li>
                            
                        <?php }?>
                    </ul>
                </div>
            </div>
        <?php $round_nbr+=1;} ?>
                
        <!--<div class="card rounds"></div>-->
    </main>

    <?php include './components/footer.php'; ?>
    <?php include './components/scripts.php'; ?>
</body>
</html>