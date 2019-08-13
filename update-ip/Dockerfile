FROM php:7.2-apache

RUN docker-php-ext-install pdo pdo_mysql

COPY html/index.php /var/www/html/index.php
COPY update-ip.ini /etc/update-ip.ini.in
COPY app.sh /usr/local/bin/app.sh

EXPOSE 80

ENTRYPOINT [ "app.sh" ]
