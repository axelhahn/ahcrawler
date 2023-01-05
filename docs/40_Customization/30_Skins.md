## Skins (WIP)

### Directory for a skin

To create a skin create a new directory below `public_html/backend/skins/`. To make it unique add a username as prefix and a name for the skin.

In this directory you MUST have these files:

* **info.json** - description file
* **skin.css** - css file with your custom rules

Additionally to them you can add here the files you want to include in your css (loaded css files or images)

If you created these files you see your skin in the settings page.

### File: info.json

This is a file with a few metadata.

```json
{
    "name": "Example skin",
    "version": "1.0",
    "description": "Example skin with sunny colors.",
    "author": "John Doe"
}
```

### File: skin.css

Have a look to the file public_html/backend/main.css. This file will be leaded by default. A skin css file defines overrides to it. 

Have a look to the delivered skin directories - and the skin.css files here.

For a light colored skin you might to override the color values only.

### Select skin

TODO