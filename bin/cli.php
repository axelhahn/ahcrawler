<?php
/*
 * 
 * AhCRAWLER :: C L I 
 * 
 */
require_once(__DIR__."/../classes/cli.class.php");
require_once(__DIR__."/../classes/crawler.class.php");
require_once(__DIR__."/../classes/ressources.class.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ----------------------------------------------------------------------
// confing
// ----------------------------------------------------------------------

$aParamDefs=array(
    'label' => 'AhCRAWLER :: C L I',
    'description' => 'start crawling and ressource scan',
    'params'=>array(
        'action'=>array(
            'short' => 'a',
            'value'=> CLIVALUE_REQUIRED,
            'pattern'=>'/^(index|update|flush|list)$/',
            'shortinfo' => 'name of action',
            'description' => 'The action value is one of index | update | flush | list',
        ),
        'data'=>array(
            'short' => 'd',
            'value'=> CLIVALUE_REQUIRED,
            'pattern'=>'/^(searchindex|ressources|search|all)$/',
            'shortinfo' => 'kind of data',
            'description' => 'The data value is one of searchindex | ressources | search | all',
        ),
        'profile'=>array(
            'short' => 'p',
            'value'=> CLIVALUE_REQUIRED,
            'pattern'=>'/^[0-9]*$/',
            'shortinfo' => 'profile id of the config',
            'description' => 'The id is an integer value ... it is one of the subkeys below profile key.',
        ),
        'help'=>array(
            'short' => 'h',
            'value'=> CLIVALUE_NONE,
            'shortinfo' => 'show help',
            'description' => '',
        ),
    ),
);
// ----------------------------------------------------------------------
// functions
// ----------------------------------------------------------------------

function getProfile(){
    global $oCli;
    $oCrawler=new crawler(); 
    $aIds=$oCrawler->getProfileIds();
    if (count($aIds)==1){
        echo "INFO: only one profile is configured.\n";
        return $aIds[0];
    } else {
        echo "no profile id was given.\n";
        echo "valid ids are: " . implode(",", $oCrawler->getProfileIds())." (enter 0 for all profiles)\n";
        return $oCli->read("profile");
    }
}

// ----------------------------------------------------------------------
// main
// ----------------------------------------------------------------------

ini_set('memory_limit', '512M');

$oCli=new axelhahn\cli($aParamDefs);
// http://www.patorjk.com/software/taag/#p=display&f=JS%20Stick%20Letters&t=ahCrawler%20-%20CLI


$oCli->color('head');
echo 
'_______________________________________________________________________________

             __   __                  ___  __      __         
   /\  |__| /  ` |__)  /\  |  | |    |__  |__)    /  ` |    | 
  /~~\ |  | \__, |  \ /~~\ |/\| |___ |___ |  \    \__, |___ | 

_______________________________________________________________________________

';
$oCli->color('reset');

// echo $oCli->getlabel();
$oCrawler=new crawler();

// print_r($oCli->getopt());
if ($oCli->getvalue("help") ||!count($oCli->getopt())){
    echo $oCli->showhelp();
    exit(0);
}

if ($oCli->getvalue("action")===false){
    echo "\nwhat shall we do ??\n";
    $oCli->read("action");
}
$oCli->color('ok', 'OK, action is ['.$oCli->getvalue("action").']'."\n\n");
$sAction=$oCli->getvalue("action");

if ($sAction==="list"){
    echo "valid profile ids: " 
        . implode(", ", $oCrawler->getProfileIds())
        . "\n\n"
        ;
    exit(0);
}

if ($sAction!=="list" && $oCli->getvalue("data")===false){
    echo "\nwhat data ??\n";
    $oCli->read("data");
}
$oCli->color('ok', 'OK, data is ['.$oCli->getvalue("data").']'."\n\n");



// ----------------------------------------------------------------------
// start actions
// ----------------------------------------------------------------------

$sWhat=$oCli->getvalue("data");


if ($sAction=="flush"){
    $oCrawler->flushData(array($sWhat=>1));
    exit(0);
}

// for other actions we need the profile id
if ($oCli->getvalue("profile")===false){
    $aIds=$oCrawler->getProfileIds();
    if (count($aIds)==1){
        $oCli->color('info', "INFO: only one profile is configured.\n");
        $oCli->setvalue("profile", $aIds[0]);
    } else {
        $oCli->color('error', "no profile id was given.\n");
        echo "valid ids are: " . implode(",", $oCrawler->getProfileIds())." (enter 0 for all profiles)\n";
        $oCli->read("profile");
    }
}
$oCli->color('ok', 'OK, profile is ['.$oCli->getvalue("profile").']'."\n\n");

$aProfileIds=$oCli->getvalue("profile") ? array($oCli->getvalue("profile")) : $oCrawler->getProfileIds();
foreach ($aProfileIds as $sSiteId){
    echo "\n-------------------------------------------------------------------------------\n"
        . "profile id $sSiteId ... $sAction ... $sWhat\n"
        ;
    $oCli->color('cli');
    switch ($sAction){
        
        case 'index':
            switch ($sWhat){
                case "all":
                    $oCrawler->setSiteId($sSiteId);
                    $oCrawler->run();
                    $oRes=new ressources();
                    $oRes->setSiteId($sSiteId);
                    $oRes->cleanupRessources();
                    $oRes->addRessourcesFromPages();
                    $oRes->crawlRessoures();
                    break;
                case "searchindex":
                    $oCrawler->setSiteId($sSiteId);
                    $oCrawler->run();
                    break;
                case "ressources":
                    $oRes=new ressources();
                    $oRes->setSiteId($sSiteId);
                    $oRes->cleanupRessources();
                    $oRes->addRessourcesFromPages();
                    $oRes->crawlRessoures();
                    break;
            }
            break;
            
        case 'update':
            switch ($sWhat){
                case "searchindex":
                    $oCrawler->setSiteId($sSiteId);
                    $oCrawler->run(true);
                    break;
                case "ressources":
                    $oRes=new ressources();
                    $oRes->setSiteId($sSiteId);
                    $oRes->crawlRessoures();
                    break;
            }
            break;
            
        default:
            echo "ooops ... action [$sAction] was not implemented yet\n";
    }
}
$oCli->color('reset');
echo "\ndone.";
// ----------------------------------------------------------------------
