## Password reset

In the settings you can define a single user and password for the backend.

Hint: if you need access for multiple users then you can use a protection with basic authentication.

See [Hardening](10_Hardening.md)

### Where

It you lost the password then you can edit `public_html/config/crawler.config.json` with a text editor and update the password in options -> auth -> password.

### Generate a new password hash

On a console get the hash for a wanted password first:

```shell
php -r "echo password_hash('mypassword', PASSWORD_DEFAULT);"
```

Remark: if you repeat the command thenyou get another hash value for the same password. This is the wanted behaviour.

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

