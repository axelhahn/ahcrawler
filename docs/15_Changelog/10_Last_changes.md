## Last changes

### 2023

* 🟢 ADDED: viewer page for all counters
* ↗️ UPDATE: Medoo (AcCrawler is compatible with PGHP 8.2 with it)
* ↗️ UPDATE: docker dev environment

#### 2023-08-03: v0.164

* 🟢 ADDED: on failed connections (http status code 0 (zero)) the curl error is shown.
* ↗️ UPDATE: add DOCKER_USER_UID in docker env

#### 2023-07-06: v0.163

* ↗️ UPDATE: ahCache class
* ↗️ UPDATE: ah web updater classes
* ↗️ UPDATE: html analyzer class

#### 2023-05-09: v0.162

* 💣 FIX: error with missing vendor cache dir
* ↗️ UPDATE: cdnorlocal --> 1.0.13
* 🟢 ADDED: metadata of needed libs

#### 2023-05-09: v0.161

* 💣 FIX: cdnorlocal because API response of Cdnjs was changed
* 💣 FIX:  left menubar is scrollable
* ↗️ UPDATE: pure 2.1.0 --> 3.0.0
* ↗️ UPDATE: jQuery 3.6.1 --> 3.6.4
* ↗️ UPDATE: font-awesome 5.15.4 --> 6.4.0

#### 2023-03-09: v0.160

* 💣 FIX: counters were set in a wrong way. Graphs of values in the last N days were wrong
  You can wait until currently wrong counter values are out of scope after 90d or you can execute `truncate counteritems` and `truncate counters` on the database to delete them.
* ↗️ UPDATE: css update of default theme
* ↗️ UPDATE: fix php warnings in some classes
