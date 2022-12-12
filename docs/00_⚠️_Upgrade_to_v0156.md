## What happened in v0.156 ...

This was the file structure so far ... all files and directories you were able to git pull below webroot or any subdir:

```txt
├── backend
├── bin
├── classes
:
└── vendor
```

Since v0.156 I added a docker dev environment and docs - and all current files were moved into a new subdir "public_html":

```txt
├── docker
├── docs
└── public_html
    ├── backend       <<< "backend" and other directories are below "public_html" now.
    ├── bin
    ├── classes
    :
    └── vendor
```

## Emergency: Rollback and update later

As first variant and if you have no time for an upgrade now:
You can rollback to the version v0.155 and update later. To reset to the last version 0.155 I created a tag "v0.155-rollback"

```txt
$ git reset --hard v0.155-rollback
HEAD is now at b23fc94 Merge pull request #10 from axelhahn/v0.155
```

Otherwise other solutions are listed below.

## For installations on webroot

### A fresh git pull (recommended)

#### Fresh installation

One level above the current installation start a new git pull

```txt
$ git pull https://github.com/axelhahn/ahcrawler.git [Name-of-subdirectory]
```

#### Transfer current config + data

Then copy these files from old appdir to the new one:

```txt
$ cp -p config/*.json [new-appdir]/public_html/config/
$ cp -p data/*        [new-appdir]/public_html/data/
```

If you downloaded some used vendor libs locally (instead of its usage from CDNJS) then copy the directories in vendor/cache too:

```txt
$ cp -rp vendor/cache/* [new-appdir]/public_html/vendor/cache/
```

If you used ahCrawler in webroot then move the application dir one directory level up that the subfolder "public_html" matches your webroot.

### Move current dir 1 level up

If you want just to copy and move data then move the appdir 1 level up.

Then copy `./config/*.json`, `./data/*` and `./vendor/cache/*` into `./public_html/[SAME_NAME]`.

Then move the current appdir 1 level up that the "public_html" folder still matches your webroot.

## For installations below webroot

If you had an installation in a subdir below webroot so far there are 2 possibilities.

### new git pull

If you use a self hosted app and you can switch to a variant that runs on webroot instead of a subdir then follow the instruction to the new git pull variant above.
If the url changes then it has side effects regarding integrated a searches on websites, set links or or bookmarks.

### Switch to a "non git" installation type

Especially on a shared hosting is less flexibility if you ordered a domain and its a requirement to run the app in a subdir. You can switch to a non-git installation type without trouble. The built in updater of ahcrawler handles git and non-git installations.

(1)
Like on other variants above you need to transfer the current config and data.
Copy `./config/*.json`, `./data/*` and `./vendor/cache/*` into `./public_html/[SAME_NAME]`.

(2)
Remove the new folders `./docs/`, `./docker/` - and very important: the folder `./.git/`

(3)
Go into public_html folder (`cd public_html`) and move all 1 directory up eg. `mv * ..`
Go one level up (`cd ..`) again and remove the folder "public_html"
