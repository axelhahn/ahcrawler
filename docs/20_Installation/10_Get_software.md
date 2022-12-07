## Introduction

If you want to run it as a **self hosted** application and can handle files outside webroot: take the data from **github.com**

* use git if available (recommended)
* otherwise download tha master as Zip or Tgz

If you have **no access outside webroot** like on a on a shared hosting then you need the part in the **public_html subfolder** only:

* get the files from github.com locally and upload the content of the public_html only OR
* download the content of public_html from sourceforge.net

## Self-hosted application

### Git clone the project (recommended)

Checkout sources with git client.
The following commands create a directory ahcrawler below webroot and put all files there:

```txt
cd /var/www
git clone https://github.com/axelhahn/ahcrawler.git [optional-name-of-subdir]
``` 

Without [optional-name-of-subdir] it will create a subdir named "ahcrawler"

In your webservice configuration set the document root to `/var/www/ahcrawler/public_html/`.

### Download the project

Use this variant if you don't have/ don't want to use git.

On the Github project <https://github.com/axelhahn/ahcrawler> you find after pressing [Code] a direct link to download a Zip archive.
<https://github.com/axelhahn/ahcrawler/archive/refs/heads/master.zip>

Extract it in /var/www/ ... it creates a subdir "ahcrawler-master" - rename it like you need it, eg. "ahcrawler".

```txt
cd /var/www/
wget https://github.com/axelhahn/ahcrawler/archive/refs/heads/master.zip
unzip master.zip
mv ahcrawler-master ahcrawler
rm -f master.zip
```

In your webservice configuration set the document root to `/var/www/ahcrawler/public_html/`.

## Web data only

Use this variant on a shared hosting with limited access to files inside webroot only.

You can get all files locally like described in the section "Self-hosted application" above and upload the content of subfolder "public_html" into a subdirectory.

Another solution is to ...

### Download from sourceforge.net

Go to your webroot of your hosting. Create a subdirectory for ahcrawler. There we extract the zip with application files.

```txt
ssh your-name@hosting.example.com
mkdir ahcrawler
cd ahcrawler
wget https://sourceforge.net/projects/ahcrawler/files/latest/download
unzip download
rm -f download
``` 
