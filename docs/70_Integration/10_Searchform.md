## Simple search form
For a quick winner there is a ready to use method for a quite simple form. Use the method renderSearchForm() to display it.

Here is no customization. To customize the layout see below the chapter Fully customized search form.

```php
require_once("classes/search.class.php");

// ----- (1) init with site id:
$o = new ahsearch();
$o->setSiteId(1);

// or shorter: $o = new ahsearch(1);

// ----- (2) set the frontend language
$o->setLangFrontend('en');

// ----- (3) show form to enter search term
echo $o->renderSearchForm();

// ------ (4) output of results
echo $o->renderSearchresults();
```

The result of a simple search form is a single input field and a submit button.

![Screenshot: Backend - Start](/images/searchform-simple.png)


## Simple search form with options

For (3) you can add an array to show the extended options by setting the flag to 0 or 1

```php
(...)
// ----- (3) show form to enter search term with additional options
echo $o->renderSearchForm(array(
    'categories'=>1,
    'lang'=>1,
    'mode'=>1,
));
(...)
```

The possible keys are

Key | Description
---|---
categories | Show a select box with your categories in your profile profiles -> [siteid] -> categories<br>The item for the search in the complete site will be added automatically.
lang | Show a select box with your document languages profiles -> [siteid] -> searchlang<br>The item for the search in all languages will be added automatically.
mode | Show a select box to choose between AND or OR condition<br> The result with activated options:


![Screenshot: Backend - Start](/images/searchform-with-options.png)

## Fully customized search form

In the methods above is no customaization. If you use frameworks like Bootstrap you want to have the full control for positioning and custom css classes and attributes for all select boxes and other items.

With a fully customized form you need to place the form tag, all labels and input tags and a submit button.
But: you don't need to care about GET or POST parameters for the search terms or options.
To do so you can use public methods of the search class to place all form items where you want and you can change its html attributes i.e. to set a custom css class.


Here an example with Bootstrap:

```php
<?php
require_once("classes/search.class.php");

// ----- (1) init with site id:
$o = new ahsearch(1);

// ----- (2) set the frontend language
$o->setLangFrontend('de');

// ----- (3) show custom form
?>
<hr>
<form method="GET" action="?">
    <?php echo $o->renderHiddenfields(); ?>
    <?php echo $o->lF('label.searchhelp'); ?><br>
    <br>

    <div>
        <div class="col-sm-12">
            <?php echo $o->renderLabelSearch() ?>
            <div class="input-group">
                <?php echo $o->renderInput(array('class'=>'form-control')) ?>
                <span class="input-group-btn">

                    <button class="btn btn-success" type="submit">
                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                        <?php echo $o->lF('btn.search.label'); ?>
                    </button>
                </span>
            </div>
        </div>
    </div>
    <br>
    <br>
    <?php echo $o->lF('label.searchoptions'); ?>:<br>
        <div class="col-sm-4">
            <?php echo $o->renderLabelCategories() . $o->renderSelectCategories(array('class'=>'form-control')) ?>
        </div>
        <div class="col-sm-2">
            <?php echo $o->renderLabelLang() . $o->renderSelectLang(array('class'=>'form-control')) ?>
        </div>
        <div class="col-sm-5">
            <?php echo $o->renderLabelMode() . $o->renderSelectMode(array('class'=>'form-control')) ?>
        </div>
    </div>

</form>
<div style="clear: both; margin-bottom: 2em;"></div>
<?php

// ------ (4) output of results
echo $o->renderSearchresults();
```

* You can use your own form tag. The search works with GET and POST.
* **renderHiddenfields()** - adds hidden fields for project and language
* **lF([id])** - shows a language specific text by a given id.
The letter F is for frontend. Ids are:
  * label.searchhelp - introtext with a short help
  * btn.search.label - text for the search button
  * label.searchoptions - text for extended options
* show label tags for input and search boxes (*)
  * **renderLabelSearch()**
  * **renderLabelCategories()**
  * **renderLabelLang()**
  * **renderLabelMode()**
* **renderInput()** - show input for search term (*)
* show select boxes (*)
  * **renderSelectCategories()**
  * **renderSelectLang()**
  * **renderSelectMode()**
* **renderSearchresults()** - show the search results. There is output only if a search term was given

(*) In all renderLabel... and renderSelect... methods you can add an optionially array as parameter that contains wanted html attributes as key-value pairs. Your custom values override the defaults (or will be added).

If you want to change the for attribute then keep in mind to set the same value as id in the corresponding input tag.

## Customize search results

You can set parameters in method renderSearchresults() to influence the output.
Next to ist PHP doc you see the description for the parameter $aParams.

```
/**
    * do search and render search results
    * @param string  $aParams     search options understanding those keys:
    * 
    *                  q      {string}  search string
    * 
    *                  url    {string}  limit url i.e. //[domain]/[path] - without "%"
    *                  subdir {string}  => subset of search without domain with starting slash (/[path])
    *                  mode   {string}  one of AND | OR | PHRASE (default: AND)
    *                  lang   {string}  force language of the document; default: all
    *                  limit  {integer} limit of max search results; default: 50
    * 
    *                  head   {string}  html template code for header before result list
    *                  result {string}  html template code for each result
    * @return string
    */
renderSearchresults($aParams = array()) {}
```

### head

Default header is
```html
<strong>{{RESULTS}}</strong><br><br>
<p>{{HITS}}</p>
```

Placeholders are:
placeholder | description
---|---
{{RESULTS}} | Headline to start the output of results.
{{HITS}} 	| Show count of hits ... or that no results were found.

### result

Html template code to show a singe result item

```html
<div class="searchresult">
    <div class="bar">
        <span>{{PERCENT}}%</span>
        <div class="bar2" style="width: {{PERCENT}}%"> </div>
    </div>
    <a href="{{URL}}">{{TITLE}}</a> <span class="date">{{AGE}}</span><br>

    <div class="url">{{URL}}</div>
    <div class="detail">{{DETAIL}}</div>
</div>
```

Possible placeholders are:
placeholder 	| description
---             |---
{{AGE}}	        | Age of the last scan in days.
{{DESCRIPTION}}	| Value of meta description tag in the document.
{{DETAIL}}	    | Snippet of hit in the content.
{{KEYWORDS}}	| Value of meta keywords tag in the document.
{{LANG}}	    | Language attribute of html document.
{{PERCENT}}	    | Value for ranking; its value is an integer 0..100.
{{TITLE}}	    | Title of the document.
{{URL}}	        | Url of the result target.

### Style the results

The default is styling is used as log you do not override result (see above).
It is written into th html document.

Recommendation:
You should define the css rules for result output into your general css file. Reason behind: Including css code inside html requires to set "unsafe-inline" value in the CSP security header (if you set it).

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
		$aDefaults=array(
			'siteid'=>1,      // set the project id in ahcrawler instance
			'guilang'=>'en',  // uses texts from lang/frontend.[your-language-code].json
		);


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
		$options  = array('http' => array(
			'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-',
			));
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

## Searches and statistics

If some searches were done in the website, you can see the history of the last searches and search it again to see the results a user user got. Or the Top 10 of different periods.

[Search terms](30_Web_UI_backend/30_Start/30_Search_terms.md)