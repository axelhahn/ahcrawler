## Page: Resource details

The detail page of a resource (given url) shows you 

* Meta information
* Http response header
* Which pages / urls point to the current url
* Links/ redirects from here

### Meta information

A table shows you 

* Age of the last scan
* Http status, eg. 200
* Place - eg. internal
* Resource - the type page, lnik, image, css, script, ...
* MIME - the mime type from the http response header, eg. text/html
* Size - the size of the transferred data
* Timestamp of scan
* Total time of transfer [s]

If you press the button with the arrow it opens the url in a new window.

### Http response header

The table contains the information with a commented http response header: you see the header variable and value. A line can by colored by type and shows a found label, eg. "caching", "compression", ...

See also: [Http header check](20_Http_header_check.md)

### Used in

In this section you see which pages / urls point to the current url.

* If you watch an html page you see the links that point here
* If you watch a css file / image / script you get a list of references here

### Linked resources

If the current url has a redirect or links to other urls then you can fnid all of it here.

By default the linked resources with http status OK are hidden - so you get a kind of a linkchecker showing warnings warnings and errors only.