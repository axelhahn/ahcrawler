## Page: Link checker

### Overview

The link checker shows errors and warning of all your set internal + external links, linked media, documents, javascripts, css.
A low count of invalid links is relevant for SEO optimization.

The **tiles** show ...

* **Age of last scan**\
... to know how old the given information is.

* **Count of resources**\
Total count of analyzed resources: html pages including all media, documents, javascripts, css, linked external pages.

* **Redirects only**\
Resources (mostly links) that point to another location.

* **todo**\
Count of resources that aren't analyzed yet. It is zero if the crawler is finished. If it is non zero the the crawler is still in progress or stopped before it finished.

* **Http errors**\
Found errors in links and resources. This group contains unreachable resources with invalid domain or certificate and links that returned an http error code or invalid http statuscode.
You should check / fix all of them.

* **Http warnings**\
Found warnings. This group contains resources that returned an http warning code.
You should check them after fixing the http errors (see last tile).

* **Http ok**\
Just for comparison: count of resources that are OK.

All clickable tiles have a different look. A click on one jumps to a page section with more details.

### Sections by http status

![Screenshot: Backend - Analysis - Link checker](/images/usage-03-analysis-linkcheck-warnings.png)

Each section has this scructure:

(1)
A headline with the total count of found items in the brackets.
Remark: This headline is in the navigation on the left too. So the navigation gives you a short status about errors, warnings and good items too.

(2)
Introduction text.

(3)
A list of tiles that show the count grouped by the http status code.
The tiles are clickable. With it you can jump to a list of resources that match this statuscode including the analysis where they are refererenced that you could fix something.

(4)
The bar chart shows the distribution of status codes and their values.

(5)
The legend shows a description text for each found http status code: what does it mean and how to handle it as a webmaster.

### Matching resources

If you click on a tile with an http status code you switch to a list of all resources that match the selected status code. Each resource is spearated with a box.
Each resource is displayed in the way: a colored status code + location (internal|external) + type + url

![Screenshot: Backend - Analysis - Link checker](/images/usage-03-analysis-by-httpcode.png)

(1)
The resource with a short description: status code + internal|exterenal + type + url

(2) For redirects only: where does it redirect to?
If the next hop is a redirect again, then all the hops will be shown as intended item. There is no limit of a maximum count of redirects.
If one redirects points back to one of the existing hops then this loop will be detected.
Another hint is shown if the same url switches the protocol from http to https.

(3) referenced in ... with the count of references in brackets.
Here is a list of all resources that point to the resource (1).

(4)
Text links with the urls point to a detail page of a resource that show more metadata.

(5)
Add the selected url in the deny list. You get a dialog to modify the new deny entry.
Adding an entry to the deny list has an impact for the next scan(s) - not for the current session. All urls (of links, images, ...) matching one of the text lines in the deny list (those are regex) will be ignored.
The beginning ^ marks the search from the beginning of the url.
To allow just the given protocol leave ^http:// or ^https://. To allow both of them use ^http[s]*:// %s.
The $ at the end means that a url must end there. You can remove it to include urls with additional characters too.
The deny list is specific for each website and can be changed in their profile settings.

(6)
The buttons open the url in a new tab.

