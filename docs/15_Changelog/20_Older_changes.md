## Changelog archive

### 2025

#### 2025-11-03: v0.183

* 🟢 ADDED: reindex a single url to update it in the search index
* 🟢 ADDED: new page analysis -> domain files (WIP)
* ↗️ UPDATE: context bar with multiple boxes
* ↗️ UPDATE: switch between search index and resource detail page
* ↗️ UPDATE: user profile: move logoff button
* 💣 FIX: http response code of error pages do not force http v1 header
* 💣 FIX: public http header page lost url to scan
* 💣 FIX: remove hardcoded http v1 response code

#### 2025-09-18: v0.182

* 💣 FIX: Damn, the login mix of login form and $_SERVER was trickier than I tought.

#### 2025-09-16: v0.181

* ↗️ UPDATE: optical enhancements, css updates eg click in menu on activated dark theme
* ↗️ UPDATE: Beautify page layout of default skin
* ↗️ UPDATE: changelog icon in about page
* ↗️ UPDATE: Render left navigation before content
* ↗️ UPDATE: Shorter waiting time after saving settings
* 💣 FIX: detect user from Basic auth without acl confg

#### 2025-09-14: v0.180

* ↗️ UPDATE: optical enhancements, css updates eg click in menu on activated dark theme
* 💣 FIX: php warning ini user detection

#### 2025-09-09: v0.179

* 🟢 ADDED: multi user access to projects with given role. This is a huge change you can enable now! Please read the docs how to configure it. 👉 See [Security User restrictions](../60_Security/30_User_restriction.md)
* ↗️ UPDATE: ahCrawler is PHP 8.4 ready
* ↗️ UPDATE: PHP versions below v2 are marked as error because http 1 .1 has security issues
* ↗️ UPDATE: replace fontawesome with tabler icons
* ↗️ UPDATE: default light theme got colors by main section
* ↗️ UPDATE: themes: more colors in navigation bar in default and default dark theme. You can switch to the older look when setting the `[name] - simple` theme.
* ↗️ UPDATE: bookmarklet page
* ↗️ UPDATE: ahcache
* ↗️ UPDATE: medoo 2.1.12 --> 2.2.0
* ↗️ UPDATE: remove warning if no https was found
* ↗️ UPDATE: Docker dev environment
* 💣 FIX: navigation - active menu item doesn't lose color on hover anymore
* 💣 FIX: logoff - don't show navigation after logging off

#### 2025-01-22: v0.178

* ↗️ UPDATE: fix position of context box
* ↗️ UPDATE: profiles page has most relevant settings on top now

#### 2025-01-21: v0.177

* 🟢 ADDED: link to online help in backend pages
* ↗️ UPDATE: http response header metadata
* ↗️ UPDATE: web ui - replace "X-Frame-Options: SAMEORIGIN" with "Content-Security-Policy: frame-ancestors deny"

#### 2025-01-19: v0.176

* 🟢 ADDED: more colors for http header types and icons
* 🟢 ADDED: show count of found experimental http reponse headers on start page
* 🟢 ADDED: counter for experimantal http response headers
* ↗️ UPDATE: (doubled) http response header was removed in curl meta infos
* ↗️ UPDATE: remove a column in http response header table
* ↗️ UPDATE: css for dark mode
* 💣 FIX: typos in http header metadata
* 💣 FIX: filter buttons of http header in resource detail page
* 💣 FIX: Remove project list from public ssl check page

#### 2025-01-16: v0.175

* 🟢 ADDED: http headeranalysis got a filter bar
* ↗️ UPDATE: http header meta data
* ↗️ UPDATE: analyzerHtml class - fix type in getHttpResponseHeader()
* ↗️ UPDATE: http headers - remove double tag values

### 2024

#### 2025-01-11: v0.174

* ↗️ UPDATE: Ignore linkcheck for link tags with rel *dns-prefetch* (before it often resulted in 404 errors)

#### 2025-01-04: v0.173

* 🟢 ADDED: select2 component for searchable dropdowns
* ↗️ UPDATE: Analyze -> Counter: change order - show latest values on top
* ↗️ UPDATE: fontAwesome 6.6.0 -> 6.7.2
* ↗️ UPDATE: Docker dev environment changed to PHP 8.4 for testing: Medoo requires 8.3

#### 2024-10-27: v0.172

* 💣 FIX: flip between search index detail and resource item (and back)
* 🟢 ADDED: show network times to prepare, process and sending response in the detail pages of a search index item and a resource item.

#### 2024-10-03: v0.171

* 💣 FIX: resource scan created duplicates of pages with same url as type "links"
* 🟢 ADDED: hide skip messages during crawler runs. This shortens the log output by default. It can be enabled in the global options.
* 🟢 ADDED: profile - more bottons to toggle extended options (like in global settings)
* 🟢 ADDED: profile - after successful save it shows the options aftrer 3 sec (like in global settings)
* 🟢 ADDED: on no response the curl error is shown in the log

#### 2024-10-02: v0.170

* 💣 FIX: Installer did not work anymore (PHP error message)
* 💣 FIX: Profile page - handle missing php-gd; show button new project]
* ↗️ UPDATE: Profile page - move input max count of webpages to crawl into non extended view

#### 2024-10-01: v0.169

* ↗️ UPDATE: add profiles navigation in pages that need it
* ↗️ UPDATE: in page home: added hints per section for found errors and warnings
* ↗️ UPDATE: in page link checker the urls are linked to the search index now
* ↗️ UPDATE: in page setup: show menu labels to enable visible items (before: keys have been shown), more buttons for toggling extended view
* ↗️ UPDATE: season skins and default skin

#### 2024-09-20: v0.168

* 💣 FIX: json errors in vietnamese backend translation
* 🟢 ADDED: 4 skins for a demo for light skins
* ↗️ UPDATE: reload after 2 sec if saving of settings was OK
* ↗️ UPDATE: chartjs -> 4.4.1
* ↗️ UPDATE: jquery -> 3.7.1
* ↗️ UPDATE: font-awesome -> 6.6.0
* ↗️ UPDATE: Medoo -> 2.1.12
* ↗️ UPDATE: Show additional text after update: reload browser, link to changelog

#### 2024-09-16: v0.167

* 🟢 ADDED: Vietnamese translation was contributed by [[https://github.com/saosangmo|saosangmo]]. Thanks a lot! He was added as contributor.
* ↗️ UPDATE: AhCrawler runs on PHP 8+ only: All classes were updated to use typed variables. Arrays were rewritten to short array syntax
* ↗️ UPDATE: My own external classes were moved to vendor subdir
* ↗️ UPDATE: The updater detects a developer branch and shows a warning
* ↗️ UPDATE: main.css and default skin were updated to simplify creation light skins (coming in next release)
* ↗️ UPDATE: missing translated items will show the english version added by "(en)"

### 2023

#### 2023-12-03: v0.166

* ↗️ UPDATE: AhCrawler runs on PHP 8.3
* ↗️ UPDATE: dark skin - login page is not white anymore
* ↗️ UPDATE: German texts for counters

#### 2023-10-29: v0.165

* 🟢 ADDED: viewer page for all counters
* 🟢 ADDED: LICENSE file (licence didn't change - but now licence text is in project root too)
* ↗️ UPDATE: Medoo to v 2.1.10 (AcCrawler is compatible with PHP 8.2 with it)
* ↗️ UPDATE: dark skin
* ↗️ UPDATE: docker dev environment

#### 2023-08-03: v0.164

* 🟢 ADDED: on failed connections (http status code 0 (zero)) the curl error is shown.
* ↗️ UPDATE: add DOCKER_USER_UID in docker env

#### 2023-07-06: v0.163

* ↗️ UPDATE: ahCache class
* ↗️ UPDATE: ah web updater classes
* ↗️ UPDATE: html analyzer class

#### 2023-05-09: v0.162

* 💣 FIX: error with missing vendor cache dir
* ↗️ UPDATE: cdnorlocal --> 1.0.13
* 🟢 ADDED: metadata of needed libs

#### 2023-05-09: v0.161

* 💣 FIX: cdnorlocal because API response of Cdnjs was changed
* 💣 FIX:  left menubar is scrollable
* ↗️ UPDATE: pure 2.1.0 --> 3.0.0
* ↗️ UPDATE: jQuery 3.6.1 --> 3.6.4
* ↗️ UPDATE: font-awesome 5.15.4 --> 6.4.0

#### 2023-03-09: v0.160

* 💣 FIX: counters were set in a wrong way. Graphs of values in the last N days were wrong
  You can wait until currently wrong counter values are out of scope after 90d or you can execute `truncate counteritems` and `truncate counters` on the database to delete them.
* ↗️ UPDATE: css update of default theme
* ↗️ UPDATE: fix php warnings in some classes

#### 2023-03-05: v0.159

* 🟢 ADDED: switch button between search index and resource detail page of the same url
* 🟢 ADDED: breadcrumb navigation 
* ↗️ UPDATE: simplify resource detail page
* ↗️ UPDATE: mark all unsecure cookies
* ↗️ UPDATE: css update of default theme

#### 2023-02-21: v0.158

* 💣 FIX: web updater and updater cronjob did not detect a git instance after directory change in v0.156

#### 2023-01-06: v0.157

* 🟢 ADDED: support for multiple values of a column (OR) ... linkchecker shows button for all http status codes of a section
* ↗️ UPDATE: ahlogger - logger with enabled debug in the backend is compatible to PHP 8.2
* ↗️ UPDATE: update Medoo (database lib) to v2.1.7
* ↗️ UPDATE: update resource scan starts with head requests (it uses less resources)
* ↗️ UPDATE: local docker environment (internal stuff for development)
* ↗️ UPDATE: css - clickable tile with soft shadow animation
* ↗️ UPDATE: statusbar during index got a progress bar during indexing resources
* ↗️ UPDATE: fix deprecated warning on empty strings in preg_match() or str_replace()


### 2022

#### 2022-12-12: v0.156

**Important note:**

This is a large update!

The folder structure was changed: The files of the software and web ui were moved to "public_html" subfolder. This update will break installations that were initialized with a `git pull`.

If you installed the software with git then you need to change the webserver config.

The reason is: I added my local dev environment (rootless docker) and rewrote the current help with markdown files and added it too.

Finally there were changes in the code to improve the search and to unify backend layout elements.

* 🟢 ADDED: docker development environment 
* 🟢 ADDED: docs folder with markdown help
* 🟢 ADDED: Textareas with placeholders: on double click the default value is editable
* ↗️ UPDATE: **software was moved to public_html subfolder**
* ↗️ UPDATE: search index - hide newest and oldest data it delta is below 1d
* ↗️ UPDATE: unify display: search index url is linked to details; showing url has same button like in resources
* ↗️ UPDATE: search index - detail page contains http response header
* ↗️ UPDATE: search index - word list on detail page is a toggled content element now
* ↗️ UPDATE: search result - contains html elememts for preview with marks
* ↗️ UPDATE: search result - full content data were removed: added a preview snippet
* ↗️ UPDATE: show clear message if a cookie file exists but is not readable (no permissions)
* ↗️ UPDATE: public search 
  * highlight searchterms in title, url, description, keywords, preview ...
  * added variable for hits per term or which term was not found
  * added meta information including timers and request data
  * added {{TOTALTIME}} (time in ms for search) and {{HITCOUNT}} (number of search results) in head template
  * added {{COUNTER}} in search result template for number of search result item
  *  output template: added html placeholders to show data with and without marked searchterm hits
    eg. {{TITLE}} and {{HTML_TITLE}}
  * placeholders from head can be used in search result template too
* ↗️ UPDATE: internal search - show times to prepare, database search, sorting results and total time

#### 2022-10-23: v0.155

* 💣 FIX: php error in setup on missing defaultUrls
* ↗️ UPDATE: deselect OK status buttons on linked resources only
* ↗️ UPDATE: backend search additionally can search in html response

#### 2022-10-18: v0.154

* 💣 FIX: http header of a failed page in detail page
* ↗️ UPDATE: css of default theme: move all colors into variables to simplify custom skins
* ↗️ UPDATE: link details show switch from secure https to unsecure http
* ↗️ UPDATE: resource details disable http ok links

#### 2022-09-06: v0.153

* 💣 FIX: add support of git repo outside approot
* 💣 FIX: php error on if a project was not crawled
* 💣 FIX: relative redirect urls
* ↗️ UPDATE: use session_write_close
* ↗️ UPDATE: skips by extension
* ↗️ UPDATE: reduce memory usage while crawling
* ↗️ UPDATE: log viewer shows filtered view as default
* ↗️ UPDATE: jquery 3.6.0 --> jquery 3.6.1
* ↗️ UPDATE: pure 2.0.6 --> pure 2.1.0
* ↗️ UPDATE: chartjs 3.6.0 --> chartjs 3.9.1

#### 2022-03-17: v0.152

* 💣 FIX: repeat search on page search terms - top N
* 💣 FIX: do not abort if creation of database index failes
* 🟢 ADDED: update detects a git instance and starts a git pull or download+unzip

#### 2022-03-07: v0.151

* 💣 FIX: switch back to language en within content
* ↗️ UPDATE: dark theme (work in progress)
* ↗️ UPDATE: about page shows PHP version and modules
* ↗️ UPDATE: PHP8.1 compatibility

## Before 2022

### 2021

2021-11-15: v0.150

* 💣 FIX: install from scratch was broken in 0.149
* 🟢 ADDED: set skin in the settings
* 🟢 ADDED: dark theme (work in progress)

2021-10-26: v0.149

* 💣 FIX: warning if missing iSiteid in counter.class.php
* 💣 FIX: follow 30x redirects in link checker
* ↗️ UPDATE: upgrade medoo from version 1.x to 2.1.3 ... which requires PHP 7.3 now
* ↗️ UPDATE: charts backgound is more simple
* 🟢 ADDED: counters for http header checks
* 🟢 ADDED: stacked view for errors and warnings on backend home page
* 🟢 ADDED: slight animation for charts
* 🟢 ADDED: many historical views of warnings/ errors/ values on several views
         (it takes a few days to appear)
* 🟢 ADDED: caching for backend pages
* ↗️ UPDATE: y scale in charts
* ↗️ UPDATE: upgrade chart js -> 3.6.0
* 🟢 ADDED: log error message on missing php interpreter as cli tool.

2021-09-14: v0.148

* 🟢 ADDED: store counter values
* ↗️ UPDATE: upgrade chart js from v2 to v3 (using 3.5.1)
* ↗️ UPDATE: font-awesome 5.15.4
* ↗️ UPDATE: pure 2.0.6
* 💣 FIX: remove encoding br (Brotli) in http request headers
* 💣 FIX: comparison with canonical links
* PATCH: get redirect url from raw http response header if missed in curl data

2021-05-04: v0.147

* 🟢 ADDED: ignore noindex tagging
* 🟢 ADDED: ignore nofollow tagging (can be dangerous)
* 💣 FIX: Php 8 compatibility in get.php (removes warnings in statusbar)
* 💣 FIX: visibilty of menu item Start -> Crawler log
* ↗️ UPDATE: home page has links to crawler start urls (before: text only)
* ↗️ UPDATE: Css rules (preparing skin support in next versions)

2021-04-25: v0.146

* 🟢 ADDED: settings got entry for custom html code (i.e. to add statistic tracking)

2021-04-24: v0.145

* 🟢 ADDED: reindex function on starting page "home"
* 🟢 ADDED: detection if only one resource was crawled
* 💣 FIXED: homepage and display items in different constellations
* 💣 FIXED: settings changed username without giving current password
* ↗️ UPDATE: move check of http version in http header check
* ↗️ UPDATE: css of contextbox

2021-04-14: v0.144

* 🟢 ADDED: chart of load time over all pages on start page
* 🟢 ADDED: http header check for http version. If below http version 2 you get a warning

2021-04-10: v0.143

* 💣 FIX: usage of local vendor libs
* 💣 FIX: public service pages did not work with a set internal auth user

2021-03-19: v0.142

* 🟢 ADDED: software updaterr script; see php cronscripts/updater.php -h
* 🟢 ADDED: updater verifies a md5 checksum of download file
* ↗️ UPDATE: setup page in backend shows checkboxes to activate menu items
* ↗️ UPDATE lib: pure -> 2.0.5
* ↗️ UPDATE lib: font-awesome -> 5.14.0
* ↗️ UPDATE lib: jquery -> 3.6.0
* ↗️UPDATE: typos in english texts; UPDATE texts for setup

2021-01-08: v0.141

* ↗️ UPDATE: cronscript supports updates and single profiles

### 2020

2020-12-30: v0.140

* ↗️ UPDATE: crawling processes
* ↗️ UPDATE: cli action " UPDATE" uses GET requets to handle errors caused by denying http head requests
* 💣 FIX: remove a var_dump output in crawling process
* 💣 FIX: remove context box in about page

2020-12-28: v0.139

* ↗️ UPDATE: show done urls in percent
* 💣 FIX: writing crawling logs is enabled again
* 💣 FIX: crawling ressources (http HEAD) runs with PHP 8 (no core dump anymore)

2020-12-05: v0.138

* 💣 FIX: deny list was not applied on 3xx redirects
* 💣 FIX: update code for PHP8 compatibility (work in progress)
* ↗️ UPDATE: Css colors
* ↗️ UPDATE lib: Chart.js 2.9.3 -> 2.9.4
* ↗️ UPDATE lib: datatables 1.10.20 -> 1.10.21
* ↗️ UPDATE lib: font-awesome 5.13.0 -> 5.15.1

2020-10-04: v0.137

* 🟢 ADDED: html analyzer - scan for AUDIO and VIDEO sources
* 🟢 ADDED: html analyzer - add line number in the source code of found items
* 💣 FIX: html analyzer - handle urls starting with ? in html content

2020-09-30: v0.136

* 🟢 ADDED: crawlerlog got a paging navi
* 🟢 ADDED: crawler follows canonical urls
* 🟢 ADDED: show contributors in about page
* 🟢 ADDED: pull request of Ozhiganov (Russian language files)

2020-09-23: v0.135

* 🟢 ADDED: log cli output of crawling actions in ./data/
* 🟢 ADDED: page to view log data of crawling actions
* 💣 FIX: ressource scan shows matching regex of the deny list
* 💣 FIX: profile page layout error
* ↗️ UPDATE: show hint if a url matches a regex in the deny list
* ↗️ UPDATE: show hint if a url switches from https to http

2020-09-11: v0.134

* 🟢 ADDED: sslcheck: show certificate chain check
* ↗️ UPDATE: rename "ressource" to resource in output. IMPORTANT: cli parameter -d is included too. --> Check your cronjobs!
* ↗️ UPDATE: profile: file upload got an accept attribute for images files
* ↗️ UPDATE: search.class: use param "guilang" for frontend language and "lang" for language in search --> Check integrations/*.php
* ↗️ UPDATE: search.class: customize search result output
* ↗️ UPDATE: remove unneeded functions

2020-09-05: v0.133

* 🟢 ADDED: profile image - has a delete button and file upload too now
* 💣 FIX: index ressources with more pages with sqlite engine
* 💣 FIX: searchindex indexer could have false positives in extension detection
* 💣 FIX: cli calls "-a reindex" or "-a index -d all" with sqlite engine locked the database for ressources
* ↗️ UPDATE: cli - show hint if using "-d all"
* ↗️ UPDATE: searchindex indexer got a few more extensions
* ↗️ UPDATE: profile image uses jpeg insted of png (uses less space)
* ↗️ UPDATE: wording changed: blacklist into deny list

2020-08-30: v0.132

* 🟢 ADDED: show warnings for deprecated http headers
* ↗️ UPDATE: forms in profiles and settings
* ↗️ UPDATE: use password_hash() instead of md5() for login. If you used the 
          build in user ... In config/crawler.config.json ...
          remove the entry options -> auth -> user.
          Then go to the settings in the backend to set the user and password again.
          OR
          Get a new password hash by
          > php -r "echo password_hash('mypassword', PASSWORD_DEFAULT);"
          and enter the output into options -> auth -> password
* 💣 FIX: navigating from "add profile" page to any other page

2020-08-19: v0.131

* 🟢 ADDED: you can set one image (i.e. screenshot) per profile
* 💣 FIX: htmlchecks - sortorder of tables for large and long loading pages

2020-08-15: v0.130

* 💣 FIX: redirect to installer
* ↗️ UPDATE: project selection is now a drop down instead of tabbed menu
* ↗️ UPDATE: move datatable initialisation into file to remove inline scripts
* ↗️ UPDATE: searchterm statistics: buttons were switched to gray
* ↗️ UPDATE: searchindex detail page (work in progress)
* ↗️ UPDATE: crawler with basic auth (if enabled) is used for search index only
* 💣 FIX: searchterm statistics ignored the newest search term
* INTERNAL: replace message box $oRenderer->renderMessagebox()

2020-08-11: v0.129

* ↗️ UPDATE: settings - searchindex: do not remove header tag as default option
* ↗️ UPDATE: Show hint of low importance for SEO
* ↗️ UPDATE: setup keyword length: allow 0 to "disable" keyword check

2020-07-26: v0.128

* 💣 FIX: ssl check self signed with org metadata was detected as Business SSL
* 💣 FIX: ssl check higlghts warning or error

2020-07-19: v0.127

* ↗️ UPDATE: speed up crawler/ indexer
* ↗️ UPDATE: ssl check colored table for certificate types
* ↗️ UPDATE: legends can be toggled now. status of toggled elements will be saved in localstorage
* ↗️ UPDATE: http header is shown i backend if no ressources were crawled yet

2020-07-07: v0.126

* 💣 FIX: ssl check Business SSL was detected as EV sometimes

2020-07-05: v0.125

* 💣 FIX: detail page links to http header check with base64 encoding
* 💣 FIX: remove logout button on public page
* 💣 FIX: ssl check can handle wildcard dns entries
* ↗️ UPDATE: ssl check has more infos about type of certificate
* ↗️ UPDATE: ssl check is ready for public page: enter host + port, error handling
* 🟢 ADDED: bookmarklet for ssl check

2020-06-30: v0.124

* 💣 FIX: http header: fix redirect and create urlbase64
* 💣 FIX: http header: fix redirect with relative url
* ↗️ UPDATE: detail page shows bookmarklet
* ↗️ UPDATE: profile and settings: mark hidden (extended) ranges

2020-06-29: v0.123

* 💣 FIX: harden against XSS attacks - IMPORTANT UPDATE

2020-06-28: v0.122

* 💣 FIX: missing language text
* 🟢 ADDED: favicon
* 🟢 ADDED: bookmarklet for http header check
* 🟢 ADDED: language select in frontend
* ↗️ UPDATE: http header check now uses base64 encoded url as param

2020-06-21: v0.121

* ↗️ UPDATE: header "Public-Key-Pins" is marked as deprecated
* ↗️ UPDATE: header "X-Frame-Options" marks ALLOW-FROM as warning
* ↗️ UPDATE: css - boxes in overview pages
* ↗️ UPDATE: header in ressource details links to live http header check (public page must be enabled)

2020-06-15: v0.120

* 🟢 ADDED: context boxes for more information/ links
* 🟢 ADDED: links in the context box in ssl check and http security header
* 🟢 ADDED: extended view in profiles and settings
* ↗️ UPDATE: added about in public area
* ↗️ UPDATE: lang texts

2020-06-12: v0.119

* ↗️ UPDATE: ssl infos: detect self signed cert
* ↗️ UPDATE: public http header check makes redirects comfortable to follow

2020-06-10: v0.118

* 💣 FIX: handle public pages outside the backend

2020-06-09: v0.117

* 🟢 ADDED: handle public pages outside the backend
* ↗️ UPDATE: httpheader class - restructure config data: all known http header variables are handled by tags
* ↗️ UPDATE: http security header fetch the line number
* ↗️ UPDATE: remove inline javascript
* ↗️ UPDATE: request headers for http request were updated
* ↗️ UPDATE: a set cookie was removed (and replaced by a session variable)
* ↗️ UPDATE: jquery to 3.5.1

2020-06-02: v0.116

* ↗️ UPDATE: added more known http headers
* ↗️ UPDATE: added deprecated flags in http headers
* ↗️ UPDATE: ressource details use open / close areas
* ↗️ UPDATE: on http status 0 (no connect) -> try to detect if host exists
* ↗️ UPDATE: change size of http response header columns
    execute "php bin/cli.php -a flush -d all"

2020-05-29: v0.115

* 💣 FIX: error counter increases on failed ressources
* ↗️ UPDATE: add dummy http code 1: hostname does not exist in DNS
* ↗️ UPDATE: show protocol switch in the opposite view of references too.

2020-05-28: v0.114

* ↗️ UPDATE: updater: got 60s timeout to download
* ↗️ UPDATE: updater: got a timestamp parameter if switching to startpage
* ↗️ UPDATE: charts get back a white border

2020-05-27: v0.113

* 💣 FIX: search - detection on word start
* ↗️ UPDATE: show info about running crawler in the footer
* ↗️ UPDATE: more clean locking during crawling and scans
* ↗️ UPDATE: smaller font for titles in overview pages
* ↗️ UPDATE: start page groups messages by check page

2020-05-23: v0.112

* 🟢 ADDED: blacklist - per profile you can add several search texts to ignore links
* 🟢 ADDED: timeout for all http requests
* 🟢 ADDED: home of project shows favicon
* 💣 FIX: label for attributes in the profile settings were not uniq
* ↗️ UPDATE: disabled items got special cursor on hover
* ↗️ UPDATE: search index test: reset [X] was fixed
* ↗️ UPDATE: about does not show (German) project page anymore
* ↗️ UPDATE: Medoo to 1.7.10

2020-05-15: v0.111

* ↗️ UPDATE: reorder menu items: website related pages are all in the upper part
* ↗️ UPDATE: menu items got logic: can be disabled based on available data
* ↗️ UPDATE: installer creates the default config (one manual step less in the initial setup)
* ↗️ UPDATE: profiles are ordered alphabetically (before: by id)

2020-05-13: v0.110

* 🟢 ADDED: identify redirects that switch the protocol from http to https
* ↗️ UPDATE: colors
* ↗️ UPDATE: SSL check got an timeout of 2 sec (1 sec before)
* ↗️ UPDATE: pure to 2.0.3

2020-05-10: v0.109

* 💣 FIX: htmlchecks page: show short tiles/ description/ keywords (it was broken in 1.08)
* ↗️ UPDATE: linkchecker page: links to ressources contain project id now
* ↗️ UPDATE: a tile "100.00%" is shown as "100%"

2020-05-09: v0.108

* ↗️ UPDATE: database table was changed 
    execute "php bin/cli.php -a flush -d searchindex"
    and then index your projects again
* ↗️ UPDATE: show count of words in title, keywords, description
* ↗️ UPDATE: remove PHP Deprecated: mb_strrpos() in analyzer.html.class.php (PHP 7.4)
* 💣 FIX: htmlchecks page: show number of pages (it was broken in 1.06)

2020-05-06: v0.107

* 💣 FIX: htmlchecks - missing tables for large/ long loading pages

2020-05-06: v0.106

* NEW start page showing an project overview with errors and warnings shown 
    in subpages before
* 💣 FIX: ressources page can show empty mime types
* ↗️ UPDATE: pure to 2.0.0
* ↗️ UPDATE: datatables to 1.10.20
* ↗️ UPDATE: font-awesome to 5.13.0
* ↗️ UPDATE: jquery to 3.5.0

2020-04-17: v0.105

* ↗️ UPDATE: resize overview tiles
* ↗️ UPDATE: software update got back buttons
* ↗️ UPDATE: software update in a single step
* ↗️ UPDATE: login form

2020-04-15: v0.104

* ↗️ UPDATE: settings allow to edit ranking multipliers (hartcoded before)
* ↗️ UPDATE: settings got more placeholders
* ↗️ UPDATE: settings page hides current database password with a dummy
* ↗️ UPDATE: showing login fom sends a 401 statuscode (instead of 200)
* ↗️ UPDATE: selected profile tab will be stored for 8 h (instead of 1 h)
* ↗️ UPDATE: ssl check for non https items jump to middle of the page (instead staying on top)

2020-04-13: v0.103

* ↗️ UPDATE: langedit saves changes
* ↗️ UPDATE: colors

2020-02-23: v0.102

* ↗️ UPDATE: fix conditions for PHP 7.4
* ↗️ UPDATE: print css
* ↗️ UPDATE: langedit: add comparison of count of specifiers

2020-01-19: v0.101

* 🟢 ADDED: backend: page for bookmarklet (moved from about page)
* ↗️ UPDATE: page for lang texts
* ↗️ UPDATE: css in overview pages
* ↗️ UPDATE: cli class (allow cgi-fcgi as cli too)
* 💣 FIX: search class - remove limit before calculation of ranking
* 💣 FIX: typo in German lang textfile

2020-01-05: v0.100

* ↗️ UPDATE: search for % char in text
* 🟢 ADDED: backend: page to test search index

2020-01-04: v0.99

* ↗️ UPDATE: font-awesome to 5.11.2
* ↗️ UPDATE: jquery to 3.4.1
* ↗️ UPDATE: Chart.js to 2.9.3
* ↗️ UPDATE: medoo to 1.7.8
* ↗️ UPDATE: ahcache class
* ↗️ UPDATE: cli class
* 💣 FIX: ranking counter in search class: it did not detect a searchterm on text end
* ↗️ UPDATE: improve details for ranking in backend searchindex search
* ↗️ UPDATE: http response headers - added non-standard headers

### 2019

2019-11-10: v0.98

* 🟢 ADDED: frontend search: added renderHiddenfields()
* 🟢 ADDED: frontend search: update to implement a search on another domain
* 🟢 ADDED: frontend search: added search for a phrase

2019-10-12: v0.97

* http header check: added tiles
* http header check: warnings if there is no caching or no compression

2019-10-10: v0.96

* 🟢 ADDED coloring of http response headers
* 🟢 ADDED toggable content elements (see ssl raw data and http response)
* 💣 FIX typo in language files

2019-10-02: v0.95

* 💣 FIX url of font awesome
* resize tiles (for Linux browsers)
* search statistics: added a search button in the top N list to repeat the search
* search statistics: legend for top N list
* ssl check: added certificate type (extended validation of business ssl)
* ssl check: show raw data

2019-10-02: v0.94

* 🟢 ADDED more legend infos

2019-10-02: v0.93

* 🟢 ADDED more legend infos

2019-10-01: v0.92

* html check: always show graphs for lading time and size (not on warning only) 
* http header: show html tags in values
* 💣 FIX height of drop downs on Linux
* 🟢 ADDED more legend infos

2019-09-21: v0.91

* html check: 🟢 ADDED limit and average value in the graph

2019-09-18: v0.90

* html check: 🟢 ADDED graph to show range of load time and sizes

2019-09-18: v0.89

* ssl check: disable check if all hosts of the cert are on the same IP

2019-07-20: v0.88

* cli: add param "reindeox" for easier handling
* cli: show indexed urls per second

2019-07-19: v0.87

* searchindex: 💣 FIX host detection to stay on domain; added password filter

2019-07-17: v0.86

* backend: ↗️ UPDATE components: fontawesome, pure
* backend: ↗️ UPDATE page searches

2019-07-03: v0.85

* remove setting for "stickydomain"

2019-07-02: v0.84

* backend: replace URL param tab with siteid

2019-06-20: v0.83

* backend: linkchecker - tiles on top: show percent values
* backend: htmlchecks - harmonize sections long loading and large pages + flip sortorder

2019-06-03: v0.82

* backend: continued: make visible if a tile is clickable or not

2019-06-02: v0.81

* backend: make visible if a tile is clickable or not
* backend: ressoure detail page got a group based filter on outgoing links
* backend: replace GET param "tab" with "siteid"
* backend: add icons and lang texts in search results 

2019-05-30: v0.80
* backend: link and ressource details: highligt last target

2019-05-30: v0.79

* backend: 💣 FIX output for non ssl items 
* search frontend: 💣 FIX output of search results

2019-05-27: v0.78

* backend: 💣 FIX cli output on Sun Solaris
* crawler: remove sleep on matching exclude rule

2019-05-26: v0.77

* backend: 💣 FIX page header in 404 pages
* backend: colored output of ressource items based on their status

2019-05-25: v0.76

* crawler: 💣 FIX lang detection (on multiple attributes in html tag)
* cli: show better warning on index action if there is no updatable url
* cli: force ouput for cli only
* cli: coloring of texts

2019-05-24: v0.75

* internal: set other user agent in the setup (prevent blocking in the link scan)
* backend: ↗️ UPDATE settings page (user and database don't need to be on top)
* backend: customizable memory limit for cli script

2019-05-19: v0.74

* internal: 🟢 ADDED header for product and licence
* internal: ↗️ UPDATE cdnorlocal for backward compatibility PHP below v7.3
* backend: show search requests with dynamic values and ranges
* backend: html checks show hint that dynamic values can be changed in the settings
* backend: url search (used in the bookmarklet) shows project on siteid=all
* backend: use htmlentities in url labels
* deleted: sws class and references

2019-05-14: v0.73

* backend: 💣 FIX creating/ saving profile
* crawler: 💣 FIX double ressources
* backend: WIP - select range for search statistics 

2019-04-25: v0.72

* backend: 💣 FIX count of local vendor libs; show count of unused libs
* backend: ↗️ UPDATEr: show hint and link to delete unused libs

2019-04-24: v0.71

* backend: ↗️ UPDATE font-awesome/5.8.1, jquery/3.4.0, Chart.js/2.8.0
* backend: 💣 FIX texts in ↗️ UPDATEr
* backend: 💣 FIX bookmarklet url to scan all profiles

2019-04-22: v0.70

* 🟢 ADDED index file in app root
* detect installation state to autorun initial setup
* lang files contain value "id" to specify their own language

2019-04-18: v0.69

* backend: 💣 FIX bookmarklet url (on some systems the url scheme was not set)
* backend: a few optical improvements

2019-04-18: v0.68

* backend: 🟢 ADDED bookmarklet to drag and drop into bookmarks toolbar
    (see analysis -> seach or about page)

2019-04-17: v0.67

* backend: show lang in searchindex
* init: add menu in default config
* 💣 FIXed default for regexToRemove in the searchindex
* backend: remove cookie file creation
* preparations for a single page bookmarklet

2019-04-14: v0.66

* backend: cookies were moved to a seperate page; 🟢 ADDED: delete cookies
* backend: 🟢 ADDED: delete cookies
* backend: 🟢 ADDED: legend and tiles for cookies
* backend: 💣 FIX ressources view - wrongly detected loops
* backend: ressources: filter with less space
* backend: ssl check headline shows count excluding links (instead of all non https ressources)
* backend: 🟢 ADDED function to initialize datatables

2019-04-10: v0.65

* backend: show cookies in a sortable table
* backend: do not delete cookie file on start of indexing
* backend: add page to compare lang texts
* backend: add release date in about page

2019-04-08: v0.64

* backend: searches - 💣 FIX url behind action button
* backend: headers - show cookies

2019-04-07: v0.63

* backend: ↗️ UPDATE test search: use form elements with search class
* backend: ↗️ UPDATE test search: show more ranking details
* search: 💣 FIX counter for word start

2019-04-07: v0.62

* 💣 FIXed: curl accepts cookies
* backend: options to remove content for search index are editable in settings and profiles
* backend: smaller menu items
* backend: highlight menu item during scrolling

2019-03-31: v0.61

* ↗️ UPDATE sslinfo.class + cdorlocal.class
* 🟢 ADDED cdorlocal-admin class
* 🟢 ADDED vendor page
* html checks: 💣 FIX warnings for $iCountNoTitle

2019-03-23: v0.60

* backend: http headers - handle double entries with the same variable name
* backend: 🟢 ADDED plain http headers
* backend: 🟢 ADDED English texts for ↗️ UPDATE wizzard
* crawler: 💣 FIX umlauts in word table

2019-03-23: v0.59

* backend: add tools and list of http statuscodes
* backend: html checks - 🟢 ADDED error tile for no title/ keywords/ description

2019-03-21: v0.58

* backend: home: remove tiles
* backend: 💣 FIX icon for htpp error
* backend: ↗️ UPDATE wizzard has 1 more page and ends on ↗️ UPDATE-home

2019-03-18: v0.57

* backend: upgrade icons of Fontawesome to version 5.x
* backend: 💣 FIX tiles without target url (do not jump on top)
* backend: ssl check - show links only or all not https ressources
* backend: ↗️ UPDATE - 🟢 ADDED a step and set new ↗️ UPDATEr finishing page

2019-03-17: v0.56

* search: 💣 FIX query while performing a search (was changed in Medoo)

2019-03-17: v0.55

* database: add abstracted definition for indexes
* database: remove own table quotes

2019-03-15: v0.54

* backend+frontend: convert html in search values (XSS bug)
* backend: upgrade Medoo to 1.6.1
* backend: upgrade datatables to 1.10.19
* backend: upgrade jQuery to 3.3.1
* backend: upgrade Chart.js to 2.7.3

2019-03-03: v0.53

* searchindex: 💣 FIX charset: utf8 detection before using utf8_decode()
  
2019-03-03: v0.52

* backend: 💣 FIX add profile tab
* crawler: add max count of crawlable urls for testing

2019-02-10: v0.51

* backend: add tiles in linkchecker and ressources (instead of a table)
* backend: translate english lang texts fot human readable time
* backend: 🟢 ADDED list of all urls in the search index
* backend: 🟢 ADDED list of non ssl items on a ssl enabled website

2019-02-08: v0.50

* 💣 FIX: ↗️ UPDATE version file after installation (needs one more ↗️ UPDATE that you see it)

2019-02-04: v0.49

* backend: remove overlays
* backend: sanitizing params (started)
* backend: 🟢 ADDED tiles in search index

2019-01-28: v0.48

* CLI: more information in help output including examples
* backend: 🟢 ADDED empty data (next to flush that deletes data of all profiles)

2019-01-27: v0.47

* backend: method set integer values in the config
* crawler: separated variables for saved config data and effective config
* crawler: confirm deletion of a profile

2019-01-26: v0.46

* backend: settings - menu items to hide were 🟢 ADDED
* backend: settings - limit values for html checks were 🟢 ADDED
* backend: html check page shows limits of the checks
* backend: human readable time (i.e. age of last scan) - 💣 FIX years

2019-01-20: v0.45

* backend: 💣 FIX warnings when starting from scratch

2019-01-20: v0.44

* backend: 🟢 ADDED status for all configured profiles on home 

2019-01-19: v0.43

* backend: 🟢 ADDED page to edit profiles

2019-01-07: v0.42

* backend: 🟢 ADDED gui for program settings (projects/ domains follow soon)
* backend: 🟢 ADDED logging class (todo: add logAdd calls in the frontend)
* backend: remove unneded console.log()
* status: use datadir if system temp dir is no writable (on webhosters)

### 2018

2018-11-02: v0.41

* backend: 🟢 ADDED ↗️ UPDATE checks and web based ↗️ UPDATEr
* ↗️ UPDATE .htacces files with apache httpd 2.4 syntax

2018-10-24: v0.40

* backend: content comes from included php files instead of private functions
* backend: ssl check was moved into its own navigation item
* backend: 💣 FIX warning message on empty ressources

2018-10-14: v0.39

* search: 🟢 ADDED methods for a search form in the frontend: there is a 
    ready-2-use method for a form and there is a fully customizable variant too
* search: 🟢 ADDED search for languages (documents must label their language
    with <html lang="en">)
* search: 🟢 ADDED search with AND or OR condition

2018-10-06: v0.38

* crawler: detection of http2 now is php 5.3 safe
* html analyzer: 💣 FIX in base href detection

2018-10-03: v0.37

* backend: 🟢 ADDED check for ssl certificate

2018-09-29: v0.36

* http analyzer: detect base href
* core: show a warning if no starting url was found in searchindex.urls2crawl

2018-09-11: v0.35

* httpheader: 💣 FIX title tag displaying html code

2018-09-10: v0.34

* cronscript: remove iProfile in flush command
* httpheader: 🟢 ADDED security header Public-Key-Pins,X-Permitted-Cross-Domain-Policies
* httpheader: 🟢 ADDED X-Pingback
* backend: show html code in httpheader data (i.e. link variable)
* crawler: 💣 FIX detection of http2 in current curl installation
* html checks: 🟢 ADDED soft scrolling linksin warning tiles

2018-09-09: v0.33

* backend: add h3 headers in menu including counters
* ↗️ UPDATE colors

2018-09-08: v0.32

* html analyzer: exclude a few link rel combinations
* curl: 🟢 ADDED param to fetch encoding
* curl: use http2 if available
* detect known, common and security variables in http response header

2018-09-03: v0.31

* 🟢 ADDED check for https in http header analysis
* 💣 FIX mixing language in the backend
* 🟢 ADDED language texts for security headers

2018-09-03: v0.30

* 💣 FIX search stats in mysql
* show charts in search stats

2018-09-02: v0.29

* 💣 FIX db column sizes for search and mysql 
* 💣 FIX nofollow

2018-08-29: v0.28

* increase column size for url, content, response
* bug💣 FIX: add site id in ressource ↗️ UPDATEs
* remove each() (it showed a deprecated warning in PHP 7.2)
* smaller boxes in linkchecker section; show percent of the counters

2018-08-29: v0.27

* about page: 🟢 ADDED link to sources and chart.js

2018-08-28: v0.26

* increase size of db column for http header 
* optimize sort order in linkchecker tables
* add response header in ressource infos

2018-08-28: v0.25

* 💣 FIX recursion: do not add ressource pointing to the same source
* 💣 FIX recursion II: detect loop of IN ressources
* ↗️ UPDATE http header check

2018-08-27: v0.24

* 🟢 ADDED check for http header (WIP)
* 🟢 ADDED check for external only hops (not linked urls that redirect to another redirect)

2018-08-06: v0.23

* 🟢 ADDED check for long loading html pages
* 🟢 ADDED check for large html reponse

2018-08-06: v0.22

* 🟢 ADDED support for cookies

2018-08-05: v0.21

* 🟢 ADDED charts in ressources
* 🟢 ADDED: 🟢 ADDED page for html checks
* 🟢 ADDED: set language of html in column pages.lang
* 💣 FIX: English texts on same level like German

2018-08-02: v0.20
