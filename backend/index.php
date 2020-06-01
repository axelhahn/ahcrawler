<?php

/*
require_once(dirname(__DIR__) . "/classes/cache.class.php");

$sCacheId=$_SERVER['REQUEST_URI'];
$sRefFile=__DIR__.'/../tmp/_last_change.txt';

$oCache=new AhCache('ahcrawler', $sCacheId);
if (!file_exists($sRefFile)){
    touch($sRefFile);
}

if(
    !$oCache->isExpired() && $oCache->isNewerThanFile($sRefFile)
    && !count($_POST)
) {  
    echo $oCache->read();
    return true;
}
*/

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
    // "pure/1.0.1",
    "pure/2.0.3",
    "datatables/1.10.20",
    "font-awesome/5.13.0",
    "jquery/3.5.0",
    "Chart.js/2.9.3"
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

// ob_start();

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


        <div id="overlay">
            <div class="divdialog" >
                <button class="button-error pure-button"
                   onclick="hideModal(); return false;"
                   style="float: right"
                   > X </button>
                <div id="dialogtitle">
                    TITLE
                </div>
                    
                <div id="dialogcontent">CONTENT</div>
            </div>
        </div>
        
        <div id="content">
            <div class="maincontent">
                <?php 
                    echo $oBackend->getHead().$oBackend->getContent(); 
                ?>
            </div>
        </div>

        <div id="navmain" class="sidebar pure-u-1 ">
            <div class="header">
                <h1 class="brand-title"><a href="?">
                        AH
                        <div id="pacman">
                            <div>
                                r a w l e r<br>
                                <span>v<?php echo $oBackend->aAbout['version']; ?></span>
                            </div>
                            
                        </div>
                        <!--
                        <?php echo $oBackend->aAbout['product']; ?>
                        -->
                    </a></h1>
                <?php echo $oBackend->getUpdateInfobox(); ?>    
                        
                <div class="pure-menu">
                    
                    <?php echo $oBackend->installationWasDone() ? $oBackend->getNavi() : ''; ?>
                    
                </div>

            </div>
        </div>
        <div id="divStatus" style="display: none;"></div>
        <script>
            initPage();
            updateStatus("<?php echo 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '').'://'.$_SERVER['HTTP_HOST'] . preg_replace('/\?.*/', '', 	$_SERVER['REQUEST_URI']) . 'get.php?action=getstatus'; ?>");
        </script>
        <div style="clear: both;"></div>
        <?php 
            echo $oBackend->logRender(); 
            // echo '<pre>'.print_r($_SERVER, 1).'</pre>';
        ?>
    </body></html><?php

    /*
    $sHtmldata= ob_get_contents();
    // ob_end_flush();
    
    $oCache->write($sHtmldata,3600);
     */