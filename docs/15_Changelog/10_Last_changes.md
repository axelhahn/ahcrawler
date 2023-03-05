## Last changes

### 2023

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

--> See [Upgrade to v0.156](../00_%E2%9A%A0%EF%B8%8F_Upgrade_to_v0156.md)

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
