<?php
ini_set('post_max_size', '1000M');
ini_set('upload_max_filesize', '1000M');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include './components/header_tags.php'; ?>
    <title>CSGO Stats</title>
</head>
<body>
    <?php include './components/header.php'; ?>

    <main class="container" id="home">
        <form enctype="multipart/form-data" action="./parse_demo.php" method="post">
            <label for="file" class="label-file">Choisir une démo (.dem)</label>
            <input id="file" class="input-file" type="file" name="demo" accept=".dem" required>
            <input class="hidden" id="submit" type="submit" value="Validé">

            <script>
                const file = document.querySelector('#file');
                const submit_btn = document.querySelector('#submit');
                file.addEventListener('change', (e) => {
                    const [file] = e.target.files; // Get the selected file
                    const { name: fileName } = file; // Get the file name and size
                    document.querySelector('.label-file').textContent = fileName;
                    submit_btn.classList.remove("hidden")
                });
            </script>
        </form>
    </main>

    <?php include './components/footer.php'; ?>
    <?php include './components/scripts.php'; ?>
</body>
</html>