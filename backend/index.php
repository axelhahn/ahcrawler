<?php
require_once(dirname(__DIR__) . "/classes/backend.class.php");
require_once(dirname(__DIR__) . "/classes/cdnorlocal.class.php");

$oBackend = new backend();
$oRenderer = new ressourcesrenderer();
$oCdn=new axelhahn\cdnorlocal(array(
    'vendorrelpath'=>'../vendor/cache',
    // 'vendordir'=>__DIR__.'/../vendor/cache',
    // 'vendorurl'=>'../vendor/cache',
    'debug'=>0,
    ));

?><!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="robots" content="noindex,nofollow">
        <meta name="description" content="">

        <?php echo $oCdn->getHtmlInclude('pure/1.0.0/pure-min.css'); ?>
        <?php echo $oCdn->getHtmlInclude('pure/1.0.0/buttons-min.css'); ?>
        <?php echo $oCdn->getHtmlInclude('pure/1.0.0/grids-responsive-min.css'); ?>
        <?php echo $oCdn->getHtmlInclude('font-awesome/4.7.0/css/font-awesome.min.css'); ?>
        <?php echo $oCdn->getHtmlInclude('datatables/1.10.15/css/jquery.dataTables.min.css'); ?>

        <?php echo $oCdn->getHtmlInclude('jquery/3.2.1/jquery.min.js'); ?>
        <?php echo $oCdn->getHtmlInclude('datatables/1.10.15/js/jquery.dataTables.min.js'); ?>

        <?php echo $oCdn->getHtmlInclude('Chart.js/2.7.2/Chart.min.js'); ?>
        
        <script src="javascript/functions.js"></script>      
        <link rel="stylesheet" href="main.css">
        <!--
        <link rel="stylesheet" href="skins/sky/theme.css">
        -->

    </head>
    <body>


        <div id="overlay" onclick="hideModal(); return false;">
            <div class="divdialog" onclick="return false;">
                <button class="button-error pure-button"
                   onclick="hideModal();return false;"
                   style="right: -1em; top: -1em; position: absolute;"
                   > X </button>
                <div id="dialogcontent"></div>
            </div>
        </div>
        
        <div id="content">
            <div class="maincontent">
                <?php 
                    echo $oBackend->getHead().$oBackend->getContent(); 
                ?>
            </div>
        </div>

        <div class="sidebar pure-u-1 ">
            <div class="header">
                <h1 class="brand-title"><a href="?">
                        <?php echo $oBackend->aAbout['product']; ?>
                        <span><?php echo $oBackend->aAbout['version']; ?></span>
                    </a></h1>
                <?php
                    echo $oBackend->oUpdate->hasUpdate()
                        // ?  '<div class="warning pure-menu"><a href="?page=update">' . sprintf($oBackend->lB('update.available-yes') , $oBackend->oUpdate->getLatestVersion()) .'</a></div><br>'
                        ?  '<div class="warning pure-menu">' . $oRenderer->renderShortInfo('warn') . sprintf($oBackend->lB('update.available-yes') , $oBackend->oUpdate->getLatestVersion()) .'</div><br>'
                        :  ''
                    ;
                ?>
                    
                        
                <div class="pure-menu">
                    
                    <?php echo $oBackend->getNavi(); ?>
                    
                </div>

            </div>
        </div>
        <style>
            span.warning1{background: #ff0000;}
        </style>
        <script>
            initPage();
        </script>
        <div style="clear: both;"></div>
        <?php echo $oBackend->logRender(); ?>
    </body></html>