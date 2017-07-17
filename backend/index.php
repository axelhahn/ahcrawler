<?php
require_once(dirname(__DIR__) . "/classes/backend.class.php");
require_once(dirname(__DIR__) . "/classes/cdnorlocal.class.php");

$oBackend = new backend();
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

        <?php echo $oCdn->getCss('pure','pure-min.css'); ?>
        <?php echo $oCdn->getCss('pure','buttons-min.css'); ?>
        <?php echo $oCdn->getCss('pure','grids-responsive-min.css'); ?>
        <?php echo $oCdn->getCss('font-awesome','css/font-awesome.min.css'); ?>
        <?php echo $oCdn->getCss('datatables','css/jquery.dataTables.min.css'); ?>

        <?php echo $oCdn->getJs('jquery','jquery.min.js'); ?>
        <?php echo $oCdn->getJs('datatables','js/jquery.dataTables.min.js'); ?>

        <link rel="stylesheet" href="main.css">
        <!--
        <link rel="stylesheet" href="skins/sky/theme.css">
        -->
        <script>
            function showModal(sUrl){
                var divOverlay=document.getElementById('overlay');
                var sHtml='';
                
                sHtml+='<iframe src="'+sUrl+'" style="width: 100%; border: 0; height: 650px;"></iframe>';
                divOverlay.style.display='block';
                var divContent=document.getElementById('dialogcontent');
                divContent.innerHTML=sHtml;
            }
            function hideModal(){
                var divOverlay=document.getElementById('overlay');
                divOverlay.style.display='none';
                
            }
        </script>

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
                    </a></h1>
                <div class="pure-menu">
                    
                    <?php echo $oBackend->getNavi(); ?>
                    
                </div>

            </div>
        </div>


    </body></html>