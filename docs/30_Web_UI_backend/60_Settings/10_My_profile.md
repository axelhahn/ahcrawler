## Page: My profile

This page shows the current user, its groups and project based permisions.
You reach this page by clicking on the top right button "My profile".


### Username

The username is shown.

### Usergroups

In the list of usergroups are the names of 

* generic groups (starting with `@`)
* global groups
* project based groups (starting with `<number>_<name>`).

Global groups and project based groups can have the names

* **viewer** - can see the produced results
  * search indexed content
  * test search results
  * see the log
  * see search terms of searches on the frontend (if available)
  * see all analyzer functions for ssl, http header, cookies, html checks, link checker, counters
* **manager** - has vierer permissions PLUS
    * can edit the project profile
* **admin**
  * a **project admin** can edit everything related to a project:
    * can trigger reindexing of the project
  * a **global admin** ... 
    * has admin permissions to all projects
    * can edit language texts
    * can edit global settings
    * can dowload libs
    * can apply updates

### Global permissions

This section is visible only if global permissions were applied to your user.

### Web spezific permissions

This section is visible only if global permissions were applied to your user.

You see table with the permissions in the columns.

Per project is a row with the project name. You see a highligthed button what permission was set for this project.

### Logoff

On the bottom side is a logoff button.
It destroys the current session and will show up on the login page.
