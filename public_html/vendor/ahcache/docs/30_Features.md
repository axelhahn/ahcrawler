## Features

* Storing as file is fast
* you can cache all serializable items: strings, arrays, objects
* cache items are initialized with any application and an id to make it unique and to separate all cache items by an application or task
* invalidate your cache with different methods:
  * by a TTL vlue in seconds
  * by a local reference file (you can touch a file to invalidate a cache)
  * touch a single module based file to all cache items of th module
* a cleanup method can delete all outdated cache items of all applications or a selected application
* a cache admin (web ui) visualizes the created cache items 
