
## Add a search in another domain

If you have a website on one domain and the ahcrawler instance runs on another one then the next snippet might be interesting for you.
Here is a very simple example for a PHP website that requests an url on the ahCrawler instance to show the form and results.
It uses the function file_get_contents for lowest requirements.

See the file integration/searchform.php on your AhCrawler instance - maybe you want to make a copy of it and restyle the form.

Very simple means: There are no protections yet against unwanted requests to the search form and no error handling.

Set **$sCrawlerUrl** to reference the php file with the search form on the AhCrawler instance.

Set values in the array **$aDefaults**.

```html
<!doctype html>
<html>
	<head>
		<title>Search example using integration/searchform.php</title>
		<style>
			body{font-family: verdana,arial;}
		</style>
	</head>
	<body>
		<h1>Search example using integration/searchform.php</h1>
		<?php

		// ----------------------------------------------------------------------
		// CONFIG
		// ----------------------------------------------------------------------
		
		// url to fetch
		$sCrawlerUrl="https://tools.example.com/ahcrawler/integration/searchform.php";
		
		// forced defaults by your own website
		// those cannot be overrided by $_GET params
		$aDefaults=[
			'siteid'=>1,      // set the project id in ahcrawler instance
			'guilang'=>'en',  // uses texts from lang/frontend.[your-language-code].json
		];


		// ----------------------------------------------------------------------
		// prepare URL
		// ----------------------------------------------------------------------
		$sParams='';
		foreach(array_merge($_GET, $aDefaults) as $sVar=>$sVal){
			$sParams.=($sParams ? '&' : '?')
				. $sVar.'='.urlencode($sVal);
		}
		$sCrawlerUrl.=$sParams;


		// ----------------------------------------------------------------------
		// prepare request header
		// ----------------------------------------------------------------------
		$options  = ['http' => [
			'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-',
			]];
		$context  = stream_context_create($options);


		// ----------------------------------------------------------------------
		// output
		// ----------------------------------------------------------------------
		echo '<div style="border: 1px dotted; padding: 1em;">'
				. file_get_contents($sCrawlerUrl, false, $context)
			.'</div>'
		;

		?>

	</body>
</html>
```
