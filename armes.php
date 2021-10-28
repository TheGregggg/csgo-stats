<?php 
include '_bdd_info.php';
//Connexion à la base de données
try {
	$bdd = new PDO("mysql:dbname=$dbBase;host=$dbHost", $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	$bdd->exec('SET NAMES utf8');
} catch ( PDOException $e ) {
	echo 'Échec connexion PDO : ' . $e->getMessage() . "<br>\n";
}

$request = "SELECT * FROM Weapons;";
$req = $bdd->prepare($request);
$req->execute();
$weapons = $req->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include './components/header_tags.php'; ?>
    <title>CSGO Stats</title>
</head>
<body>
    <div class="hidden loading" >
        <img src="./static/sync.svg">
    </div>
    <?php include './components/header.php'; ?>

    <main class="container" id="weapons">
        <div class="row" id="header">
            <div class="col-2 col-4-sm">Arme</div>
            <div class="col-5 col-8-sm">Descriptif</div>
            <div class="col-1 col-4-sm">Chargeur</div>
            <div class="col-1 col-4-sm">Dégats</div>
            <div class="col-3 col-4-sm">RPM</div>
        </div>
        <?php foreach($weapons as $weapon){ 
            if($weapon['Description'] != ""){?>
        <div class="row" id="<?php echo $weapon['Name']; ?>">
            <div class="col-2 col-4-sm"><?php echo $weapon['Name']; ?></div>
            <div class="col-5 col-8-sm"><?php echo $weapon['Description']; ?></div>
            <div class="col-1 col-4-sm">
                <?php if($weapon['Magazine_size'] > 0){
                    echo $weapon['Magazine_size'];
                } ?>
            </div>
            <div class="col-1 col-4-sm">
                <?php if($weapon['Damage_per_bullet'] > 0){
                    echo $weapon['Damage_per_bullet'];
                } ?>
            </div>
            <div class="col-3 col-4-sm">
                <?php if($weapon['Bullet_per_seconde'] > 0){
                    echo round($weapon['Bullet_per_seconde']*60);
                } ?>
            </div>
        </div>
        <?php }} ?>
    </main>

    <?php include './components/footer.php'; ?>
    <?php include './components/scripts.php'; ?>
</body>
</html>