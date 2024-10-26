##  Page: Search index

### Status overview

The tiles on top give a few information.

**indexed**\
The count of indexed web pages.

**in the last 24 hours**\
The count of indexed web pages that were updated in the last 24 h.

**Last update**\
It shows the date and time when the last page was indexed.

**oldest data in index**\
It shows the date and time of oldest pages. This is just a small check. If you update the complete search index once per week then the oldest element never should be older 7 days.

### Newest/ oldest urls in the index

This section appears if the newest and oldest items differ more than a day.

The table shows the 5 newest pages in the search index.
This is a control for you if the indexer is running.
Oldest urls in the index

The table shows the 5 oldest pages in the search index.
This is a control of the indexer for you. If you let update your index once per week then if your data older 7days i is a sign that the indexer did not run. 

### Urls in the search index ([N])

The table shows **all** pages in the search index.

Click to the url to get a detail page.

## Detail page

### Metadata of a page

You get details with 

* Title (it is a headline)
* Url - with a button to open it
* Description - from html head section
* Keywords - from html head section
* Language - from lang attribute in html tag
* Time - timestamp of the crawling
* Measured times - It shows a few network timers for this request. You see the method, url and http status code as text. Followed by a graph for 
  * time to prepare:
    * DNS lookup
    * connect
    * handshake
  * on server: The time that was used on server to process the request. This is the time you could optimize in your application.
  * Transfer to the client

A missing description or keywords will be marked.

### Http response header

Shows the http header if the crawler visited the url.

With the button below you can check the current http reaponse header. This feature requires that you enabled the http header check as a public page.

### Curl metadata

Show raw data of collected curl metadata.

Among them are http status code, download speed, timers for the connection, certificate information and more.

### Wordlist

This is a helper to define the keywords in the html meta section.

You get a list of most used words on this page with more than 3 occurences. By clicking a button with a number (of occurences) you can reduce and expand this list.

### Raw data in the database

This is a very deep view: You get tha values in the database table that is used for the online search. The line "content" shows the stripped data of a page.
