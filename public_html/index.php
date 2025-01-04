<?php

    if(!defined('BACKEND')){
        define('BACKEND', false);
    } 
    
    require_once(__DIR__ . "/classes/backend.class.php");
    
    $bIsBackend = preg_match('#\/backend\/#', $_SERVER["REQUEST_URI"]);
    $sBaseUrl= preg_replace('/(\/backend\/|\?.*)/', '', $_SERVER["REQUEST_URI"]);
    
    $sBackendRel=$bIsBackend ? '.' : './backend';
    
    if($bIsBackend){
        // echo "DEBUG: backend<br>";
        $oBackend = new backend();
    } else {
        // echo "DEBUG: fronmtend<br>";
        $oBackend = new backend(0, 'public');
    }
    
    if(!$oBackend->installationWasDone() && !$bIsBackend){
        header('Location: '.$sBackendRel.'/?page=installer');
        die();
    }
    
    // ----- INIT CACHE
    $iSiteId=isset($_GET['siteid']) ? $_GET['siteid'] : (isset($_SESSION['siteid']) ? $_SESSION['siteid'] : false);
    // $iSiteId=$oBackend->getSiteId();
    $bUseCache=$bIsBackend && $iSiteId && $oBackend->isCacheable() && $_SERVER['REQUEST_METHOD']=='GET';
    // $bUseCache=false;
    $sRefFile=false;
    if($bUseCache){
        require_once(__DIR__ . "/vendor/ahcache/cache.class.php");
        $sRefFile=__DIR__. '/data/indexlog-siteid-'.$iSiteId.'.log';
        $aGetParams=$_GET;
        unset($aGetParams['page']);
        unset($aGetParams['siteid']);
        unset($aGetParams['lang']);
        $sCacheId='backend-page'
                // .'|user='.$_SESSION['AUTH_USER']
                // .'|template='...
                .'|'.$oBackend->aAbout['product']
                .'|'.$oBackend->aAbout['version']
                .'|'.$oBackend->aAbout['date']
                .'|siteid='.$iSiteId
                .'|skin='.$oBackend->getSkin()
                .'|lang='.$oBackend->getLang()
                .'|page='.$oBackend->getPage()
                .'|more_get_params='.print_r($aGetParams, 1)
                ;
        // echo $sCacheId.'<br>'; // die();
        $oCache = new AhCache($oBackend->getCacheModule(), $sCacheId);
        if (!$oCache->isExpired()){
            header("X-CACHE-DELIVERY: YES");
            echo $oCache->read();
            return true;
            exit(0);
        }
    }
    // ----- START PAGE
    header("X-CACHE-DELIVERY: NO");
    ob_start();

    require_once(__DIR__ . "/vendor/cdnorlocal/cdnorlocal.class.php");

    $oRenderer = new ressourcesrenderer();

    global $oCdn;
    $oCdn=new axelhahn\cdnorlocal([
        'vendordir'=>__DIR__.'/vendor/cache',
        'vendorurl'=>($bIsBackend ? '..' :'') . '/vendor/cache',
        'debug'=>0,
        ]);

    $oCdn->setLibs([
        "pure/3.0.0",
        "datatables/1.10.21",
        "font-awesome/6.6.0",
        "jquery/3.7.1",
        "select2/4.0.13",
        "Chart.js/4.4.1"
    ]);
    // $oCdn->dump(); exit(0);

    // ----------------------------------------------------------------------
    // F U N C T I O N S
    // ----------------------------------------------------------------------

    /**
     * get new querystring - create the new querystring by existing query string
     * of current request and given new parameters
     * @param array $aQueryParams
     * @return string
     */
    function getNewQs($aQueryParams = []) {
        $s = false;
        $aDelParams = ["doinstall"];

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

    // ----------------------------------------------------------------------
    // GENERATE CONTENT
    // ----------------------------------------------------------------------

    $sHtmlHead=''
        
            // pure css
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
            .$oCdn->getHtmlInclude($oCdn->getLibRelpath('Chart.js')."/chart.umd.js") . "\n"

            // select2
            .$oCdn->getHtmlInclude($oCdn->getLibRelpath('select2')."/css/select2.min.css") . "\n"
            .$oCdn->getHtmlInclude($oCdn->getLibRelpath('select2')."/js/select2.min.js") . "\n"

            // <link href="vendor/select2/dist/css/select2.min.css" rel="stylesheet" />
            // <script src="vendor/select2/dist/js/select2.min.js"></script>

            // more
            .'<script src="'.$sBackendRel.'/javascript/functions.js"></script>'
            .'<link rel="stylesheet" href="'.$sBackendRel.'/main.css">'
            .'<link rel="stylesheet" href="'.$sBackendRel.'/skins/'.$oBackend->getSkin().'/skin.css">'

            ;
    
    // ----------------------------------------------------------------------
    // SEND SECURITY HEADER
    // ----------------------------------------------------------------------
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']){
        header('Strict-Transport-Security: max-age=63072000');
    }
    header('X-XSS-Protection: 1; mode=block');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Feature-Policy: sync-xhr \'self\'');

    // ----------------------------------------------------------------------
    // GET CONTENT
    // ----------------------------------------------------------------------
    if($oBackend->isNavitemHidden()){
        header('HTTP/1.0 403 Forbidden');
        $sHtmlContent = $oBackend->getHead()
                .'<h3>'.$oBackend->lB('error.403.title').'</h3>'
                .$oRenderer->renderMessagebox($oBackend->lB('error.403.message'), 'warning')
                ;
    } else {
        $sHtmlContent = $oBackend->getHead()
            .($bIsBackend ? $oBackend->getBreadcrumb() : '')
            .$oBackend->getContent()
            ; 
    }
    
    // ----------------------------------------------------------------------
    // GET LEFT NAVIGATION
    // ----------------------------------------------------------------------
    $sHtmlNaviLeft=$oBackend->installationWasDone() 
            ? $oBackend->getNavi() . ($bIsBackend ? '' : $oBackend->getLangNavi() )
            : ''
        ;

    // get git branch name
    $branchname = '';
    $sHeadfile=__DIR__.'/../.git/HEAD';
    if(file_exists($sHeadfile)){
        $stringfromfile = file($sHeadfile, FILE_USE_INCLUDE_PATH);
        $firstLine = $stringfromfile[0]; //get the string from the array
        $explodedstring = explode("/", $firstLine, 3); //seperate out by the "/" in the string
        $branchname = trim($explodedstring[2]);
        if($branchname=='main' || $branchname=='master'){
            $branchname='';
        }
        $branchname = $branchname ? '<br>('.$branchname.')' : '';
    }
    $oBackend->getMoreJS();
    
?><!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex,nofollow">
        <?php echo $sHtmlHead; ?>
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
                    echo $sHtmlContent; 
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
                            <span>v<?php echo $oBackend->aAbout['version'].$branchname; ?></span>
                        </div>

                    </div>
                    <!--
                    <?php echo $oBackend->aAbout['product']; ?>
                    -->
                </a></h1>
                <?php echo $oBackend->getUpdateInfobox(); ?>    
                        
                <div class="pure-menu">
                    
                    <?php echo $sHtmlNaviLeft; ?>
                    
                </div>

            </div>
        </div>
        <div id="divStatus"></div>
        
        <iframe name="selfiframe"></iframe>
        <footer>
            <?php 
                echo '<a href="'.$oBackend->aAbout['urlDocs'].'" target="_blank"><strong>'.$oBackend->aAbout['product'].'</strong></a>'
                        // .' v'.$oBackend->aAbout['version']
                        .' &copy '.$oBackend->aAbout['author']
                        .' 2015-'.substr($oBackend->aAbout['date'], 0, 4)
                        ; 
            ?>
        </footer>
        <?php 

            // load a page specific js file
            $sMoreJs=$oBackend->getMoreJS();
            echo $sMoreJs ? '<script src="'.$sBackendRel.'/'.$sMoreJs.'"></script>' : '';
            
            echo $oBackend->getCustomFooter(); 
            echo $oBackend->logRender(); 
            // echo '<pre>'.print_r($_SERVER, 1).'</pre>';
        ?>
    </body></html><?php

    $sHtmldata= ob_get_contents();
    // ob_end_flush();
    if ($bUseCache){
        $oCache->write($sHtmldata,30,$sRefFile);
    }
    // echo $sHtmldata;