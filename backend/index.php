<?php
require_once(dirname(__DIR__) . "/classes/backend.class.php");
require_once(dirname(__DIR__) . "/classes/cdnorlocal.class.php");

$oBackend = new backend();
$oRenderer = new ressourcesrenderer();

global $oCdn;
$oCdn=new axelhahn\cdnorlocal(array(
    'vendorrelpath'=>'../vendor/cache',
    // 'vendordir'=>__DIR__.'/../vendor/cache',
    // 'vendorurl'=>'../vendor/cache',
    'debug'=>0,
    ));
$oCdn->setLibs(array(
    "pure/1.0.0",
    "datatables/1.10.19",
    "font-awesome/5.8.1",
    "jquery/3.4.0",
    "Chart.js/2.8.0"
));


/**
 * get new querystring - create the new querystring by existing query string
 * of current request and given new parameters
 * @param array $aQueryParams
 * @return string
 */
function getNewQs($aQueryParams = array()) {
    $s = false;
    $aDelParams = array("doinstall");

    if ($_GET) {
        $aDefaults = $_GET;
        foreach ($aDelParams as $sParam) {
            if (array_key_exists($sParam, $aDefaults)) {
                unset($aDefaults[$sParam]);
            }
        }
        $aQueryParams = array_merge($aDefaults, $aQueryParams);
    }

    foreach ($aQueryParams as $var => $value) {
        if ($value)
            $s .= "&amp;" . $var . "=" . urlencode($value);
    }
    $s = "?" . $s;
    return $s;
}



?><!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="robots" content="noindex,nofollow">
        <meta name="description" content="">

        <?php
        echo ''
        
            .$oCdn->getHtmlInclude($oCdn->getLibRelpath('pure')."/pure-min.css") . "\n"
            .$oCdn->getHtmlInclude($oCdn->getLibRelpath('pure')."/buttons-min.css") . "\n"
            .$oCdn->getHtmlInclude($oCdn->getLibRelpath('pure')."/grids-responsive-min.css") . "\n"

            // fontawesome
            .$oCdn->getHtmlInclude($oCdn->getLibRelpath('font-awesome')."/css/all.css") . "\n"
        
            // jQuery
            .$oCdn->getHtmlInclude($oCdn->getLibRelpath('jquery')."/jquery.min.js") . "\n"
            // datatables
            .$oCdn->getHtmlInclude($oCdn->getLibRelpath('datatables')."/css/jquery.dataTables.min.css") . "\n"
            .$oCdn->getHtmlInclude($oCdn->getLibRelpath('datatables')."/js/jquery.dataTables.min.js") . "\n"

            // Chart.js
            .$oCdn->getHtmlInclude($oCdn->getLibRelpath('Chart.js')."/Chart.min.js") . "\n"

            ;
        ?>
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
                        ?  '<div class="warning pure-menu">' 
                            . $oRenderer->renderShortInfo('warn') . sprintf($oBackend->lB('update.available-yes') , $oBackend->oUpdate->getLatestVersion()) 
                            . '<br><a href="?page=update">'.$oBackend->lB('nav.update.label').'</a>'
                            .'</div><br>'
                        :  ''
                    ;
                ?>
                    
                        
                <div class="pure-menu">
                    
                    <?php echo $oBackend->installationWasDone() ? $oBackend->getNavi() : ''; ?>
                    
                </div>

            </div>
        </div>
        <script>
            initPage();
        </script>
        <div style="clear: both;"></div>
        <?php echo $oBackend->logRender(); ?>
    </body></html>