<?php

// Search for keywords
// http://cmstest.axel-hahn.de/axel/php/ahcrawler/webservice.php?&class=ahsearch&init=[1]&action=searchKeyword&args=[%22russ%22]&type=raw

// Search for page titles
// http://cmstest.axel-hahn.de/axel/php/ahcrawler/webservice.php?&class=ahsearch&init=[1]&action=searchTitle&args=[%22site%22]&type=raw

require_once(__DIR__ . "/classes/sws.class.php");

$aConfig=json_decode(file_get_contents(__DIR__ . "/classes/sws-config.json"), 1);
$oSws=new sws($aConfig);
$oSws->run();