<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/flickity.css">
    <link rel="stylesheet" href="styles/style.css">
    <title>Authors List Maker 3000</title>
</head>
<body>
    <?php
        define('ROOT_DIR', realpath(__DIR__));
        require ROOT_DIR.'/control/functions.php';
        $forced=false;
        if(isset($_GET["forced"])){$forced=$_GET["forced"];}
        echo makeHtmlList($_GET["country"],$_GET["cdn"],$_GET["site"],$forced);
    ?>
    <script src="js/flickity.pkgd.min.js"></script>
    <script src="js/index.js"></script>
</body>
</html>