<?php

require __DIR__ . '/../src/RollingCurl/RollingCurl.php';
require __DIR__ . '/../src/RollingCurl/Request.php';

$rollingCurl = new \RollingCurl\RollingCurl();

$sites = array(
    'http://yahoo.com' => array(
        CURLOPT_TIMEOUT => 15
    ),
    'http://google.com' => array(
        CURLOPT_TIMEOUT => 5
    ),
    'http://hotmail.com' => array(
        CURLOPT_TIMEOUT => 10
    ),
    'http://msn.com' => array(
        CURLOPT_TIMEOUT => 10
    ),
    'http://reddit.com' => array(
        CURLOPT_TIMEOUT => 25
    ),
);

foreach ($sites as $url => $options) {
    $request = new \RollingCurl\Request($url);
    $request->setOptions($options);
    $rollingCurl->add($request);
}

$rollingCurl
    ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) {
        echo "Fetch complete for (" . $request->getUrl() . ")" . PHP_EOL;
    })
    ->execute()
;