
# AH CRAWLER #

## DESCRIPTION ## 

This is free software and Open Source 
GNU General Public License (GNU GPL) version 3

It is written in PHP and consists of
- crawler (spider) and indexer
- search for your website
- website analyzer with
  - ssl certificate check
  - saved cookies
  - http response header check
  - linkchecker (http status check of all links, css, images, ...)

Runs with PHP 7 and higher (up to PHP 7.3) Maybe it runs on PHP 5.5, but PHP7 is recommended.
It uses PDO to store indexed data. So far sqlite and mysql were tested.

This software has BETA status.
You can preview it ... but let me do some more work :-)

![Screenshot: backend](https://www.axel-hahn.de/assets/projects/ahcrawler/03-analyse.png)


## INSTALLATION ##
see the docs https://www.axel-hahn.de/docs/ahcrawler/get_started.htm


## FEATURES ##

- Free software and Open Source.
- you can install it on your location. 
  - All data stay under your control. 
  - And you have full control about the age of the checked content. After fixing errors rerun the indexer and immediately get fresh results.
- multi language support (backend and frontend)
- built in web updater

### spider ###
- respects exclude rules in
  - robots.txt
  - x-robots http header
  - meta robots values noindex, no follow
  - rel=nofollow in links
- additional rules for include and exclude rules with regex
- multiple simultanous requests
- rebuild full index or update a single url (i.e. to be triggered by a cms)
- uses http2 (if possible)

### search for your website ###
- search with OR or AND
- search in language (requires lang attribute in your html tags)
- search in a given subfolder only
- several methods for pre defined forms or for fully customized form
- stores users searchterms for a statistics

### website analyzer ###
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
