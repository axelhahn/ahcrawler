## Introduction of the CLI tool

The ahcrawler has a command line tool.
It is located in the ./bin/ subdirectory.
With it you can

* list current profiles
* (re)index a website profile
* delete data of a profile
* flush all data of all profiles

### Syntax

It was written to be used in cronjobs and for manual indexing.

Calling it without parameter it shows a help.

![Screenshot: help page of cli.php](/images/cli-help.png)

**Basic syntax**

The most commands you will need have a structure with 3 parameter blocks

```txt
cli.php [action] [for wich data] [and which profile]
```

You can use the short variant for the parameters or long (which are more readable).

**Actions**

```txt
--action [name of action] or -a [name of action]
```

Known actions are:

Action | Description
---    |---
list   | list all existing profiles
index  | start crawler to reindex searchindex or resources
update | start crawler to update missed searchindex or resources
empty  | remove existing data of a profile
flush  | drop data for ALL profiles

**Data**

```txt
--data [name] or -d [name]
```

Valid data items are:

Item        | Description
---         |---
searchindex | the database of the webcontent for a website search; his is always the first data item you need to fill!!
resources   |  the used resources in your website (links, images, css, js files)
search      | the entered search terms of your visitors (if you use the search form)
all         | short for searchindex + resources


**Profile**

In the backend you can define multiple profiles for different websites. You need to add the profile id for each action.

```txt
--profile [id] or -p [id]
```


### List profiles

With the list action you find out the ids of your profiles.
These ids you will need for the parameter --profile (or -p) in other actions.

**Example**:

```txt
cli.php --action list
```

### (Re-) Create the website data

With the reindex action you can 

* delete existing indexed data and 
* start the indexer

This is is the most simple variant to create or fully update a profile. It handles both data stores in a single step: it deletes and indexes **searchindex** and **resources**.

The --profile parameter defines the profile to handle.

**Example**:

```txt
cli.php --action reindex --profile 1
```

**Remark**: On a shared hosting with a limited execution time you can split actions (empty then index and then update), data resources (searchindex and resources) while looping over all profiles.

### Create index of a website

With the index action you can start the indexer to rescan the searchindex OR the linked resources.

The --profile parameter defines the profile to handle.
The --data parameter is used to tell what to index.

* searchindex - the database of the webcontent for a website search; this is always the first data item you need to fill!!
* resources
* all (searchindex + resources)

**Example**:

```txt
cli.php --action index --data all --profile 1
```

**Remarks**:

* If the website was crawled before you may want to delete the data of a single profile first (action empty) - or flush all indexed content of all profiles (action flush).
* To delete already indexed data you need to call the "empty" action (see below).

### Rescan last errors

With the update action you can complete a scan. It starts the indexer to check all items that failed in the last run and have an error status.
Repeat the update command after a full index of a website profile only.

The --profile parameter defines the profile to handle.
The --data parameter is used to tell what to index.

* searchindex
* resources

**Example**:

```txt
cli.php --action update --data resources --profile 1
```

### Empty data of a single website profile

With the empty action you can delete all entries of the given profile id. This command initiates a DELETE in the database table(s) for all items with the given profile id.

The --profile parameter defines the profile to handle.
The --data parameter is used to tell what to delete.

* searchindex
* resources
* all (searchindex + resources)
* search - be careful - this you don't want in the most cases
* full (searchindex + resources + search) - be careful - this you don't want in the most cases

**Example**:

```txt
cli.php --action empty --data searchindex --profile 1
```

### Flush data of all website profiles

With the flush action you can delete all data of all profiles. This command initiates a DROP TABLE command in the database.
You should use the flush command if you have created a search index and a resources scan and want to rebuild them from point zero.

The --profile parameter is not needed - dropping tables has impact to all profiles.
The --data parameter is used to tell what to delete.

* searchindex
* resources
* all (searchindex + resources)
* search - be careful - this you don't want in the most cases
* full (searchindex + resources + search) - be careful - this you don't want in the most cases

**Example**:

```txt
cli.php --action flush --data all
```
