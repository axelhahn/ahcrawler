## Introduction to hardening

This section is important if you use the ahCrawler on productive systems.

### Deny access to a few directories

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

If you want to give more than 1 user access to the backend then you can restrict the access to the backend with basic authentication. Then you can setup users in htauth users in files or database backends.

IMPORTANT: restrict the access to the location /backend/index.php.

```txt
   <Location "/backend/index.php">
     Require valid-user
     AuthType Basic
     ...
   </Location>
```
