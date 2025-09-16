## Last changes

### 2025

#### 2025-09-16: v0.181

* ↗️ UPDATE: optical enhancements, css updates eg click in menu on activated dark theme
* ↗️ UPDATE: Beautify page layout of default skin
* ↗️ UPDATE: changelog icon in about page
* ↗️ UPDATE: Render left navigation before content
* ↗️ UPDATE: Shorter waiting time after saving settings
* 💣 FIX: detect user from Basic auth without acl confg

#### 2025-09-14: v0.180

* ↗️ UPDATE: optical enhancements, css updates eg click in menu on activated dark theme
* 💣 FIX: php warning ini user detection

#### 2025-09-09: v0.179

* 🟢 ADDED: multi user access to projects with given role. This is a huge change you can enable now! Please read the docs how to configure it. 👉 See [Security User restrictions](../60_Security/30_User_restriction.md)
* ↗️ UPDATE: ahCrawler is PHP 8.4 ready
* ↗️ UPDATE: PHP versions below v2 are marked as error because http 1 .1 has security issues
* ↗️ UPDATE: replace fontawesome with tabler icons
* ↗️ UPDATE: default light theme got colors by main section
* ↗️ UPDATE: themes: more colors in navigation bar in default and default dark theme. You can switch to the older look when setting the `[name] - simple` theme.
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
