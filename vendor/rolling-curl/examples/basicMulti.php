<?php

require __DIR__ . '/../src/RollingCurl/RollingCurl.php';
require __DIR__ . '/../src/RollingCurl/Request.php';

$rollingCurl = new \RollingCurl\RollingCurl();
$rollingCurl
    ->get('http://yahoo.com')
    ->get('http://google.com')
    ->get('http://hotmail.com')
    ->get('http://msn.com')
    ->get('http://reddit.com')
    ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) {
        if (preg_match("#<title>(.*)</title>#i", $request->getResponseText(), $out)) {
            $title = $out[1];
        }
        else {
            $title = '[No Title Tag Found]';
        }
        echo "Fetch complete for (" . $request->getUrl() . ") $title " . PHP_EOL;
    })
    ->execute();
;