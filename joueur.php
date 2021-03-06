<?php 
include '_bdd_info.php';
//Connexion à la base de données
try {
	$bdd = new PDO("mysql:dbname=$dbBase;host=$dbHost", $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	$bdd->exec('SET NAMES utf8');
} catch ( PDOException $e ) {
	echo 'Échec connexion PDO : ' . $e->getMessage() . "<br>\n";
}

$player_name = $_GET['name'];
if(! isset($player_name)){
    header("Location: ./"); //Redirige si pas de nom dans l'url
}

//récupère le total de victimes du joueur
$request = "SELECT k.FK_killer as name, COUNT(k.FK_victim) as kills
 FROM Kills AS k 
 WHERE k.FK_killer='$player_name'
 GROUP BY k.FK_killer;";
$req = $bdd->prepare($request);
$req->execute();
$total_kills = $req->fetchAll()[0]['kills'];

//récupère le total de morts du joueur
$request = "SELECT k.FK_victim as name, COUNT(k.FK_killer) as deaths
 FROM Kills AS k 
 WHERE k.FK_victim='$player_name'
 GROUP BY k.FK_victim;";
$req = $bdd->prepare($request);
$req->execute();
$total_deaths = $req->fetchAll()[0]['deaths'];

//calcul le ratio V/M et rajoute un .0 si le nombre est entier
$total_kd = round($total_kills/$total_deaths, 2);
if(intval($total_kd) == $total_kd){
    $total_kd = number_format((float)$total_kd, 1, '.', '');
}

//récupère le nombre de parties joué par le joueur
$request = "SELECT pd.FK_player AS name, COUNT(pd.FK_Demo) AS games
 FROM Player_in_Demo AS pd 
 WHERE pd.FK_player='$player_name'
 GROUP BY pd.FK_player;";
$req = $bdd->prepare($request);
$req->execute();
$total_games = $req->fetchAll()[0]['games'];

//récupère le nombre de victimes par armes du joueur
$request =  "SELECT w.name AS name, COUNT(w.id) AS totalkill
FROM Kills AS k 
JOIN Weapons AS w ON k.FK_killed_with_weapon = w.id
WHERE k.FK_killer='$player_name'
GROUP BY w.name
ORDER BY totalkill DESC;";
$req = $bdd->prepare($request);
$req->execute();
$weapon_stats = $req->fetchAll();
$favorite_weapon = $weapon_stats[0]['name']; //prend l'arme avec le plus de kill

//récupère le nombre de morts par armes du joueur
$request =  "SELECT w.name AS name, COUNT(w.id) AS totalkill
FROM Kills AS k 
JOIN Weapons AS w ON k.FK_killed_with_weapon = w.id
WHERE k.FK_victim='$player_name'
GROUP BY w.name
ORDER BY totalkill DESC;";
$req = $bdd->prepare($request);
$req->execute();
$weapon_stats_deaths = $req->fetchAll();

//récupère la carte la plus jouée
$request =  "SELECT m.Name AS name, COUNT(pd.FK_Demo) AS games
FROM Player_in_Demo AS pd 
JOIN Demos AS d ON pd.FK_demo = d.id 
JOIN Maps AS m ON d.FK_map = m.Name
WHERE pd.FK_player='$player_name'
GROUP BY m.Name
ORDER BY games DESC;";
$req = $bdd->prepare($request);
$req->execute();
$favorite_map = $req->fetchAll()[0]['name'];

//récupère le nombre de parties gagné et le nombre de parties perdu
$request =  "SELECT COUNT(id) as games, IF(nb_rounds-nb_win > nb_win, 0, 1) as won
FROM (
    SELECT d.id as id, COUNT(IF(pr.side = r.winner, 1, NULL)) AS nb_win, COUNT(IF(r.winner != 0, 1, NULL)) AS nb_rounds
    FROM Player_in_demo AS pd
    JOIN Demos AS d ON pd.FK_demo = d.id
    JOIN Rounds AS r ON d.id = r.FK_Demo
    JOIN Player_in_round AS pr ON r.id = pr.FK_round
    WHERE pd.FK_player='$player_name' AND pr.FK_player='$player_name'
    GROUP BY d.id
) AS demo
GROUP BY won 
ORDER BY won;";
$req = $bdd->prepare($request);
$req->execute();
$games_result = $req->fetchAll();

// calcul du pourcentage de victoire total du joueur
// si il a que des défaite ou des victoire, savoir si cest un nombre de défaite ou de victoire
if(count($games_result) == 1){
    if($games_result[0]['won'] == 0){ //nombre de défaite
        $games_lost = $games_result[0]['games'];
        $games_won = 0;
    }else{//nombre de victoire
        $games_won = $games_result[0]['games'];
        $games_lost = 0;
    }
}
else{ // sinon assigné les valeurs 
    $games_lost = $games_result[0]['games'];
    $games_won = $games_result[1]['games'];
}
$totals_games = $games_won+$games_lost;
$percentage_game_won = round(($games_won*100)/$totals_games);

// récupère le pourcentage de victoire par cartes pour ce joueur
$request =  "SELECT map, (SUM(IF(nb_rounds-nb_win > nb_win, 0, 1))*100)/COUNT(nb_win)  as win_rate
FROM (
    SELECT d.FK_Map as map, COUNT(IF(pr.side = r.winner, 1, NULL)) AS nb_win, COUNT(IF(r.winner != 0, 1, NULL)) AS nb_rounds
    FROM Player_in_demo AS pd
    JOIN Demos AS d ON pd.FK_demo = d.id
    JOIN Rounds AS r ON d.id = r.FK_Demo
    JOIN Player_in_round AS pr ON r.id = pr.FK_round
    WHERE pd.FK_player='$player_name' AND pr.FK_player='$player_name'
    GROUP BY d.id
) AS demo
GROUP BY map
ORDER BY map;";
$req = $bdd->prepare($request);
$req->execute();
$maps_winrate = $req->fetchAll();

//récupère les stats pour chaque cartes
$request =  "SELECT m.name AS name, COUNT(id) AS nb_games, m.Image AS img, m.Img_ref_x, m.Img_ref_y, m.Map_ref_x, m.Map_ref_y, m.Img_origin_x, m.Img_origin_y
FROM Player_in_demo AS pd
JOIN Demos AS d ON pd.FK_demo = d.id
JOIN Maps AS m ON d.FK_map = m.name
WHERE pd.FK_player='$player_name'
GROUP BY m.name
ORDER BY m.name;";
$req = $bdd->prepare($request);
$req->execute();
$maps_stats = $req->fetchAll();

//récupère le pourcentage de victoire pour chaque cartes entre les deux side T et CT
$request =  "SELECT map, 
(nb_win_t*100)/nb_rounds_t as win_rate_t,
(nb_win_ct*100)/nb_rounds_ct as win_rate_ct
FROM (
    SELECT d.FK_Map as map, 
    COUNT(IF(pr.side = r.winner AND pr.side = 2, 1, NULL )) AS nb_win_t, 
    COUNT(IF(r.winner != 0 AND pr.side = 2, 1, NULL )) AS nb_rounds_t, 
    COUNT(IF(pr.side = r.winner AND pr.side = 3, 1, NULL )) AS nb_win_ct, 
    COUNT(IF(r.winner != 0 AND pr.side = 3, 1, NULL )) AS nb_rounds_ct
    FROM Player_in_demo AS pd
    JOIN Demos AS d ON pd.FK_demo = d.id
    JOIN Rounds AS r ON d.id = r.FK_Demo
    JOIN Player_in_round AS pr ON r.id = pr.FK_round
    WHERE pd.FK_player='$player_name' AND pr.FK_player='$player_name'
    GROUP BY d.FK_map
) AS map;
GROUP BY map
ORDER BY map;";
$req = $bdd->prepare($request);
$req->execute();
$maps_side_winrate = $req->fetchAll();
//ajoute les pourcentages de victoire dans le tableau des stats des maps, pour facilement y avoir accès
for ($i=0; $i < count($maps_side_winrate); $i++) { 
    $maps_stats[$i]['win_rate_ct'] = $maps_side_winrate[$i]['win_rate_ct'];
    $maps_stats[$i]['win_rate_t'] = $maps_side_winrate[$i]['win_rate_t'];
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
        <div class="row">
            <div class="col-12">
                <h1><?php echo $player_name;?></h1>
            </div>
        </div>
        <div class="row card" style="padding:0;">
            <div class="col-7 stats">
                <div class="row">
                    <div class="col-12"><h3>Statistiques général</h3></div>
                </div>
                <div class="row">
                    <div class="col-3 player-card"><span class="title">Victimes</span>  <span class="data"><?php echo $total_kills;?></span></div>
                    <div class="col-3 player-card"><span class="title">Morts</span>     <span class="data"><?php echo $total_deaths;?></span></div>
                    <div class="col-3 player-card"><span class="title">V/M</span>       <span class="data"><?php echo $total_kd;?></span></div>
                    <div class="col-3 player-card"><span class="title">+/-</span>       <span class="data"><?php echo $total_kills-$total_deaths;?></span></div>
                    <div class="col-3 player-card"><span class="title">Parties</span>   <span class="data"><?php echo $total_games;?></span></div>
                    <div class="col-3 player-card"><span class="title">Victoire</span>  <span class="data"><?php echo $percentage_game_won;?>%</span> </div>
                    <div class="col-3 player-card"><span class="title">Arme fav.</span> <span class="data"><?php echo $favorite_weapon;?></span></div>
                    <div class="col-3 player-card"><span class="title">Carte fav.</span><span class="data"><?php echo $favorite_map;?></span></div>
                </div>
            </div>
            <div class="col-5 history">
                <div class="row">
                    <div class="col-12"><h3>Pourcentage victoire par cartes</h3></div>
                </div>
                <?php foreach($maps_winrate as $map){ ?>
                    <div class="col-4 player-card">
                        <span class="title"><?php echo $map['map'];?></span>
                        <span class="data"><?php echo round($map['win_rate'])?>%</span>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="row card">
            <div class="row">
                <div class="col-6"><h3>Stats par cartes</h3></div>
            </div>
            <?php foreach($maps_stats as $map){ 
                $img_size = getimagesize("./static/maps/light/".$map['img']);
                $img_height = $img_size[1];
                $img_width = $img_size[0];

                //calcul combien un pixel de l'image faut dans le monde de CSGO avec les données des deux points
                $pixel_per_csgo_unite_x = ($map['Img_ref_x'] - $map['Img_origin_x'])/($map['Map_ref_x']);
                $pixel_per_csgo_unite_y = ($map['Img_ref_y'] - $map['Img_origin_y'])/($map['Map_ref_y']);

                //récupère la listes des positions des victimes du joueur sur cette carte
                $request =  "SELECT k.k_pos_x AS pos_x, k.k_pos_y AS pos_y
                FROM Kills AS k 
                JOIN Rounds AS r ON k.FK_round = r.id
                JOIN Demos AS d ON r.FK_demo = d.id
                WHERE k.FK_killer='$player_name' AND d.FK_Map = '". $map['name'] ."';";
                $req = $bdd->prepare($request);
                $req->execute();
                $kills_list = $req->fetchAll();
                
                $points_kills = [];
                foreach($kills_list as $kill){
                    /*
                    multiplie la position par l'echelle obtenue precedement 
                    puis ajoute la position de l'origin (de la carte dans CSGO) sur l'image de la carte
                    convertie ensuite en pourcentage
                    */
                    $pos_x = ($kill['pos_x'] * $pixel_per_csgo_unite_x) + $map['Img_origin_x'];
                    $pos_x_percent = abs(($pos_x*100)/$img_width);
                    
                    $pos_y = ($kill['pos_y'] * $pixel_per_csgo_unite_y) + $map['Img_origin_y'];
                    $pos_y_percent = abs(($pos_y*100)/$img_height);

                    //ajoute seulement si coords ne dépassent pas la carte, possibilité à cause des bugs de démos
                    if(! ($pos_x_percent > 100 or $pos_x_percent < 0 or $pos_y_percent > 100 or $pos_y_percent < 0)){
                        array_push($points_kills, ['x' => $pos_x_percent, 'y' => $pos_y_percent]); // ajoute à la listes des points le couple de coordonées
                    }
                }

                //récupère la listes des positions des morts du joueur sur cette carte
                $request =  "SELECT k.v_pos_x AS pos_x, k.v_pos_y AS pos_y
                FROM Kills AS k 
                JOIN Rounds AS r ON k.FK_round = r.id
                JOIN Demos AS d ON r.FK_demo = d.id
                WHERE k.FK_victim='$player_name' AND d.FK_Map = '". $map['name'] ."';";
                $req = $bdd->prepare($request);
                $req->execute();
                $deaths_list = $req->fetchAll();
                
                $points_deaths = [];
                foreach($deaths_list as $death){
                    //meme logique
                    $pos_x = ($death['pos_x'] * $pixel_per_csgo_unite_x) + $map['Img_origin_x'];
                    $pos_x_percent = abs(($pos_x*100)/$img_width);
                    
                    $pos_y = ($death['pos_y'] * $pixel_per_csgo_unite_y) + $map['Img_origin_y'];
                    $pos_y_percent = abs(($pos_y*100)/$img_height);

                    if(! ($pos_x_percent > 100 or $pos_x_percent < 0 or $pos_y_percent > 100 or $pos_y_percent < 0)){
                        array_push($points_deaths, ['x' => $pos_x_percent, 'y' => $pos_y_percent]); // ajoute à la listes des points le couple de coordonées
                    }
                }
                
                ?>
                <div class="row map-stats">
                    <div class="col-3 col-6-sm">
                        <div class="map-circle">
                            <div class="map-border">
                                <img  class="map-img" src="./static/maps/light/<?php echo $map['img']; ?>">
                            </div>
                            <div class="txt">
                                <span class="map-name"><?php echo $map['name'];?></span>
                                <span class="map-games">Joué <?php echo $map['nb_games'];?>x</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-3 col-6-sm">
                        <div class="side_stats">
                            <span class="ct-winrate side-winrate">CT : </span><span class="winrate"><?php echo round($map['win_rate_ct']);?>%</span>
                            <div class="graphs">
                                <div class="ct">
                                    <div class="graph" style="width: <?php echo round($map['win_rate_ct']);?>%;"></div>
                                </div>
                                <div class="line"></div>
                                <div class="t">
                                    <div class="graph" style="width: <?php echo round($map['win_rate_t']);?>%;"></div>
                                </div>
                            </div>
                            <span class="t-winrate side-winrate">T : </span><span class="winrate"><?php echo round($map['win_rate_t']);?>%</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <span>Cartes des victimes</span>
                        <div class="heatmap">
                            <img src="./static/maps/light/<?php echo $map['img']; ?>">
                            <div class="points">
                                <?php foreach($points_kills as $point) {?>
                                    <div class="point" style="top: <?php echo $point['y'];?>%; left: <?php echo $point['x'];?>%;"></div>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <span>Cartes des morts</span>
                        <div class="heatmap">
                            <img src="./static/maps/light/<?php echo $map['img']; ?>">
                            <div class="points">
                                <?php foreach($points_deaths as $point) {?>
                                    <div class="point" style="top: <?php echo $point['y'];?>%; left: <?php echo $point['x'];?>%;"></div>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="row">
            <div class="col-6 s-card">
                <div class="row">
                    <div class="col-12"><h3>Victimes avec armes</h3></div>
                </div>
                <?php foreach($weapon_stats as $weapon){ ?>
                    <div class="row weapon-stats">
                        <div class="col-4 col-4-sm">
                            <a class="weapon-name" href="./armes#<?php echo $weapon['name']; ?>"><?php echo $weapon['name'];?></a>
                        </div>
                        <div class="col-2 col-2-sm">
                            <span class="weapon-stat"><?php echo $weapon['totalkill'];?> </span>
                        </div>
                        <div class="col-2 col-2-sm">
                            <span class="weapon-stat"><?php echo round(($weapon['totalkill']*100)/$total_kills);?> %</span>
                        </div>
                        <div class="col-4 col-4-sm">
                            <div class="weapon-death-graph">
                                <div class="graph" style="width: <?php echo round(($weapon['totalkill']*100)/$total_kills);?>%;"></div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="col-6 s-card">
                <div class="row">
                    <div class="col-12"><h3>Morts contre armes</h3></div>
                </div>
                <?php foreach($weapon_stats_deaths as $weapon){ ?>
                    <div class="row weapon-stats">
                        <div class="col-4 col-4-sm">
                            <a class="weapon-name" href="./armes#<?php echo $weapon['name']; ?>"><?php echo $weapon['name'];?></a>
                        </div>
                        <div class="col-2 col-2-sm">
                            <span class="weapon-stat"><?php echo $weapon['totalkill'];?> </span>
                        </div>
                        <div class="col-2 col-2-sm">
                            <span class="weapon-stat"><?php echo round(($weapon['totalkill']*100)/$total_deaths);?> %</span>
                        </div>
                        <div class="col-4 col-4-sm">
                            <div class="weapon-death-graph">
                                <div class="graph" style="width: <?php echo round(($weapon['totalkill']*100)/$total_deaths);?>%;"></div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </main>

    <?php include './components/footer.php'; ?>
    <?php include './components/scripts.php'; ?>
</body>
</html>