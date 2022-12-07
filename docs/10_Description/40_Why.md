## So, why I wrote this tool?

The starting point was to write a crawler and website search in PHP as a replacement for Sphider that was discontinued and had open bugs.

On my domain I don't use just a CMS ... I also have a blog tool, several small applications for photos, demos, ... That's why the internal search of a CMS is not enough. I wanted to have a search index with all of my content of different tools.
So I wrote a crawler and an html parser to index my pages...

If I had a spider ... and an html parser ... and all content information ... the next logical step was to add more checks for my website. And there are a lot of possibilities like link checkers, check metadata, http response headers, ssl information ...

You can install it on your own server or a shared hoster. All data are under your control. All timers are under your control. If you fix something: just reindex and then immediately check if the error is gone.
