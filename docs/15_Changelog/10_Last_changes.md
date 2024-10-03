## Last changes

### 2024

#### 2024-10-03: v0.171

* 💣 FIX: resource scan created duplicates of pages as links
* ↗️ UPDATE: optimize resource scan
* ↗️ UPDATE: hide skip messages in crawler runs (can be enabled in the options)
* ↗️ UPDATE: on error code 0 the curl error is shown

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

