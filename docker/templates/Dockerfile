# TARGET: docker/server/Dockerfile
#
# {{generator}}
#
FROM php:8.1-apache

# braucht der Crawler nicht:
# RUN a2enmod rewrite

RUN apt-get update && apt-get install -y {{APP_APT_PACKAGES}}

# WORKDIR /var/www/{{APP_NAME}}/public_html
WORKDIR {{APPROOT}}


COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
# RUN install-php-extensions gd pdo_mysql bcmath zip intl opcache
RUN install-php-extensions {{APP_PHP_MODULES}}


# create shared group id (local machine and inside container)
# RUN addgroup --gid {{WEBGID}} sharedgroup
# RUN usermod -a -G sharedgroup {{WEBUSER}}
# USER {{WEBUSER}}