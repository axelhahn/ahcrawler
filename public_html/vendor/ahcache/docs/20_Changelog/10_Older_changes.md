## Older changes

### 2021

#### 2021-10-07  2.8

FIX: remove chdir() in _readCacheItem()
ADD reference file to expire a cache item

- added: getRefFile
- added: setRefFile
- update: dump, isExpired, isNewerThanFile, write
- update cache admin

#### 2021-10-07 2.7  

FIX: remove chdir() in _readCacheItem()
ADD reference file to expire a cache item

- added: getRefFile
- added: setRefFile
- update: dump, isExpired, isNewerThanFile, write
- update cache admin

#### 2021-09-28  2.6

added a simple admin UI; the cache class got a few new methods

- update: cleanup() now always deletes expired items
- update: dump() styles output as table
- added: getCurrentModule 
- added: deleteModule 
- added: loadCachefile
- added: removefileDelete
- added: setCacheId
- added: setModule

### 20219

#### 2019-11-xx  2.7

- class was moved to folder src
- added admin webgui
- method getCachedItems - fix filter lifetime_greater

#### 2019-11-26  2.5  

- added getModules() to get a list of existing modules that stored a cached item

#### 2019-11-24  2.4

- added getCachedItems() to get a filtered list of cache files
- added remove file to make complete cache of a module invalid
- rename var in cache.class_config.php to "$this->_sCacheDirDivider"

### 2014

#### 2014-03-31  2.3

- added _setup() that to includes custom settings
- limit number of files in cache directory

### 2012

#### 2012-05-15  2.2

- rename to AhCache
- _cleanup checks with file_exists

#### 2012-05-15  2.1

- isExpired() returns as bool; new method iExpired() to get expiration in sec

#### 2012-02-04  2.0  

cache serialzable types; more methods, i.e.:

- comparison of timestimp with a sourcefile
- cleanup unused cachefiles

### 2011

#### 2011-08-27  1.1

comments added; sCacheFile is private

### 2009 

#### 2009-07-20  1.0

cache class on www.axel-hahn.de
