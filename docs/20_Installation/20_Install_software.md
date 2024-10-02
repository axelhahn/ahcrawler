## Installation

In your browser open <http://localhost/ahcrawler/>

You will be guided to make an initial setup... and then in a second step to create a profile for a website.

### Select a language

![Screenshot: select language](/images/installer-01-language.png)

### Check requirements

If a requirement is missed you get a warning message.
![Screenshot: Check requirements](/images/installer-02-requirements.png)

### Setup database connection

The sqlite is for small tests or small websites only.
I highly recommend to use Mysql/ MariaDb - therefore you need to create a database and a database user.
You see if the required pdo module is installed or not.
Enter the connection data.

![Screenshot: Setup database](/images/installer-03-database.png)

If you continue the connection will be checked.

**Hint** when using docker with Mariadb:

Have a look to the docker environment file `docker/.env` too. The default parameters are:

* Server: ahcrawler-db
* Port: leave empty
* Scheme: ahcrawler
* Username: ahcrawler
* Password: mypassword
* Charset: utf8

![Screenshot: Done](/images/installer-04-done.png)
