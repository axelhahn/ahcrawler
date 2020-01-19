<?php
require_once(__DIR__."/../classes/crawler.class.php");
$oCrawler=new crawler(1);

$oCrawler->updateSingleUrl('https://www.axel-hahn.de/batch/batchecke/tipps');
echo "\nDONE.\n\n";
// ----------------------------------------------------------------------
