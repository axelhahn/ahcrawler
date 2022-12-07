<?php
/*
 * Hi!
 * 
 * This file gives you a demonstration to implement a search form in your own website.
 * To make changes: make a copy with name "custom_[yourname].php" and then make 
 * your changes in that copy.
 * 
 * With it you can request this page to integrate a search on another domain.
 * For more information see 
 * https://www.axel-hahn.de/docs/ahcrawler/searchform.htm
 * 
 *  
 */
require_once(__DIR__ . "/../classes/search.class.php");

// --- defaults:
$sDefaultSiteId=1;
$sDefaultLang='en';

// ----- check current params:

$iSiteId=isset($_GET['siteid'])?(int)$_GET['siteid'] : $sDefaultSiteId;
$sLang=isset($_GET['guilang'])?$_GET['guilang'] : $sDefaultLang;

// ----- (1) init with site id:
$o = new ahsearch();
$o->setSiteId($iSiteId);

// or shorter:
// $o = new ahsearch(1);

// ----- (2) set the frontend language
$o->setLangFrontend($sLang);

// ----- (3) show form to enter search term
// most simple way:
// echo $o->renderSearchForm();

// with additional options
echo $o->renderSearchForm(array(
    'categories'=>1,
    'lang'=>1,
    'mode'=>1,
))
.'<br>'
;
// ------ (4) output of results
echo $o->renderSearchresults();
