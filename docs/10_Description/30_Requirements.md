## Requirements

The tool is designed to run on a local machine, a vm or your own server or as docker container.

* any webserver plus PHP 
* PHP: 
  * version 8 up to 8.3 as module (mod_php) or PHP-FPM service (maybe it runs on PHP 8.0+)
  * php-curl (could be included in php-common in some distros)
  * php-pdo and database extension (sqlite or mysql)
  * php-mbstring
  * php-xml

### Hints for shared hostings

It CAN run on a shared hosting (I do it myself). But a shared hosting can have limitations. It is not guaranteed that the tool can work for all websites with every hoster. Keep an eye to the following troublemakers.

* **PHP interpreter**: it must be allowed to start the php interpreter in a shell. To verify it, connect via ssh to your web hosting and execute php -v. If there is a not found message there is maybe a possibibility to start php in a cronjob. If the php interpreter is not available then it is not possible to start a crawling process. Ask your provider how to start php as cli tool. Maybe the provide allows it in another hosting package.
* **Script timeout**: The indexing process needs its time. It is longer as more single pages and linked elements you have. The indexing process for the html content must be finished within the timeout. The 2nd crawler process to follow linked items (javascript, css, images, media, links) can be repeated to handle still missed elements. You also can tune the number of parallel processes but increase it slowly to prevent selfmade DOS attacks.
