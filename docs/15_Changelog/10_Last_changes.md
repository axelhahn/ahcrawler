## Last changes

### 2025

#### 2025-01-04: v0.173

* 🟢 ADDED: select2 component for searchable drodowns
* ↗️ UPDATE: Analyze -> Counter: change order - show latest values on top
* ↗️ UPDATE: fontAwesome 6.6.0 -> 6.7.2
* ↗️ UPDATE: Docker dev environment changed to PHP 8.4 for testing: Medoo requires 8.3

### 2024

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
