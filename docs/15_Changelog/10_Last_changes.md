## Last changes

### 2025

#### 2025-09-09: v0.179

* 🟢 ADDED: multi user access to projects with given role. This is a huge change you can enable now! Please read the docs how to configure it. 👉 See [Security User restrictions](../60_Security/30_User_restriction.md)
* ↗️ UPDATE: ahCrawler is PHP 8.4 ready
* ↗️ UPDATE: PHP versions below v2 are marked as error because http 1 .1 has security issues
* ↗️ UPDATE: replace fontawesome with tabler icons
* ↗️ UPDATE: default light theme got colors by main section
* ↗️ UPDATE: themes: more colors in navigation bar in deault and default dark theme. You can switch to the older look when setting the `[name] - simple` theme.
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
