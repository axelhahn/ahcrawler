## page: Setup

Here are settings for the program.

### Backend

**Language in the backend**\
Switch the language by changing it in the dropdown.

**Look/ Skin**\
Switch the layout by selecting an available skin.

**Visibility of menu items**\
In the textarea is a JSON structure with all pages.
The keys are the page names (if you have a look to the urls: they end like /backend/?page=setup). You can disable a page name to hide it in the navigation on the left. You can simplify the interface if you don't need a function or wanna simplify it. But this is just a visual function - a page is still available.

**Show debugging information**\
This option is for development only. It enables the measurement of internal modules.

**Custom html code at the body end**\
You have the option to add custom html code. It will be added before closing body tag. It is useful to add a statistic tracker. You need to enter html code - if you want to add javascript then you need to wrap it into a \<script\> tag.

**Authentication**\
You can protect the access to the backend interface with a single user. It is not a must. you can protect the access by webserver configuration (Basic authentication, IP restriction).
To change the username you need to enter the current password.
To set a new user or password you need to enter the current password. Enter the new one twice.

### Crawler defaults

**search index - simultanous requests Http GET**\
The search index fetches the html content of a website.
Set a value how many parallel requests you want to allow.
The minimum number is 2.
The default is 2.
A higher value gives you more speed to finish a website scan. On the other hand it generates more traffic on your webserver. Do not overload your own servers. Speed is really not important for a cronjob.
This is the default setting for all projects. You also can override this value in each project.

**resources scan - simultanous requests Http HEAD**\
The resources scan makes just http HEAD requests which is lighter, faster because it transfers header information only without content.
Set a value how many parallel requests you want to allow.
The minimum number is 2.
The default is 3.
Do not overload your own servers. Speed is really not important for a cronjob.
This is the default setting for all projects. You also can override this value in each project.

**Timeout in [s]**\
Defines the timout of each http request. This is a global setting for all profiles.

**Memory (memory_limit) for CLI**\
Starting the spider on command line keeps a lit of all crawled urls in memory. If you have a large website or many links you could get an out of memory error. Here you can set a higher value for the spider if needed.

**User Agent of the crawler**\
Some websites block spiders and crawlers. To improve the scan result of external links you can set a user agent that the spider will use while crawling.
As a helper there is a button to place the user agent of your currently used browser there.

**Content to remove from searchindex**\
The search index process extracts text from the content of each html page.
You can remove parts of each page by defining a set of regex. So you can remove words of the navigation items or other unwanted parts.
Write 1 line per regex. These regex are not casesensitive.

```txt
<footer[^>]*>.*?</footer>
<header[^>]*>.*?</header>
<nav[^>]*>.*?</nav>
<script[^>]*>.*?</script>
<style[^>]*>.*?</style>
```

### Search Frontend

Settings for finetuning of search result ranking. They will be used if you build in the search frontend in your website. Depending on place and kind of match exist different multipliers.

### Constants for the analysis

You can set default parameters for the website Analysis -> Html checks. With these values you can set the sensitivity of the checks there.

**Minimum count of chars in the document title**\
Default: 20

**Minimum count of chars in the description**\
Default: 40

**Minimum count of chars in the keywords**\
Default: 10

**Limit to show as large page (byte)**\
Default: 150000

**Limit to show as long loading page (ms)**\
Default: 500

### Public services

Enabled services will be visisble for a public, anonymous visitor without logging in into the backend.
By default all public pages are diabled. It results in a 403 error if the starting page (one level above the backend) will be requested.
If you wish to offer the service for a few web tools then set them to true. Remark: You should enable home too to offer a useful starting page.

### Database

You can override the current database settings. The new data will be verified. If they are wrong then the new settings won't be saved.
