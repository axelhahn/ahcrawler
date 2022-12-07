## Cronjob: updater

A software update is visible in the backend and could be done interactively. Or you install a daily or weekly cronjob with the updater script.

The updater checks if a newer version exists. If so it will be installed. The script supports command line parameters to check the version only without installing a newer version (parameter -c) or to force an installation (of newer or current version).

The script supports exitcodes if you wish to embed it in other scripts.

### Show parameters

You can run updater.php -h or --help to get a list of supported prameters.

```txt

===== AhCrawler :: Updater =====

HELP:
CLI updater tool. It checks if a newer version exists and - if so - installs the update.

PARAMETERS:
  -c
  --check (without value)
    Check only.
    If a newer version would exist, you get just a message and exitcode 1 (without installing the update).

  -f
  --force (without value)
    force installation
    If no newer version exists it reinstalls the current version.

  -h
  --help (without value)
    show this help

```

### Add cronjob

As an example to install a software update every night at 5:45 AM add a crontab entry

```txt
45 5 * * *     php [path]/cronscripts/updater.php 2>/dev/null
```

To start the script without parameters once per day should be the most common call for self managed servers.
Add the redirection 2>/dev/null to prevent getting emails. In the backend you get the log output of the spider process.

Maybe you want to have a look to my [Cronwrapper (on Github)](https://github.com/axelhahn/cronwrapper) ... a daily job has a ttl of 1440 minutes:

```txt
45 5 * * * /usr/local/bin/cronwrapper.sh 1440 "php [path]/cronscripts/updater.php"
```

### Exitcodes

The updater script supports exitcodes to be embedded in other scripts.
Especially the parameter "--check" (to check for an update without installing it) could be used to trigger an alert or information.

exitcode | Comments
---      | ---
0 	     | Execution was successful. It can mean<br>- No newer version exists.<br>- If a newer version existed: the installation of the update was successful.
1        | A newer version exists.<br>This exitcode can occur on param "--check" only.
2 	     | A newer version exists but the download failed.
3 	     | A newer version exists, download was successful but the installation failed. 