## Last changes

### 2025

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

#### 2025-01-11: v0.174

* ↗️ UPDATE: Ignore linkcheck for link tags with rel *dns-prefetch* (before it often resulted in 404 errors)

#### 2025-01-04: v0.173

* 🟢 ADDED: select2 component for searchable dropdowns
* ↗️ UPDATE: Analyze -> Counter: change order - show latest values on top
* ↗️ UPDATE: fontAwesome 6.6.0 -> 6.7.2
* ↗️ UPDATE: Docker dev environment changed to PHP 8.4 for testing: Medoo requires 8.3

### 2024

#### 2024-10-27: v0.172

* 💣 FIX: flip between search index detail and resource item (and back)
* 🟢 ADDED: show network times to prepare, process and sending response in the detail pages of a search index item and a resource item.
