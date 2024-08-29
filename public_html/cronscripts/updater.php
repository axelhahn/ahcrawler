<?php
/*
 * ----------------------------------------------------------------------
 * 
 * AhCRAWLER :: Cronjob - updater
 * 
 * This script checks if an update exists and installs the update.
 * With parameters you can handle a check only ... or force an 
 * installation of the current version.
 * 
 * ----------------------------------------------------------------------
 * 
 * Add this script as a daily or weekly cronjob to keep the software up to date.
 * 
 * REMARK:
 * php binary must be in the search path - or set PATH in crontab file.
 *
 * ----------------------------------------------------------------------
 * 
 * PARAMETERS:
 *   -c
 *   --check (without value)
 *     Check only.
 *     If a newer version would exist, you get just a message (without installing the update).
 * 
 *   -f
 *   --force (without value)
 *     force installation
 *     If no newer version exists it reinstalls the current version.
 * 
 *   -h
 *   --help (without value)
 *     show this help
 * 
 * ----------------------------------------------------------------------
 */

require_once(__DIR__ . '/../classes/crawler.class.php');
require_once(__DIR__ . '/../vendor/ahcli/cli.class.php');
require_once(__DIR__ . '/../vendor/ahwebinstall/ahwi-updatecheck.class.php');
require_once(__DIR__ . '/../vendor/ahwebinstall/ahwi-installer.class.php');

$aDirs=[
    'download'=>[
        'root'=>dirname(__DIR__),
        'zip' =>dirname(__DIR__).'/tmp/__latest.zip',
    ],
    'git'=>[
        'root'=>dirname(dirname(__DIR__)),
        'zip' =>false,
    ]
];

$aParamDefs=array(
    'label' => 'AhCrawler :: Updater',
    'description' => 'CLI updater tool. It checks if a newer version exists and - if so - installs the update.',
    'params'=>array(
        'check'=>array(
            'short' => 'c',
            'value'=> CLIVALUE_NONE,
            'shortinfo' => 'Check only.',
            'description' => 'If a newer version would exist, you get just a message and exitcode 1 (without installing the update).',
        ),
        'force'=>array(
            'short' => 'f',
            'value'=> CLIVALUE_NONE,
            'shortinfo' => 'force installation',
            'description' => 'If no newer version exists it reinstalls the current version.',
        ),
        'help'=>array(
            'short' => 'h',
            'value'=> CLIVALUE_NONE,
            'shortinfo' => 'show this help',
            'description' => '',
        ),
    ),
);


// ----- check params
$oCli=new axelhahn\cli($aParamDefs);

echo $oCli->getlabel();

if($oCli->getvalue('help')){
    echo $oCli->showhelp();
    exit(0);
}


// ----- init update check
$oCrawler = new crawler();
$aOptions=$oCrawler->getEffectiveOptions();
$oCheck=new ahwiupdatecheck(array(
    'product'=>$oCrawler->aAbout['product'],
    'version'=>$oCrawler->aAbout['version'],
    'baseurl'=>$aOptions['updater']['baseurl'],
    'tmpdir'=>($aOptions['updater']['tmpdir'] ? $aOptions['updater']['tmpdir'] : __DIR__.'/../tmp/'),
    'ttl'=>$aOptions['updater']['ttl'],
));


// ----- check version on server 
echo 'Checking update of your '.$oCrawler->aAbout['product'].' v'.$oCrawler->aAbout['version'].'...'.PHP_EOL;
$oCheck->getUpdateInfos(true);

$bHasUpdate=$oCheck->hasUpdate();
echo ($bHasUpdate 
        ? 'Update AVAILABLE: ' . $oCheck->getLatestVersion() 
        : 'Version '.$oCheck->getLatestVersion() .' was found in the internet. You are UP TO DATE :-)'
    ).PHP_EOL
    ;

if($oCli->getvalue('check')
  || !$bHasUpdate && !$oCli->getvalue('force')
){
    exit($bHasUpdate ? 1 : 0);
}


// ----- start / force update
echo PHP_EOL;
$oInstaller=new ahwi(array(
    'product'=>$oCrawler->aAbout['product'].' v'.$oCrawler->aAbout['version'],
    'source'=>$oCheck->getDownloadUrl(),
    'md5'=>$oCheck->getChecksumUrl(),
    'installdir'=>$aDirs['git']['root'],
    'tmpzip'=>$aDirs['git']['zip'],
    'checks'=>$oCrawler->aAbout['requirements'],
));

$bIsGit=$oInstaller->vcsDetect('git');
if(!$bIsGit){
    echo 'Starting Zip-Installer...'.PHP_EOL.PHP_EOL;
    $oInstaller=new ahwi(array(
        'product'=>$oCrawler->aAbout['product'].' v'.$oCrawler->aAbout['version'],
        'source'=>$oCheck->getDownloadUrl(),
        'md5'=>$oCheck->getChecksumUrl(),
        'installdir'=>$aDirs['download']['root'],
        'tmpzip'=>$aDirs['download']['zip'],
        'checks'=>$oCrawler->aAbout['requirements'],
    ));
    if(!$oInstaller->download(true)){
        echo 'ERROR: download failed :-/'.PHP_EOL;
        exit(2);
    }

    echo PHP_EOL;
    if(!$oInstaller->install()){
        echo 'ERROR: installation failed :-/'.PHP_EOL;
        exit(3);
    }
} else {
    echo 'Starting git pull ...'.PHP_EOL.PHP_EOL;
    $aResult=$oInstaller->vcsUpdate('git');
    if($aResult===false){
        echo 'ERROR: git cli client not detected locally'.PHP_EOL;
        exit(4);
    } else {
        $iRc=$aResult[0];
        echo implode(PHP_EOL, $aResult[1]).PHP_EOL;
        if ($iRc!=0){
            echo 'ERROR: update with git failed :-/'.PHP_EOL;
            exit(5);
        }
    }
}


echo PHP_EOL . '--- Installation was successful :-)'.PHP_EOL;