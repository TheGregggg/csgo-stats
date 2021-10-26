<?php 
include '_bdd_info.php';
//Connexion à la base de données
try {
	$bdd = new PDO("mysql:dbname=$dbBase;host=$dbHost", $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	$bdd->exec('SET NAMES utf8');
} catch ( PDOException $e ) {
	echo 'Échec connexion PDO : ' . $e->getMessage() . "<br>\n";
}

$request = "SELECT p.Name AS name, count(pd.FK_Demo) AS games
FROM Players AS p 
JOIN Player_in_Demo as pd On p.Name = pd.FK_player
GROUP BY p.Name
ORDER BY Name;";
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
        <div class="search-bar">
            <span>Rechercher : </span>
            <input onkeyup="search_bar()" type="text" name="search" id="search">
            <!--<img src="./static/search.svg">-->
        </div>
        <div class="players">
            <ul id="ul">
                <li class="header">
                    <span class="left-item">Joueur </span> 
                    <span class="right-item">
                        <span class="games">Parties</span> 
                    </span> 
                </li>
                <?php foreach($players as $player){?>
                <li class="elem"> 
                    <span class="left-item"> <?php echo $player['name']; ?> </span> 
                    <span class="right-item">
                        <span class="games"> <?php echo $player['games']; ?> </span> 
                    </span> 
                </li>
                <?php } ?>
            </ul>
        </div>
    </main>
    <script>
    function search_bar() {
    // Declare variables
    var input, filter, ul, li, a, i, txtValue;
    input = document.getElementById('search');
    filter = input.value.toUpperCase();
    ul = document.getElementById("ul");
    li = ul.getElementsByClassName('elem');
    
    // Loop through all list items, and hide those who don't match the search query
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("span")[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
        li[i].style.display = "";
        } else {
        li[i].style.display = "none";
        }
    }
    }
    </script>

    <?php include './components/footer.php'; ?>
    <?php include './components/scripts.php'; ?>
</body>
</html>