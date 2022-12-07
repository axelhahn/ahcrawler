## Page: Http Header check

If an http(s) request is sent to a server then it responds with header information (http response header) and the data of a website or file.
This page gives you an analysis of the header data that consists of a list of variables and values ... line by line. The http protocol has a set defined (known) variables. In general it is not easy to understand those or to see what information is missed.

![Screenshot: Backend - Analysis - SSL cert infos](/images/usage-03-analysis-http-response-header.png)

### Http response header

On top of the page is a bar with tiles give a first overview.

* total - the count of header information
* valid variables - the count of header data that match defined keys
* security headers - the count of security headers. If there is none the tile changes to a warning color.
* caching information - the count of caching information (it includes settings for no caching)
* compression - shows if a compression was set. If there is none the tile changes to a warning color.
* unknown varianbles - count of variables in the response that do not maht the http standard.
* unwanted data - count of header data that present more internal information than needed.

Below the tiles there is a link **Http header (plain)** that offers the http response header as plain text. Click it to open or close it.

Then follows a **table** with all http response data. For a visual help the header items are colored and use icons. You can see here if valid or invalid variables.
Additionally here is a check for availability of

* compression information - violet
* caching information - blue
* unknown/ unwanted information - yellow or orange
* security headers - green

### Warnings for Http headers

In this section you get details about header variables you should verify/ update. You get a tile in a warning color with the found variable and its value followed by a short description text and the source line in the http response header including a line number in brackets.

* unknown variables\
Check the variables that are unknown in Http standard. In most cases these are debugging information or invalid header data.

* unwanted data\
You should not show too many details of your system in the http header. Even if sniffer tools can analyze several details. You should remove these variables or remove the version details in their values.

* non standard headers\
Header entries will be detected, that are quite common - but non http standard.

### Check of existing security headers

Security headers are handled by modern browsers and increase the security of your application that runs in the browser. It is strongly recommended to use them to minify XSS or script injection.
In this section all security headers are shown. The tiles are green if one was found including their value ... or in a warning color if not.

Links:

* [developer.mozilla.org: Content-Security-Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy)
* [developer.mozilla.org: Cross-Origin Resource Sharing (CORS)](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
* [developer.mozilla.org: Expect-CT](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expect-CT)
* [developer.mozilla.org: Feature-Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy)
* [developer.mozilla.org: Public-Key-Pins](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Public-Key-Pins)
* [developer.mozilla.org: Referrer-Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy)
* [developer.mozilla.org: Strict-Transport-Security](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security)
* [developer.mozilla.org: X-Content-Type-Options](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options)
* [developer.mozilla.org: X-Frame-Options](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options)
* [developer.mozilla.org: X-XSS-Protection](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-XSS-Protection)
* [keycdn.com: Hardening Your HTTP Security Headers](https://www.keycdn.com/blog/http-security-headers)
