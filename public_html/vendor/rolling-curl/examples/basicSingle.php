<?php

require __DIR__ . '/../src/RollingCurl/RollingCurl.php';
require __DIR__ . '/../src/RollingCurl/Request.php';

// using this library to do a single request is a bit silly, but it will work.

$rollingCurl = new \RollingCurl\RollingCurl();
$rollingCurl
    ->get('http://google.com')
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