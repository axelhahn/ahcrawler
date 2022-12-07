## Page: SSL check

When opening this page the check to the to the certificate will be made (live check).
If your page uses plain http you get a red box that no encryption is in use.

For SSL encrypted websites there is a check for its certificate. It is green if your certificate is fine.
It shows warnings if the certificate is valid 30 days or less.
It shows errors if the certificate is out of date or the domain name is not incuded in the DNS names.

![Screenshot: Backend - Analysis - SSL cert infos](/images/usage-03-analysis-sslcheck-certinfos.png)

### General information

In the table below follow basic information to the certificate.

*  **Common Name**\
Default domain for the certificate.
Remark: A certificate can contain other valid domain names. See DNS names below

* **Type of certificate**\
It can have the value "Business SSL" or "Extended Validation".
"Extended Validation" should be used for websites with financial transactions (like shops, banks). With this type of certificate the owner of a domain is part of the certificate meta data and can be verified.

* **Information about domain owner**:\
Read information from CN field (common name)

* **Issuer**\
Company that issued the certificate.

* **CA**\
Name of the root certificate (of issuer) that you must trust to trust the domain certificate too.

* **Certificate chain**\
Check if the certificate configuration on server deliveres host certificate, intermediate certificate and root certificate.
If it should fail then the most common browsers have no problem with a missing piece - they will download it and are able to verfy it. But CLI tools (eg. wget, curl) or programmed API requests will fail.
It is highly recommended to configure the full chain.

* **DNS names**\
List of DNS names for which (sub-) domains the certificate is valid too.
This is optional. A certificate can be valid for a single domain ... or many ... or all subdomains (wildcard certificate).

* **Encryption**\
Encryption type and key length.

* **valid from / to**\
Time range when the certificate is valid.

* **still valid (in days)**\
The time left up to the end of lifetime of the certificate.
If it is less than 30 days the status changes to warning.

### Encryption levels

This is for informatin- The certificate levels and their usage are listed here.
The level of the currently used certificate is marked.

### Raw data

Click the button to show/ hide readable details in JSON syntax. It's quite plain.

### Check non https resources

You get an overview about used all non SSL encrypted elements if your website uses SSL. If you have a website running with https then browsers can show a warning if you embed unencrypted resources.

By default you get a list of embedded non SSL elements in your website that could be the reason that a browser hides them because of usage of mixed content.

Do you ever try to navigate through all your webpages to find where a browser warns for mixed content? Here you could get all http only resources. If there are some then click one to see where it is used.
With a click on the other tile you get all links that still use http (and no https).

![Screenshot: Backend - Analysis - unencrypted resources](/images/usage-03-analysis-sslcheck-unencrypted-ressources.png)

