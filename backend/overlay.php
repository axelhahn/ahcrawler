<?php
require_once(dirname(__DIR__) . "/classes/backend.class.php");
require_once(dirname(__DIR__) . "/classes/cdnorlocal.class.php");

$oBackend = new backend();
$oCdn=new axelhahn\cdnorlocal(array(
    'vendorrelpath'=>'../vendor/cache',
    'debug'=>0,
    ));

require_once(dirname(__DIR__) . "/classes/backend.class.php");
$oBackend = new backend();

?><!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="description" content="">

        <?php echo $oCdn->getHtmlInclude('font-awesome/4.7.0/css/font-awesome.min.css'); ?>
        <?php echo $oCdn->getHtmlInclude('pure/1.0.0/pure-min.css'); ?>
        <?php echo $oCdn->getHtmlInclude('pure/1.0.0/buttons-min.css'); ?>
        <?php echo $oCdn->getHtmlInclude('pure/1.0.0/grids-responsive-min.css'); ?>
        <link rel="stylesheet" href="main.css">

    </head>
    <body>
        <span style="float: left; margin: 0.5em;"><?php echo $oBackend->aAbout['product']; ?></span>
        <?php 
            echo $oBackend->getOverlayContent(); 
        ?>

    </body></html>