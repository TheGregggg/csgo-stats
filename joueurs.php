<?php 
include '_bdd_info.php';
//Connexion à la base de données
try {
	$bdd = new PDO("mysql:dbname=$dbBase;host=$dbHost", $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	$bdd->exec('SET NAMES utf8');
} catch ( PDOException $e ) {
	echo 'Échec connexion PDO : ' . $e->getMessage() . "<br>\n";
}

//récupère tout les joueurs de la bdd
$request = "SELECT p.Name AS name, count(pd.FK_Demo) AS games
FROM Players AS p 
JOIN Player_in_Demo as pd On p.Name = pd.FK_player
GROUP BY p.Name
ORDER BY games DESC, Name;";
$req = $bdd->prepare($request);
$req->execute();
$players = $req->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include './components/header_tags.php'; ?>
    <title>CSGO Stats</title>
</head>
<body>
    <?php include './components/header.php'; ?>

    <main class="container" id="players">
        <div class="header">
            <h2 class="left-item">Joueurs : </h2>
            <div class="search-bar right-item">
                <span>Nom : </span>
                <input onkeyup="search_bar()" type="text" name="search" id="search">
            </div>
        </div>
        <div class="players">
            <ul id="ul">
                <?php foreach($players as $player){?>
                    <a href="./joueur?name=<?php echo $player['name']; ?>">
                        <li class="elem"> 
                            <span class="left-item"> <?php echo $player['name']; ?> </span> 
                            <span class="right-item">
                                <span class="games"> <?php echo $player['games']; ?> Parties</span> 
                            </span> 
                        </li>
                    </a>
                <?php } ?>
            </ul>
        </div>
    </main>
    <script src="./static/search_bar.js"></script>

    <?php include './components/footer.php'; ?>
    <?php include './components/scripts.php'; ?>
</body>
</html>