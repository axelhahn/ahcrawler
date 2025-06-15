## Page: Update

The software Updater of the web ui.

### Version check in web ui

If a new version exists you get a direct link to this page.
![Screenshot: Updater](/images/updater-01-infobox.png)


**Remark**: you can check or apply a software update on command line too.

### On start

It checks if a newer version exists. 

* If not it show an OK message that you are up to date.
* If a new version exists it show your current and the latest available version.

![Screenshot: Updater](/images/updater-02-assistent-startpage.png)

### Buttons

* **Continue**
  interactive mode to download new software archive and install it.
  Use it the first time to see how the updater works.
* **Update in a single step**
  This is the one click updater method.

### Start update

The updater has a bit intelligence. It detects if it was installed by git (git pull) or by a manual download to define the update method.

* Git\
  A `git pull` wil be started
* manual installation
  * A TGZ archive will be downloaded from sourceforge.
  * The Md5 checksum will be verified
  * The TGZ will be extracted in the install dir

On each step you get a description and the console output of a command. The updater aborts if one of the steps failed.

![Screenshot: Updater](/images/updater-03-information-before-download.png)
![Screenshot: Updater](/images/updater-04-information-after-download.png)
![Screenshot: Updater](/images/updater-05-information-after-uncompress.png)
![Screenshot: Updater](/images/updater-06-done.png)

Last but not least:

Refresh your browser cache to load latest changes of css and javascript.