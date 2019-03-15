
# AH CRAWLER #

## DESCRIPTION ## 

Open Source tool written in PHP with 
- crawler (spider) and indexer
- simple search for your website
- website analyzer with
  - ssl certificate check
  - http response header check
  - linkchecker

Runs with PHP 5.5 and higher (up to PHP 7.3); PHP7 is recommended.
It uses PDO to store indexed data. So far sqlite and mysql were tested.

This software has BETA status.
You can preview it ... but let me do some more work :-)

![Screenshot: backend](https://www.axel-hahn.de/assets/projects/ahcrawler/03-analyse.png)


## INSTALLATION ##
see the docs https://www.axel-hahn.de/docs/ahcrawler/get_started.htm


## FEATURES ##

- multiple website support within a single installation
- multi language support (backend and frontend)
- install on your location - all data stay under your control
- you time the run of the spider: make a check based on a full scan of your
  website (you don't know when a webbot has seen all of you pages the last time)
- built in web updater

### spider ###
- respect exclude rules 
  - robots.txt
  - meta robots values noindex, no follow
  - x-robots http header
  - rel=nofollow in links
- additional rules for include and exclude rules with regex
- multiple simultanous requests
- rebuild full index or update a single url (i.e. to be triggered by a cms)
- uses http2 (if possible)

### search for your website ###
- search with OR or AND
- search in language (requires lang attribute in your html tags)
- search in a subfolder only
- several methods for pre defined forms or for fully customized form
- stores users searchterms for a statistics

### website analyzer ###
- check of http reponse header for 
  - unknown headers
  - unwanted headers
  - security headers
- check ssl certificate (if your website uses https)
- show webite errors, warnings based on http status code (a.k.a. linkchecker)
  with giving hints what to do at which status code
- for a given url: display where it is used and where it links to showing
  as cascade on redirects (30x status in repsonse header)
- view over all webpage items (pages, js, css, media) with filter by
  - http status code
  - mime type
  - place (internal item or extern)
