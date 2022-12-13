
# AH CRAWLER #

## Description ## 

AhCrawler is a search engine for your website and analytics tool.

This is free software and Open Source 
GNU General Public License (GNU GPL) version 3

👤 Author: Axel Hahn\
🧾 Source: <https://github.com/axelhahn/ahcrawler/>\
📜 License: GNU GPL 3.0\
📗 Docs: see <https://www.axel-hahn.de/docs/ahcrawler/>

⚠️ **Important notice**:
In version **v0.156** the filestructure was changed. 
--> See [Upgrade to v0.156](docs/00_⚠️_Upgrade_to_v0156.md)

- - - 
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
