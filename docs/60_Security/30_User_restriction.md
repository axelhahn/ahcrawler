## User restriction

### Introduction

There are different levels of user restriction when using the ahCrawler backend. Choose a level of a protection you need.

(1) **No user restriction**

On a local system/ small network you can run the tool without user definition. You have full access to the backend and the frontend.

(2) **Single user**

If you install ahCrawler on a public system / shared Hosting you should set minimum an admin account in the settings to protect the backend.

Remark: In the backend you can setup exactly one backend user.

Without definig acl rules this user has full access to all pages for all created projects.

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
        AuthName "AhCrawler Backend"
        AuthType Basic
        Require valid-user
        ...
    </Location>
```

### ACL

You can setup an ACL for the backend. This allows you to protect the backend for specific users. For each user you can define its visibility of projects and its permissions per project.

Additionally you can define a list of users with global access to all projects.

#### Requirements

* You need to setup an external authentication to fetch a user id from any $_SERVER environment variable. Many use "REMOTE_USER".
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

##### Basic auth - file based

With htpasswd (or openssl or other tools) you can create a file with users and passwords.
The generated file must be readable by the webserver user (eg www-data). Reference its file location with "AuthUserFile".

This is a basic example:

```txt

    # when using php-fpm don't forget this line:

    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

    # protection of backend

    <Location "/backend/index.php">
        AuthName "AhCrawler Backend"
        AuthType Basic
        AuthUserFile "/var/www/ahcrawler/users/.htpasswd"
        Require valid-user
    </Location>
```

##### Openidc

You can use an oidc provider to fetch users eg Keycloak.
You need to enable the module "auth_openidc".

This is a basic example for the vhost configuration with placeholders. For its values contact your oidc provider.

```txt

    # set vars in your vhost:

    OIDCCryptoPassphrase <OIDCCryptoPassphrase>
    OIDCClientID <KEYCLOAK_CLIENT>
    OIDCClientSecret <KEYCLOAK_CLIENT_SECRET>
    OIDCProviderMetadataURL https://keycloak.example.com/realms/<KEYCLOAK_REALM>/.well-known/openid-configuration
    OIDCRedirectURI ...
    OIDCPassClaimsAs ...
    OIDCRemoteUserClaim ...
    OIDCScope ...

    # protection of backend

    <Location "/backend/index.php">
        AuthType openid-connect
        Require valid-user
    </Location>
```

##### Shibboleth

```txt
    <Location "/backend/index.php">
        AuthType shibboleth
        ShibRequestSetting requireSession 1
        ShibUseEnvironment On
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
        // 'userfield'=>'mail', // set by shibbolethauthentication

        'displayname'=>false,
        // 'displayname'=>['givenName', '_surname'],

        // ---------- GROUPS
        'groups'=>[...],
];
```

name         | type   | description
---          | ---    | ---
session_user | string | Name of the session variable that will hold the detected user id. It must be 'AUTH_USER' for ahCrawler.
userfield    | string | Name of the field in $_SERVER that holds the user id. It depends on the authentication method and environment. <ul><li>Basic auth: try these<ul><li>REMOTE_USER</li><li>AUTHENTICATE_UID</li><li>PHP_AUTH_USER</li></ul></li><li>Oidc</li><ul><li>REMOTE_USER</li></ul><li>Shibboleth<ul><li>REMOTE_USER (it contains the user id that can be a bit cryptical)</li><li>mail (it can be an alternative to configure just a few users)</li></ul></li></ul>
displayname  | bool|array | name of the fields that holds the display name. Use this if your login system offers a user and a clear display name for a user. When setting it to false then the userfield will be used.
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
                'viewer'=>[
                    '@authenticated'
                ],
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

Subkeys are the names of the groups. "global" is a special key for global permissions that will be applied to all projects.

The subkey inside a group is the name of the permission.

name         | type   | description
---          | ---    | ---
admin        | array  | users with admin permissions
manager      | array  | users with manager permissions
viewer       | array  | users with viewer permissions

Global groups and project based groups can have the names (starting with the lowest permissions)

* **viewer** - can see the produced results
  * search indexed content
  * test search results
  * see the log
  * see search terms of searches on the frontend (if available)
  * see all analyzer functions for ssl, http header, cookies, html checks, link checker, counters
  * see users with access to his projects
* **manager** - has viewer permissions PLUS
    * can trigger reindexing of the project
* **admin** has manager permissions PLUS
  * a **project admin** can edit everything related to a project:
    * can edit the project profile
  * a **global admin** ... 
    * has admin permissions to all projects
    * can edit language texts
    * can see all users of all projects
    * can see user permissions of a selected user
    * can edit global settings
    * can download libs
    * can apply updates

In each permission there can be a list of users. As user you need to enter ...

* the user id of the user that you get back by the configured "userfield" value in $_SERVER scope.
* a generic group is '@authenticated'. Each user that is logged in has this group. 

With activated acl configuration a not configured user has no access anymore.

As **global admin** you can verify the applied changes visually in the page Settings -> User roles.
