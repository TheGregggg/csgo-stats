<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include './components/header_tags.php'; ?>
    <title>Document</title>
</head>
<body>
    <?php include './components/header.php'; ?>

    <main class="container" id="home">
        <form action="/parse_demo.php">
            <label for="file" class="label-file">Choisir une démo (.dem)</label>
            <input id="file" class="input-file" type="file" accept=".dem">
            <input type="submit" value="Validé">
        </form>
    </main>

    <?php include './components/footer.php'; ?>
</body>
</html>