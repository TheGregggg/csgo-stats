<?php 
include '_bdd_info.php';
//Connexion à la base de données
try {
	$bdd = new PDO("mysql:dbname=$dbBase;host=$dbHost", $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	$bdd->exec('SET NAMES utf8');
} catch ( PDOException $e ) {
    //db note created
    header("Location: ./install");
	echo 'Échec connexion PDO : ' . $e->getMessage() . "<br>\n";
}

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

    <main class="container" id="home">
        <form enctype="multipart/form-data" action="./parse_demo" method="post" >
            <label for="file" class="label-file">Choisir une démo (.dem)</label>
            <input id="file" class="input-file" type="file" name="demo" accept=".dem" required>
            <input class="hidden" id="submit" type="submit" onclick="activate_loading()" value="Validé">

            <script>
                const file = document.querySelector('#file');
                const submit_btn = document.querySelector('#submit');
                file.addEventListener('change', (e) => {
                    const [file] = e.target.files; // Get the selected file
                    const { name: fileName } = file; // Get the file name and size
                    document.querySelector('.label-file').textContent = fileName;
                    submit_btn.classList.remove("hidden")
                });

                function activate_loading(){
                    document.querySelector('.loading').classList.remove("hidden");
                    document.querySelector('header').classList.add("hidden");
                    document.querySelector('footer').classList.add("hidden");
                    document.querySelector('main').style.display = "none";
                }
            </script>
        </form>
    </main>

    <?php include './components/footer.php'; ?>
    <?php include './components/scripts.php'; ?>
</body>
</html>