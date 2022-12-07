# RollingCurl

A cURL library to fetch a large number of resources while maintaining a consistent number of simultaneous connections

Authors:
 - Jeff Minard (jrm.cc)
 - Josh Fraser (joshfraser.com)
 - Alexander Makarov (rmcreative.ru)

## Overview

RollingCurl is a more efficient implementation of curl_multi().

curl_multi is a great way to process multiple HTTP requests in parallel in PHP but suffers from a few faults:

 1. The documentation for curl_multi is very obtuse and, as such, is easy to incorrectly or poorly implement
 2. Most curl_multi examples queue up all requests and execute them all at once

The second point is the most important one for two reasons:

 1. If you have to wait on every single request to complete, your program is "blocked" by the longest running request.
 2. More importantly, when you run a large number of cURL requests simultaneously you are, essentially, running a DOS attack. If you have to fetch hundreds or even thousands of URLs you're very likely to be blocked by automatic DOS systems. At best, you're not being a very respectful citizen of the internet.

RollingCurl deals with both issues by maintaining a maximum number of simultaneous requests and "rolling" new requests into the queue as existing requests complete. When requests complete, and while other requests are still running, RollingCurl can run an anonymous function to process the fetched result. (You have the option to skip the function and instead process all requests once they are done, should you prefer.)

## Installation (via composer)

[Get composer](http://getcomposer.org/doc/00-intro.md) and add this in your requires section of the composer.json:

```
{
    "require": {
        "chuyskywalker/rolling-curl": "*"
    }
}
```

and then

```
composer install
```

## Usage

### Basic Example

```php
$rollingCurl = new \RollingCurl\RollingCurl();
$rollingCurl
    ->get('http://yahoo.com')
    ->get('http://google.com')
    ->get('http://hotmail.com')
    ->get('http://msn.com')
    ->get('http://reddit.com')
    ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) {
        // parsing html with regex is evil (http://bit.ly/3x9sQX), but this is just a demo
        if (preg_match("#<title>(.*)</title>#i", $request->getResponseText(), $out)) {
            $title = $out[1];
        }
        else {
            $title = '[No Title Tag Found]';
        }
        echo "Fetch complete for (" . $request->getUrl() . ") $title " . PHP_EOL;
    })
    ->setSimultaneousLimit(3)
    ->execute();
```

### Fetch A Very Large Number Of Pages

Let's scrape google for the first 500 links & titles for "curl"

```php
$rollingCurl = new \RollingCurl\RollingCurl();
for ($i = 0; $i <= 500; $i+=10) {
    // https://www.google.com/search?q=curl&start=10
    $rollingCurl->get('https://www.google.com/search?q=curl&start=' . $i);
}

$results = array();

$start = microtime(true);
echo "Fetching..." . PHP_EOL;
$rollingCurl
    ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use (&$results) {
        if (preg_match_all('#<h3 class="r"><a href="([^"]+)">(.*)</a></h3>#iU', $request->getResponseText(), $out)) {
            foreach ($out[1] as $idx => $url) {
                parse_str(parse_url($url, PHP_URL_QUERY), $params);
                $results[$params['q']] = strip_tags($out[2][$idx]);
            }
        }

        // Clear list of completed requests and prune pending request queue to avoid memory growth
        $rollingCurl->clearCompleted();
        $rollingCurl->prunePendingRequestQueue();

        echo "Fetch complete for (" . $request->getUrl() . ")" . PHP_EOL;
    })
    ->setSimultaneousLimit(10)
    ->execute();
;
echo "...done in " . (microtime(true) - $start) . PHP_EOL;

echo "All results: " . PHP_EOL;
print_r($results);
```

### Setting custom curl options

For *every* request

```php
$rollingCurl = new \RollingCurl\RollingCurl();
$rollingCurl
    // setOptions will overwrite all the default options.
    // addOptions is probably a better choice
    ->setOptions(array(
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => true
    ))
    ->get('http://yahoo.com')
    ->get('http://google.com')
    ->get('http://hotmail.com')
    ->get('http://msn.com')
    ->get('http://reddit.com')
    ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) {
        echo "Fetch complete for (" . $request->getUrl() . ")" . PHP_EOL;
    })
    ->setSimultaneousLimit(3)
    ->execute();
```

For *a single* request:

```php
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
    $rollingCurl->add(
        $request->addOptions($options)
    );
}

$rollingCurl->execute();
```

More examples can be found in the examples/ directory.

## TODO:

 - PHPUnit test
 - Ensure PSR spec compatibility
 - Fix TODOs
 - Better validation on setters

Feel free to fork and pull request to help out with the above. :D

## Similar Projects

 - http://code.google.com/p/multirequest/
