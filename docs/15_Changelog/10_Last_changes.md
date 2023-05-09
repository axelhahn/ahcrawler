## Last changes

### 2023

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
