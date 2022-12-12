## Cronjob: reindex all

It is helpful to regenerate the index by a cronjob. Therefor exists a script that reindexes all data of all projects you already created. Don't fiddle with the parameters above :-) ... use the script in ./cronscripts/ directory.

### Show parameters

You can run reindex_all_profiles.php -h or --help to get a list of supported prameters.

```txt
===== AhCrawler :: Cronjob - reindex all =====

HELP:
CLI reindexer tool for a cronjob.
It flushes all indexed data of all profiles and then reindexes them.

PARAMETERS:
  -u
  --update (without value)
    update only
    Do not flush and reindex all - only update (=rescan errors and missed items)

  -p
  --profile [value] (value required)
    profile id
    Set a profile id. Do not handle all profiles - just a single one. default: all profiles
    If a value is given then it will be checked against regex /^[0-9]*$/

  -h
  --help (without value)
    show this help

```

### Add cronjob

As an example to reindex every night at 2:15 AM add a crontab entry

```txt
15 2 * * *     php [path]/cronscripts/reindex_all_profiles.php 2>/dev/null
```

To start the script without parameters once per day should be the most common call for self managed servers.
Add the redirection 2>/dev/null to prevent getting emails. In the backend you get the log output of the spider process.

Maybe you want to have a look to my [Cronwrapper (on Github)](https://github.com/axelhahn/cronwrapper) ... a daily job has a ttl of 1440 minutes:

```txt
15 2 * * * /usr/local/bin/cronwrapper.sh 1440 "php [path]/cronscripts/reindex_all_profiles.php"
```

### Hints for usage on a shared hoster

A limititation on a shared hoster can be the execution timeout.
You can try to run a an initial process for a full reindex (the same example like above)...

```txt
15 2     * * *   php [path]/cronscripts/reindex_all_profiles.php          2>/dev/null
```

and additionally add jobs with --update to finish the crawling of missed items:

```txt
15 4,6,8 * * *   php [path]/cronscripts/reindex_all_profiles.php --update 2>/dev/null
```

If you still get timeouts run the full index and update jobs profile by profile as single tasks.

As an example for two profiles:

```txt
15 2     * * *   php [path]/cronscripts/reindex_all_profiles.php --profile 1           2>/dev/null
15 4,6,8 * * *   php [path]/cronscripts/reindex_all_profiles.php --profile 1 --update  2>/dev/null
```

For your website with profile 2 add its own commands for indexing on a separate timeslot:

```txt
15 3     * * *   php [path]/cronscripts/reindex_all_profiles.php --profile 2           2>/dev/null
15 5,7,9 * * *   php [path]/cronscripts/reindex_all_profiles.php --profile 2 --update  2>/dev/null
```
