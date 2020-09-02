<?php
/*
 * ____________________________________________________________________________
 *          __    ______                    __             
 *   ____ _/ /_  / ____/________ __      __/ /__  _____    
 *  / __ `/ __ \/ /   / ___/ __ `/ | /| / / / _ \/ ___/    
 * / /_/ / / / / /___/ /  / /_/ /| |/ |/ / /  __/ /        
 * \__,_/_/ /_/\____/_/   \__,_/ |__/|__/_/\___/_/         
 * ____________________________________________________________________________ 
 * Free software and OpenSource * GNU GPL 3
 * DOCS https://www.axel-hahn.de/docs/ahcrawler/index.htm
 * 
 * THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE <br>
 * LAW. EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR <br>
 * OTHER PARTIES PROVIDE THE PROGRAM ?AS IS? WITHOUT WARRANTY OF ANY KIND, <br>
 * EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED <br>
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE <br>
 * ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU. <br>
 * SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY <br>
 * SERVICING, REPAIR OR CORRECTION.<br>
 * 
 * ----------------------------------------------------------------------------
 * 
 * C L I 
 * 
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
    'label' => 'AhCrawler :: C L I',
    'description' => 'CLI tool to start crawling and ressource scan',
    'params'=>array(
        'action'=>array(
            'short' => 'a',
            'value'=> CLIVALUE_REQUIRED,
            'pattern'=>'/^(list|index|update|empty|flush|reindex)$/',
            'shortinfo' => 'name of action',
            'description' => 'The action value is one of list | index | update | empty | flush | reindex',
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
            'description' => 'The id is an integer value ... see list action.',
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

// echo $oCli->getlabel();
$oCrawler=new crawler();

$oCli=new axelhahn\cli($aParamDefs);
// http://www.patorjk.com/software/taag/#p=display&f=JS%20Stick%20Letters&t=ahCrawler%20-%20CLI


$oCli->color('head');
echo 
'_______________________________________________________________________________

            __   _____                __          _______   ____
      ___ _/ /  / ___/______ __    __/ /__ ____  / ___/ /  /  _/
     / _ `/ _ \/ /__/ __/ _ `/ |/|/ / / -_) __/ / /__/ /___/ /  
     \_,_/_//_/\___/_/  \_,_/|__,__/_/\__/_/    \___/____/___/  v'.$oCrawler->aAbout['version'].'

     DOCS: '.$oCrawler->aAbout['urlDocs'].'
     Free software and open source. '.$oCrawler->aAbout['license'].'; release date: '.$oCrawler->aAbout['date'].'
     (c) '.$oCrawler->aAbout['author'].'

_______________________________________________________________________________

';
$oCli->color('reset');

// print_r($oCli->getopt());
if ($oCli->getvalue("help") ||!count($oCli->getopt())){
    echo $oCli->showhelp();
    $sBase='php ./'.basename(__FILE__);
    echo '
ACTIONS:
    empty:   remove existing data of a profile
             (requires -d [value] and -p [profile])

    flush:   drop data for ALL profiles
             (requires -d [value])

    index:   start crawler to reindex searchindex or ressources 
             (requires -d [value] and -p [profile])

    list:    list all existing profiles

    reindex: delete existing data of the given profile an reindex searchindex 
             and ressources (requires -p [profile])
             This is a combination of actions empty + index for [all] data.

    update:  start crawler to update missed searchindex or ressources 
             (requires -d [value] and -p [profile])

EXAMPLES:

    show infos:

      '.$sBase.' -a list
          list all profiles

    delete indexed data:

      '.$sBase.' -a flush
          drop all data of ALL profiles (it keeps the search results)

      '.$sBase.' -a empty -d all -p 1
          erase data for single profile [1] (it keeps the search results)

    index data:

      '.$sBase.' -a reindex -p 1
          recreate search index + ressources for profile [1]

      '.$sBase.' -a index -d all -p 1
          create search index and ressources for profile [1]

      '.$sBase.' -a update -d all -p 1
          update missed items in search index and ressources for profile [1]

';
    exit(0);
}

if ($oCli->getvalue("action")===false){
    echo "\nwhat shall we do ??\n";
    $oCli->read("action");
}
$oCli->color('ok', 'OK, action is ['.$oCli->getvalue("action").']'."\n\n");
$sAction=$oCli->getvalue("action");

if ($sAction==="list"){
    echo "--- Existing profiles:\n\n";
    foreach ($oCrawler->getProfileIds() as $MySiteId){
        $oCrawler->setSiteId($MySiteId);
        $aProfile=$oCrawler->getEffectiveProfile($MySiteId);
        echo "$MySiteId: ".$aProfile['label']."\n".$aProfile['description']."\n\n";
    }
    echo "\n--> valid profile ids are: " 
        . implode(", ", $oCrawler->getProfileIds())
        . "\n\n"
        ;
    exit(0);
}

if ($sAction!=="list" && $sAction!=="reindex" && $oCli->getvalue("data")===false){
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

$aOptions=$oCrawler->getEffectiveOptions();
echo "INFO: set memory_limit to ".$aOptions['crawler']['memoryLimit']."\n";
ini_set('memory_limit', $aOptions['crawler']['memoryLimit']);

$aProfileIds=$oCli->getvalue("profile") ? array($oCli->getvalue("profile")) : $oCrawler->getProfileIds();
foreach ($aProfileIds as $sSiteId){
    echo "\n-------------------------------------------------------------------------------\n"
        . "profile id $sSiteId ... $sAction ... $sWhat\n"
        ;    
    $oCli->color('cli');
    switch ($sAction){
        
        case 'reindex':
            $oCrawler->setSiteId($sSiteId);
            $oCrawler->flushData(array('searchindex'=>1, 'ressources'=>1), $sSiteId);
            
            $sWhat='all';
            // no break but continue :-)

        case 'index':
            switch ($sWhat){
                case "all":
                    $oCrawler->setSiteId($sSiteId);
                    $oCrawler->run();
                    unset($oCrawler); // prevents locking of sqlite database
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
        case 'empty':
            $oCrawler->flushData(array($sWhat=>1), $sSiteId);
            break;
            
        default:
            echo "ooops ... action [$sAction] was not implemented yet\n";
    }
}
$oCli->color('reset');
echo "\nDONE.\n\n";
// ----------------------------------------------------------------------
