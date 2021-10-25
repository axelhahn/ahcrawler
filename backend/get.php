<?php
require_once(dirname(__DIR__) . "/classes/backend.class.php");


$sAction = isset($_GET['action']) ? $_GET['action'] : '';
// $sPostAction = isset($_POST['action']) ? $_POST['action'] : '';

if(!$sAction && !$sPostAction){
    header('HTTP 1.1 400 Bad Request');
    die('<h1>400 Bad Request</h1>Nothing to do');
}

switch($sAction){
    case 'getstatus':
        $oBackend = new backend();
        echo $oBackend->getStatus();
        return true;
        break;
    case 'reindex-searchindex':
        ignore_user_abort(true);
        set_time_limit(0);
        
        $sSiteid = isset($_POST['siteid']) ? (int)$_POST['siteid'] 
                : (isset($_GET['siteid']) ? (int)$_GET['siteid'] : false);
        exec('php -v', $sOut, $iRcPhp);
        if(!$iRcPhp==0){
            echo 'PHP interpreter was not found. Cannot start crawler :-/';
            return false;
        } 
        exec('php '.dirname(__DIR__).'/cronscripts/reindex_all_profiles.php -p '.$sSiteid.' & ');
        echo 'Crawler was started.';
        return true;
        /*
        $oBackend = new backend();
        if (!$oBackend->checkAuth()){
            // header('HTTP 1.1 403 Access denied');
            // die('<h1>403 Access denied</h1>');
        }
        
        $sSiteid = isset($_POST['siteid']) ? $_POST['siteid'] 
                : isset($_GET['siteid']) ? $_GET['siteid'] : false;
        if($sSiteid){
            require_once(dirname(__DIR__) . "/classes/crawler.class.php");
            echo "indexing siteid $sSiteid ... ";
            $oCrawler = new crawler($sSiteid);
            ob_start();
            $oCrawler->run();
            $sReturn.='<pre>' . ob_get_contents() . '</pre>';
            ob_end_clean();
            echo $sReturn;
            echo "... done";
            return true;
        }
        header('HTTP 1.1 400 Bad Request');
        die('<h1>400 Bad Request</h1>missing siteid ['.$sAction.']');
         * 
         */
        break;
    
    default:
        header('HTTP 1.1 400 Bad Request');
        die('<h1>400 Bad Request</h1>Wrong action ['.$sAction.']');
}
/*
switch($sPostAction){
    case 'reindex-searchindex':
        ignore_user_abort(true);
        set_time_limit(0);
        $sSiteid = isset($_POST['siteid']) ? $_POST['siteid'] : false;
        if($sSiteid){
            require_once(dirname(__DIR__) . "/classes/crawler.class.php");
            $oCrawler = new crawler($this->_sTab);
            ob_start();
            $oCrawler->run();
            $sReturn.='<pre>' . ob_get_contents() . '</pre>';
            ob_end_clean();
            return true;
        }
        break;
    
    default:
        header('HTTP 1.1 400 Bad Request');
        die('<h1>400 Bad Request</h1>Wrong POST action');
}
*/

header('HTTP 1.1 400 Bad Request');
die('<h1>400 Bad Request</h1>Wrong action ...');
