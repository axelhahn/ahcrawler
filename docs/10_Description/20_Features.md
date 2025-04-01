## Features

The tool is designed to run on a local machine, a vm or your own server. All data, content, timing of reindex processes are under your control.

### General

  * The application is written in PHP
  * Web ui and CLI interface
  * CAN be usable on shared hosters (see requirements)
  * support of multiple websites in a single backend
  * All data under ypour control
  * All timings for reindexing under your control

### Spider

  * spider to index content: CLI / cronjob / from web ui
  * respects
    * robots.txt
    * rel attributes or http headers noindex, no-follow
    * custom include an exclude rules
  * multiple parallel requests for GET and HEAD

### Search for your website

* customizable search form and result page can be integrated into your website.
* search can be limited to 
  * a path eg. /blog/ or /docs/ - if you structured your website it will be useful for you
  * an language (requires lang attribute in html tag)
* Ranking is calculated by count of hits, its places (url, description, keywords, content) and position (matching a full word, on word start, anywhere)
* templating with placeholders to customize output of the search results 


### Web UI

**Admin backend**

* Spidering
  * verify work of the spider: search content
  * test search index
* Website search
  * check entered search commands of your visitors
* Analysis
  * analyze http reponse header
  * analyze ssl certificate
  * analyze html metadata: title, keywords, loading time
  * link checker
* Other features
  * built in web updater
  * multi language support
  * user based permissions per project

**Public tools**

Some basic functionality can be enabled to be used without access to the backend.

* analyze http reponse header
* analyze ssl certificate
