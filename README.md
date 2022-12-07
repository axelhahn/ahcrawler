
# AH CRAWLER #

## Description ## 

This is free software and Open Source 
GNU General Public License (GNU GPL) version 3

AhCrawler is a search engine for your website and analytics tool.

👤 Author: Axel Hahn\
🧾 Source: <https://github.com/axelhahn/ahcrawler/>\
📜 License: GNU GPL 3.0\
📗 Docs: see <https://www.axel-hahn.de/docs/ahcrawler/>

⚠️ **Important notice**:
In version **v0.156** the filestructure was changed. See [Changelog](docs/80_Changelog/10_Last_changes.md)

It is written in PHP and consists of
- crawler (spider) and indexer
- search for your website
- website analyzer with
  - ssl certificate check
  - saved cookies
  - http response header check
  - linkchecker (http status check of all links, css, images, ...)

It runs with PHP 7.3 and higher (up to PHP 8.1).
It uses PDO to store indexed data. So far sqlite and mysql were tested.

This is not a version 1.x yet ... let me do some more work :-)

## Screenshot ## 

![Screenshot: backend](https://www.axel-hahn.de/assets/projects/ahcrawler/03-analyse.png)


## Installation ##
see the docs https://www.axel-hahn.de/docs/ahcrawler/get_started.htm


## Features ##

- Free software and Open Source.
- you can install it on your location. 
  - All data stay under your control. 
  - And you have full control about the age of the checked content. After fixing errors rerun the indexer and immediately get fresh results.
- multi language support (backend and frontend)
- built in web updater

### Spider ###

- respects exclude rules in
  - robots.txt
  - x-robots http header
  - meta robots values noindex, no follow
  - rel=nofollow in links
- additional rules for include and exclude rules with regex
- multiple simultanous requests
- rebuild full index or update a single url (i.e. to be triggered by a cms)
- uses http2 (if possible)

### Search for your website ###

- search with OR or AND
- search in language (requires lang attribute in your html tags)
- search in a given subfolder only
- several methods for pre defined forms or for fully customized form
- stores users searchterms for a statistics

### Website analyzer ###

- check of http reponse header for 
  - unknown headers
  - unwanted headers
  - security headers
- check ssl certificate (if your website uses https)
- show stored server cookies during crawling and following links
- show website errors, warnings based on http status code (a.k.a. linkchecker)
  for all links, images, css, javascripts, media, ... including hints what to do on which status code
- for a given url: display where it is used and where it links to showing
  as cascade on redirects (30x status in repsonse header)
- view over all webpage items (pages, js, css, media) with filter by
  - http status code
  - mime type
  - place (internal item or extern)
- multiple website support within a single installation
