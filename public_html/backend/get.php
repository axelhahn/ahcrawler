<?php
require_once(dirname(__DIR__) . "/classes/backend.class.php");


$sAction = isset($_GET['action']) ? $_GET['action'] : '';

if(!$sAction && !$sPostAction){
    http_response_code(400);
    die('<h1>400 Bad Request</h1>Nothing to do');
}
$sSiteid = isset($_POST['siteid']) ? (int)$_POST['siteid'] 
        : (isset($_GET['siteid']) ? (int)$_GET['siteid'] : false);

switch($sAction){
    case 'getstatus':
        $oBackend = new backend();
        echo $oBackend->getStatus();
        return true;
        break;
    case 'reindex-searchindex':
    case 'update-searchindex':
        ignore_user_abort(true);
        set_time_limit(0);
        
        if($sSiteid){
            exec('php -v', $sOut, $iRcPhp);
            if(!$iRcPhp==0){
                $oBackend = new backend($sSiteid);
                $oBackend->logfileAppend('error','PHP interpreter was not found. It is needed on console as cli program. Cannot start crawler :-/');
                return false;
            }
            $sParams=($sAction=='update-searchindex' ? ' --update' : '')
                . ' --profile '.$sSiteid
                ;
            exec('php '.dirname(__DIR__).'/cronscripts/reindex_all_profiles.php '.$sParams.' & ');
        }
        return true;

        break;

    case 'reindex-singlepage':
        if($sSiteid){
            $oBackend = new backend($sSiteid);
            $sUrl = isset($_POST['url']) ? $_POST['url'] 
                    : (isset($_GET['url']) ? $_GET['url'] : false);
            if(!$sUrl){
                $oBackend->logfileAppend('error',"Missing url for $sAction");
                return false;
            }
            $oBackend->logfileAppend('info',"Reindex single url to update search index: $sUrl");
            require_once(dirname(__DIR__) . "/classes/crawler.class.php");
            $oCrawler=new crawler($sSiteid);
            $oCrawler->updateMultipleUrls([$sUrl], false);
        }
        return true;

        break;
    
    default:
        http_response_code(400);
        die('<h1>400 Bad Request</h1>Wrong action ['.$sAction.']');
}
http_response_code(400);
die('<h1>400 Bad Request</h1>Wrong action ...');
