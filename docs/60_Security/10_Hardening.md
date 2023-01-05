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

### Basic authentication on backend

In the backend you can setup exactly one backend user.

If you want to give more than 1 user access to the backend then you can restrict the access to the backend with basic authentication. Then you can setup users in htauth users in files or database backends.

**IMPORTANT**: restrict the access to the location */backend/index.php* - a single file only.

```txt
   <Location "/backend/index.php">
     Require valid-user
     AuthType Basic
     ...
   </Location>
```
