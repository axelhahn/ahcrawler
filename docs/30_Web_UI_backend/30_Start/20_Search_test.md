##  Page: Search test

Here you can search in the created search index to find out the behaviour of a search placed on your website. The search terms entered here in the backend won't be stored (tracked) in the database.

### Search form

The search form in the backend automatically adds the search for subfolders and languages from the profile settings of the selected project.

With this form you can simulate a search of your frontend. 

The differences are: 

* the search here is excluded from the search statistics
* there is an additional feature: you can search not in the visible text content only but in the html source too. \
  Example: you can search for linked email addresses by using "mailto:" 

![Screenshot: Backend - Start](/images/usage-02-start-profile-searchfrontend.png)

### Search results

You get a result page with some debugging information, raw data from the database and the details of calculation of the ranking mechanism that defines the position in the search results.

The multiplicators for the ranking can be changed in the global settings.

With the linked url you come to a detail page.

Another button opens the url in a new tab.