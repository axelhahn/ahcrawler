## Configuration

You find the config in the `./config/` directory. There is a json file `crawler.config.json` with several sections.
By default the project is shipped with an example file you can use for adaptions. 

### Section "options"

setup of default options ... not specific to a website profile

Variable | type | Comments
---|---|---
**analysis** | key 	| Values for analysis
... -> MinDescriptionLength  | integer | html check - Minimum count of chars in the description
... -> MinKeywordsLength 	 | integer | html check - Minimum count of chars in the keywords<br>Remark: the importance for SEO optimization is very low. Google does not process any keyword for their search index.<br>If you use the ahCrawler search hits in keywords get a higher ranking than those in the content.<br>If you do not use the search engine then you can set the value to 0 (zero) to disable the check for keywords.
... -> MinTitleLength        | integer  | html check - Minimum count of chars in the document title
... -> MaxPagesize 	         | integer  | html check - Limit to show as large page (byte)
... -> MaxLoadtime           | integer  | html check - Limit to show as long loading page (ms)
**auth**                     | key      | authentication for the backend; you can setup a single user only.<br>You can disable this (quite simple) internal authentication with removing (or renaming) this section.<br>If you need several users: disable this section and setup an authentication with apache users based on directory or location.
... -> user                  | string 	| username
... -> password              | string   | hash of wanted password using *password_hash()*
**crawler**                  | key      | defaults for crawling
... -> memoryLimit           | string   | Memory size for CLI only; the value is a valid memory size for ini_set('memory_limit', [value]); the default is 512M
... -> searchindex           | key      | defaults for search index crawler
...... -> simultanousRequests| integer  | default count of simultanous requests for crawling pages for all projects you setup.<br>The crawling for the search index makes http GET requests (it loads the content).<br>The minimum number is 2.<br>Hint: Do not overload your own servers. Speed is really not important for a cronjob.
... -> ressources            | key      | defaults for resources crawler
...... -> simultanousRequests| integer  | default count of simultanous requests for resources scan for all projects you setup.<br>The crawling for the search index makes http HEAD requests (it does NOT load the content).<br>The minimum number is 2.<br>Hint: Do not overload your own servers. Speed is really not important for a cronjob.<br>And: you can setup HEAD reqests with a much higher value than GET requests. Try 5 ... and then higher values.
... -> timeout                | integer | timout value for all crawling requests (GET and HEAD) in sec.
... -> userAgent              | string  | User agent to use for crawling. Background: a few webservers send an http error code by detecting a crawler. If you set a user agent of a "normal" webbrowser then the chance is higher to get a valid response.<br>Hint: In the web gui go to the setup to use the user agent of your currently used browser.
**database**                  | key     | define database connection
... -> database_type          | string  | type of the PDO database connection; so far only sqlite and mysql are supported.<br>For first tests use "sqlite" - it OK for websites with a few hundred pages and is more simple to setup.<br>one of "sqlite" | "mysql"
... -> database_file          | string  | sqlite only: name of the database file.<br>default is "\_\_DIR\_\_/data/ahcrawl.db" (where \_\_DIR\_\_ will be replaced with application root directory)
... -> database_name          | string  | non-sqlite only: name of the database scheme
... -> server                 | string  | non-sqlite only: name of database host
... -> username               | string  | non-sqlite only: name of database user
... -> password               | string  | non-sqlite only: password of database user
... -> charset                | string  | non-sqlite only: cahrset; example: "utf-8"
**cache**                     | boolean | Use cache in backend pages to speed up pages. The caches expires with a new craling process for the viewed website profile.<br>
This feature is work in progress and is disabled by default (false).
**debug**                     | boolean | show debug infos in the backend pages.
**lang**                      | string  | language of the backend interface;<br>one of "de" | "en"
**skin**                      | string  | Name of the current skin. It is a directory name in ./backend/skins/.
**menu**                      | array   | hide menu items in the backend<br>- key is the name of the page (have look to the url in the address bar ?page=[name])<br>- value is one of true\|false
**menu-public**               | array   | hide menu items in the public frontent<br>- key is the name of the page (have look to the url in the address bar ?page=[name])<br>- value is one of true\|false
**searchindex**               | array   | key
... -> regexToRemove          | array   | list of regex to remove from html body for the search index; by default it contains<br>- html comments<br>- script and style sections<br>- link rel<br>- nav tags<br>- footer tags
... -> rankingWeights         | array   | Define factors to weight the search results with the searchform in your website. A direct match of the searchterm with a found word should be higher than a match in the middle of a longer word.<br><br>The sections are:<br>- matchWord - Exact hit of a whole word<br>- WordStart - Hit at the beginning of a word<br>- any - Hit anywhere in the text<br><br>In each of these section are places where the search term is scanned. A hit in the url i.e. should have a higher weight than in the content.<br>- content ... in the content<br>- description... in the meta description<br>- keywords ... in the keywords<br>- title ... in the title tag<br>- url ... in the url

```json
{
    "options":{
        "database":{
            "database_type": "sqlite",
            "database_file": "__DIR__/data/ahcrawl.db"
        },
        "auth": {
            "user": "admin",
            "password": "put-md5-hash-here",
        },
        "lang": "en",
        "crawler": {
            "memoryLimit": false,
            "userAgent": false,
            "searchindex":{
                "simultanousRequests": 2
            },
            "ressources":{
                "simultanousRequests": 2
            }	
        },
        "searchindex": {
            "regexToRemove": [
                "<footer[^>]>.*?<\/footer>",
                "<nav[^>]>.*?<\/nav>",
                "<script[^>]*>.*?<\/script>",
                "<style[^>]*>.*?<\/style>"
            ]
        },
        "analysis": {
            "MinTitleLength": 20,
            "MinDescriptionLength": 40,
            "MinKeywordsLength": 10,
            "MaxPagesize": 150000,
            "MaxLoadtime": 500
        }
    },
    (...)
}
```

### Section "profiles"

Setup of websites to crawl. The first index below is an integer value that is called profile id.
The table below describes all values of a profile id.

```json
{
    "options":{
	(... see above ...)
	},
    "profiles":{
		"[id]":{
			(... profile settings ...)
		}
	}
}
```

Variable           | type    | Comments
---                |---      |---
label              | string  |  A short name for the website. It is shown inside the admin as tab label on the top.
description        | string  | description text for this website
userpwd            | string  | optional setting for password protected websites with basic authentication<br>The syntax is<br>[username]:[password]
**searchindex**    | key     | definitions for the crawler of the search index
... -> urls2crawl  | array   | Start urls for scan
... -> iDepth      | integer | Maximum path level to scan
... -> iMaxUrls    | integer | For initial tests: set max. count of urls to scan (0 = no limit)
... -> include     | array   | Array with regex that will be applied on any detected full url in a link.<br>The crawler adds an url if it matches one of the regex.<br>Default: none; any url (matching the sticky url) will be followed.
... -> includepath | array   | Array with regex that will be applied on any url path of a detected link.<br>The crawler adds an url if it matches one of the regex.<br>Default: none; any url (matching the sticky url) will be followed.
... -> exclude     | array   | Array with regex that will be applied on any url path of a detected link.<br>The crawler skips an url if it matches one of the regex.<br>Default: none; any url (matching the sticky url) will be followed.<br>Remark: Even if it is empty ... the crawler follows several disallow options by default:<br>- disallow for agent "*" in robots.txt<br>- disallow for agent "ahcrawler" in robots.txt<br>- meta robots no index and nofollow in html head<br>- attribute nofollow in a link
... -> regexToRemove | array | list of regex to remove from html body for the search index; it overrides the default in the options section: options -> searchindex -> regexToRemove.
... -> simultanousRequests | integer | count of simultanous requests; it overrides the default in the options section: options -> crawler -> searchindex -> simultanousRequests.
**frontend**         | key    | definitions for the frontend (search form for your website)
... -> searchcategories | array | items for search categories based on url path<br>Syntax:<br>[key] - label of the filter<br>[value] - WHERE value for sql statement<br>i.e. "... my blog": "/blog/%"
... -> searchlang    | array  | items for language select box in the search form<br>i.e. ["de", "en"]
**resources**        | key    | definitions for the crawler of the search index
... -> simultanousRequests | integer | count of simultanous requests; it overrides the default in the options section: options -> crawler -> ressources -> simultanousRequests
