## Passwords

In the settings you can define a single user and password for the backend.

Hint: if you need access for multiple users then you can use a protection with basic authentication.

See [Hardening](10_Hardening.md)

### Password reset

It you lost the password and cannot login anymore then you can edit `public_html/config/crawler.config.json` with a text editor and update the password in options -> auth -> password.

### Generate a new password hash

On a console get the hash for a wanted password first. Execute the command and replace *mypassword* with the wanted string:

```shell
php -r "echo password_hash('mypassword', PASSWORD_DEFAULT);"
```

Remark: each time you repeat the command you get another hash value for the same password. This is the wanted behaviour.

### Update the password hash

Then open public_html/config/crawler.config.json in your text editor. Update the part behind the key password:

```json
{
    "options": {
        ...
        "auth": {
            "user": "...",
            "password": "SET-THE_NEW-HASH-HERE"
        },
        ...
    },
    ...
}
```

### Remove password

If you feel uncomfortable with executing a command in the terminal then you can disable the user and password in the config. The result is a password less access to the backend - without a login form.

There yo can set a new password in the settings.

If we have a look to the same snippet in the config file again:

```json
{
    "options": {
        ...
        "auth": {
            "user": "...",
            "password": "SET-THE_NEW-HASH-HERE"
        },
        ...
    },
    ...
}
```

Remove the 4 lines starting from `"auth"` including subitems "user" + "password" and `},`. The saved file must be a valid json file.

### Remove password II

If you know the current password and can login but want to remove it in the backend ui:

Got to the settings - in the section authentication:

* REMOVE the current username
* enter the current password
* leave the 2 fields for the new password empty

and save the settings. If you got the message, that the settings were saved and press Continue, then the logout button on the top right disappears.
