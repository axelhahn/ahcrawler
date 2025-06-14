## User restriction

### Introduction

There are different levels of user restriction when using the ahCrawler backend. Choose a level of a protection you need.

(1) **No user restriction**

On a local system/ small network you can run the tool without user definition. You have access to the backend and the frontend.

(2) **Single user**

If you install ahCrawler on a public system / shared Hosting you should set minimum an admin account to protect the backend.
In the backend you can setup exactly one backend user.

(3) **Multiple users - all with full access**

If multiple users have access to the backend then you can setup a restriction for the access. 

Choose any method you already support to limit a user based access.

In very easy cases maybe a simple ip restriction is a possible choice.

(4) **Multiple users - restrict everyone to specific projects with given roles**


You need a logon to fetch a user id from the $_SERVER environment variable. For that you can use different methods

* Basic authentication
* SSO (like Shibboleth)
* OpenIDC (like a connection to Keycloak)
* ...

### Basic authentication on backend

This is just an example for an authentication that is managed outside ahCrawler.

You can setup users in htauth users in files or database backends (like Mysql or Ldap).

!!! warning "Important"
    Restrict the access to the location */backend/index.php* - a single file only.

```txt
   <Location "/backend/index.php">
     Require valid-user
     AuthType Basic
     ...
   </Location>
```

### ACL

You can setup an ACL for the backend.

#### Requirements

* You need to setup an external authentication to fetch a user id from any $_SERVER environment variable.
* In a php configuration (= there is no web ui for it) you need to define
    * global access for defined admin users
    * project based access for users with defined roles (one of admin, manager, view)

#### Installation

(1) **Protect the backend**

```txt
   <Location "/backend/index.php">
    # your authentication type/ SSO specific
    # protection code
   </Location>
```

(2) **Create Configuration file**

Copy the file `public_html/config/acl.php.dist` to `public_html/config/acl.php` and make your changes there.

#### Configuration

```php
<?php

return [
        // ---------- USER
        'session_user'=>'AUTH_USER',
        'userfield'=>'REMOTE_USER',
        // 'displayname'=>['givenName', '_surname'],
        'displayname'=>false,

        // 'userfield'=>'mail', // set by shibbolethauthentication

        // ---------- GROUPS
        'groups'=>[...],
];
```

name         | type   | description
---          | ---    | ---
session_user | string | name of the session variable that will hold the detected user id
userfield    | string | name of the field in $_SERVER that holds the user id. REMOTE_USER is a good choice. Other methods maybe use other fields - then you need to customize it.
displayname  | string | name of the field that holds the display name. Use this if your login system offers a user and a clear display name. When setting it to false then the userfield will be used.
groups       | array  | group based acl rules. See below.

Let's have a look at the Section "groups":

```php
<?php

return [
        ...

        // ---------- GROUPS
        'groups'=>[

            // global admins for all projects
            'global'=>[
                'admin'=>[
                    'axel',
                    'peter',
                ],
                'manager'=>[],
                'viewer'=>[],
            ],

            // project id 1
            '1'=>[
                'admin'=>[],
                'manager'=>[
                    'anton',
                ],
                'viewer'=>[
                    'berta'
                ],
                    
            ],
        }
];
```
        

Global groups and project based groups can have the names (starting with the lowest permissions)

* **viewer** - can see the produced results
  * search indexed content
  * test search results
  * see the log
  * see search terms of searches on the frontend (if available)
  * see all analyzer functions for ssl, http header, cookies, html checks, link checker, counters
* **manager** - has viewer permissions PLUS
    * can edit the project profile
* **admin**
  * a **project admin** can edit everything related to a project:
    * can trigger reindexing of the project
  * a **global admin** ... 
    * has admin permissions to all projects
    * can edit language texts
    * can edit global settings
    * can download libs
    * can apply updates

With activated acl configuration a not configured user has no access anymore.

Edit (or deploy) `public_html/config/acl.php` with set global and app specific permissions. As **global admin** you can verify the applied changes visually in the page Settings -> User roles.
