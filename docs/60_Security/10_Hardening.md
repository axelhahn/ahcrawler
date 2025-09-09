## Introduction to hardening

This section is important if you use the ahCrawler on productive systems.

### Deny access to a few directories

In the named directories below exist .htaccess files for apache httpd already exist. But it is better to put the deny rules into your vhost configuration.

```txt
   <Location "/bin">
     Require all denied
   </Location>

   <Location "/config">
     Require all denied
   </Location>

   <Location "/data">
     Require all denied
   </Location>

   <Location "/tmp">
     Require all denied
   </Location>
```
