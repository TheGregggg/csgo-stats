<?php 
include '_bdd_info.php';
//Connexion à la base de données
try {
	$bdd = new PDO("mysql:dbname=$dbBase;host=$dbHost", $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	$bdd->exec('SET NAMES utf8');
} catch ( PDOException $e ) {
	echo 'Échec connexion PDO : ' . $e->getMessage() . "<br>\n";
}

// recupérer tout les démos
$request = "SELECT id, FK_Map AS map, Date AS date, Name as name
FROM Demos
ORDER BY Date DESC, id DESC;";
$req = $bdd->prepare($request);
$req->execute();
$demos = $req->fetchAll();

//si il n'y a aucune parties, mets les dates du jour dans les bars de recherches sinon mets la date la plus récente et ancienne
if(count($demos) == 0){
    $last_demo_date = date("Y-m-d");
    $first_demo_date = date("Y-m-d");
}else{
    $last_demo_date = $demos[array_key_last($demos)]['date'];
    $first_demo_date = $demos[0]['date'] ;
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

    <main class="container" id="demos">
        <div class="header">
            <h2 class="left-item">Parties : </h2>
            <div class="search-bar right-item">
                <div>
                    <span>Nom : </span>
                    <input onkeyup="search_bar_and_date()" type="text" name="search" id="search">
                </div>
                <div>
                    <span>A partir : </span>
                    <input onchange="search_bar_and_date()" type="date" id="start" name="date-start" value="<?php echo $last_demo_date; ?>">
                </div>
                <div>
                    <span>Jusqu'a : </span>
                    <input onchange="search_bar_and_date()" type="date" id="end" name="date-end" value="<?php echo $first_demo_date; ?>">
                </div>
            </div>
        </div>
        <div class="demos">
            <ul id="ul">
                <?php foreach($demos as $demo){?>
                    <li class="elem"> 
                        <a href="./partie?id=<?php echo $demo['id']; ?>"> <span class="left-item">
                            <?php 
                                if($demo['name'] != null){
                                    echo $demo['name'];
                                    echo " - ";
                                }
                                echo $demo['map'];
                            ?>
                        </span> </a>
                        <span class="right-item">
                            <span class="date" data-date="<?php echo $demo['date']; ?>"> <?php echo date('d/m/Y', strtotime($demo['date'])); ?> </span> 
                            <a class="del-btn" href="./api/delete_demo?id=<?php echo $demo['id'];?>">
                                <img src="./static/trash.svg">
                            </a>
                        </span> 
                    </li>
                <?php } ?>
            </ul>
        </div>
    </main>
    <script src="./static/search_bar.js"></script>

    <?php include './components/footer.php'; ?>
    <?php include './components/scripts.php'; ?>
</body>
</html>