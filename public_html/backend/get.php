<?php
require_once(dirname(__DIR__) . "/classes/backend.class.php");


$sAction = isset($_GET['action']) ? $_GET['action'] : '';

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
            $oBackend = new backend($sSiteid);
            $oBackend->logfileAppend('error','PHP interpreter was not found. It is needed on console as cli program. Cannot start crawler :-/');
            return false;
        } 
        exec('php '.dirname(__DIR__).'/cronscripts/reindex_all_profiles.php -p '.$sSiteid.' & ');
        
        return true;

        break;
    
    default:
        header('HTTP 1.1 400 Bad Request');
        die('<h1>400 Bad Request</h1>Wrong action ['.$sAction.']');
}

header('HTTP 1.1 400 Bad Request');
die('<h1>400 Bad Request</h1>Wrong action ...');
