<?php

require_once __DIR__.'/../vendor/rolling-curl/src/RollingCurl/RollingCurl.php';
require_once __DIR__.'/../vendor/rolling-curl/src/RollingCurl/Request.php';

require_once '../classes/analyzer.html.class.php';
// require_once '_analyzer.html.class.php';
global $oHtml;
$oHtml=new analyzerHtml();


function showTimer(){
    static $iStart;
    if (!$iStart){
        $iStart=microtime(1);
    } else {
        $iNow=microtime(1);
        echo ($iNow-$iStart) . " s: ";
    }
}

function maketest($sCode,$Expected){
    global $oHtml;

    $sReturn='';
    eval("\$result=$sCode;");
    $sReturn.="TEST  : " . $sCode. "<br>\n"
            ;
    if($result===$Expected){
        $sReturn.="--> OK [".print_r($result, 1)."]<br>\n";
    } else {
        $sReturn.=""
            . "RESULT: [".print_r($result, 1)."]<br>\n"
            . "EXPECT: [".print_r($Expected, 1)."]<br>\n"
            . "\n--> ERROR<br>\n\n";
    }
    $sReturn.="<br>\n\n";
    echo $sReturn;
}

    
$sUrl='https://www.axel-hahn.de/startseite/impressum';
echo '<a href="'.$sUrl.'">'.$sUrl.'</a><br>';

$oHtml->fetchUrl($sUrl);
showTimer(); echo "\nDONE fetchUrl()\n\n--- get data...\n";

/*
$aTmp=$oHtml->getCss(); 
showTimer(); echo "DONE getCss()\n";

$aTmp=$oHtml->getMetaDescription(); 
showTimer(); echo "DONE getMetaDescription()\n";
                        
$aTmp=$oHtml->getMetaKeywords(); 
showTimer(); echo "DONE getMetaKeywords()\n";

$aTmp=$oHtml->canFollowLinks(); 
showTimer(); echo "DONE canFollowLinks()\n";

$aTmp=$oHtml->getMetaGenerator(); 
showTimer(); echo "DONE getMetaGenerator()\n";

$aTmp=$oHtml->getMetaIndex(); 
showTimer(); echo "DONE getMetaIndex()\n";

$aTmp=$oHtml->getScripts(); 
showTimer(); echo "DONE getScripts()\n";

$aTmp=$oHtml->getMetaTitle(); 
showTimer(); echo "DONE getMetaTitle()\n";

$aTmp=$oHtml->getImages(); 
showTimer(); echo "DONE getImages()\n";

$aTmp=$oHtml->getLinks(); 
showTimer(); echo "DONE getLinks()\n";

*/


$aTmp=$oHtml->getReport(); 
showTimer(); echo "DONE getReport()\n";
echo count($aTmp['body']['links']['internal']) . " interne Links\n";


// print_r($aTmp);
echo "\n\n-----\n\n\n";

maketest("\$oHtml->getUrlType('/startseite/impressum')", "internal");
maketest("\$oHtml->getUrlType('//fonts.googleapis.com')", "external");
maketest("\$oHtml->getUrlType('https://fonts.googleapis.com')", "external");
maketest("\$oHtml->getUrlType('a-filename.txt')", "internal");
maketest("\$oHtml->getUrlType('#top')", "local");
maketest("\$oHtml->getUrlType('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAADICAIAAACRe4S/AAAc20lEQVR4nO3de3hcZZ0H8')", "local");
maketest("\$oHtml->getUrlType('http://www.axel-hahn.de/whatever')", "external");
maketest("\$oHtml->getUrlType('https://www.axel-hahn.de/whatever')", "internal");
maketest("\$oHtml->getUrlType('//www.axel-hahn.de/whatever')", "internal");
maketest("\$oHtml->getUrlType('file:////mypc/blubb')", "file");
maketest("\$oHtml->getUrlType('ssh://server/path/file.txt')", "other::ssh");
maketest("\$oHtml->getUrlType('ftp://server/path/file.txt')", "other::ftp");



// print_r($oHtml->getLinks()); 

echo "\n\nDONE.";
