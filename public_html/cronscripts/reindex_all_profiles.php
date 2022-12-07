<?php
/*
 * ----------------------------------------------------------------------
 * 
 * AhCRAWLER :: Cronjob - reindex all profiles
 * 
 * This script flushes all indexed data of all profiles and then
 * reindexes them again.
 * 
 * ----------------------------------------------------------------------
 * 
 * Add this script as a daily or weekly cronjob to keep information in the
 * backend up to date.
 * 
 * REMARK:
 * php binary must be in the search path - or set PATH in crontab file.
 *
 * ----------------------------------------------------------------------
 * 
 * PARAMETERS:
 * 
 * -h
 * --help
 *      show help
 * 
 * -u
 * --update
 *      update only
 *      Do not flush and reindex all - only update 
 *      (=rescan errors and missed items)
 * 
 * -p
 * --profile
 *      do handle a single profile only instead of all profiles
 * 
 * ----------------------------------------------------------------------
 */

require_once(__DIR__ . '/../classes/crawler.class.php');
require_once(__DIR__ . '/../classes/cli.class.php');

// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------

/**
 * run a shell command with showing the executed commmand.
 * It returns the exit code
 * 
 * @param string  $sCmd  command to execute
 * @return integer
 */
function run($sCmd){
        echo "\n";
        echo "RUN ::\n";
        echo "RUN :: **************************************** START\n";
        echo "RUN :: $$sCmd\n";
        echo "RUN ::\n\n";

        // exec($sCmd, $aOut, $iRc);
        // echo implode("\n", $aOut);
        system($sCmd, $iRc);


        echo "\n\n";
        echo "RUN ::\n";
        echo "RUN :: $$sCmd\n";
        echo "RUN :: rc=$iRc\n";
        echo "RUN :: **************************************** END\n\n";

        return $iRc;
}

// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------

$sScript = "php " . __DIR__ . '/../bin/cli.php';
$iRc=0;

$oCrawler = new crawler();

$aParamDefs=array(
    'label' => 'AhCrawler :: Cronjob - reindex all',
    'description' => 'CLI reindexer tool for a cronjob.'.PHP_EOL.'It flushes all indexed data of all profiles and then reindexes them.',
    'params'=>array(
        'update'=>array(
            'short' => 'u',
            'value'=> CLIVALUE_NONE,
            'shortinfo' => 'update only',
            'description' => 'Do not flush and reindex all - only update (=rescan errors and missed items)',
        ),
        'profile'=>array(
            'short' => 'p',
            'value'=> CLIVALUE_REQUIRED,
            'pattern'=>'/^[0-9]*$/',
            'shortinfo' => 'Handle a single profile id',
            'description' => 'Set a profile id. Do not handle all profiles - just a single one. Use this on tomeout problems on a shared hoster; default: all profiles',
        ),
        'help'=>array(
            'short' => 'h',
            'value'=> CLIVALUE_NONE,
            'shortinfo' => 'show this help',
            'description' => '',
        ),
    ),
);

$oCli=new axelhahn\cli($aParamDefs);

// ----- check params
if($oCli->getvalue('help')){
    echo $oCli->getlabel() . $oCli->showhelp();
    exit(0);
}


$aIds = $oCrawler->getProfileIds();
if($oCli->getvalue('profile')){
    echo "INFO: set single profile " . $oCli->getvalue('profile') . PHP_EOL;
    $aIds=array($oCli->getvalue('profile'));
} else {
    echo "INFO: processing ALL profiles" . PHP_EOL;
}


// ----- FULL REINDEX
if(!$oCli->getvalue('update')){

    // ----- FLUSH
    if($oCli->getvalue('profile')){
        $iRc += run($sScript . ' --action empty --data all --profile '.$oCli->getvalue('profile'));
    } else {
        $iRc += run($sScript . ' --action flush --data all');
    }


    // ----- SCAN
    foreach ($aIds as $iProfile) {
        $iRc += run($sScript . ' --action index --data searchindex --profile ' . $iProfile);
    }
    foreach ($aIds as $iProfile) {
        $iRc += run($sScript . ' --action index --data resources   --profile ' . $iProfile);
    }
}

// ----- UPDATE MISSING RESSOURCES
foreach ($aIds as $iProfile) {
    $iRc += run($sScript . ' --action update --data resources --profile ' . $iProfile);
}

echo PHP_EOL . "--- DONE.";
exit($iRc);
