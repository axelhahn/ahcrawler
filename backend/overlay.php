<?php
require_once(dirname(__DIR__) . "/classes/backend.class.php");
$oBackend = new backend();

$sDirPure = "../vendor/pure-release-1.0.0";

?><!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="description" content="">

        <link rel="stylesheet" href="../vendor/font-awesome-4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="<?php echo $sDirPure; ?>/pure-min.css">
        <link rel="stylesheet" href="<?php echo $sDirPure; ?>/grids-responsive-min.css">
        <link rel="stylesheet" href="main.css">

    </head>
    <body>
        <span style="float: left; margin: 0.5em;"><?php echo $oBackend->aAbout['product']; ?></span>
        <?php 
            echo $oBackend->getOverlayContent(); 
        ?>

    </body></html>