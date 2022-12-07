## page: Start

![Screenshot: Backend - Start](/images/usage-02-start-01.png)

(1) **Project**

Select the profile / website to switch to.

(2) **Overview**

The section shows a short overview of the selected profile.

* Name and description - collected items in the database

  * **Searchindex**
    It contains the number of single (html) pages that were spidered as full text.
    The view button brings you to Searchindex -> Status to see / search / analyze the currently spidered content.

  * **Resources**
    It contains the number of all pages, linked media, scripts, css and external links.
    The view button brings you to the Analysis -> Resources to analyze the items of the selected project. You can filter by http statuscode / MIME type and other criteria.

  * **Search terms**
    It contains the number of user searches on your website.
    The view button brings you to Searchindex -> Search terms to view the search statistics of the selected project. You need to implement the website search into your website to get a counter here.

(3) **Contextbox**

Short information about current profile.

* Description of the website
* Screenshot (optional; if added in the profile page) starting url(s)
* excluded urls
* The [profile] button switches to profile to edit its parameters
* The [reindex] button recreates the search index and scans linked resources. 
  Remark: using ahCrawler on a shared hosting this action could run into execution timeout.

(4) **Hints for improvement**

Here are found errors and warnings of different categories are shown. Its details yo can see by clicking the button on the left.