<?php

require __DIR__ . '/../src/RollingCurl/RollingCurl.php';
require __DIR__ . '/../src/RollingCurl/Request.php';

/*
 * This example does the same thing as search scrape, but instead of letting
 * things get processed by the call back, we simply wait until all the HTTP
 * traffic has been run, then we process the request objects one at a time.
 *
 * This is an approach you may wish to take if your callback routine is
 * particularly long running, so as to not tie up the fetching phase as much.
 */

$rollingCurl = new \RollingCurl\RollingCurl();

for ($i = 0; $i <= 500; $i+=10) {
    // https://www.google.com/search?q=curl&start=10
    $rollingCurl->get('https://www.google.com/search?q=curl&start=' . $i);
}

$results = array();

$start = microtime(true);
echo "Fetching..." . PHP_EOL;
$rollingCurl
    ->setSimultaneousLimit(10)
    ->execute();
;
echo "...done in " . (microtime(true) - $start) . PHP_EOL;

foreach ($rollingCurl->getCompletedRequests() as $request) {
    if (preg_match_all('#<h3 class="r"><a href="([^"]+)">(.*)</a></h3>#iU', $request->getResponseText(), $out)) {
        foreach ($out[1] as $idx => $url) {
            parse_str(parse_url($url, PHP_URL_QUERY), $params);
            $results[$params['q']] = strip_tags($out[2][$idx]);
        }
    }
    echo "Processsed (" . $request->getUrl() . ")" . PHP_EOL;
}

echo "All results: " . PHP_EOL;
print_r($results);