#!/bin/bash

# Configure the update-ip.ini file
cp /etc/update-ip.ini.in /etc/update-ip.ini

sed -i "s/@MYSQL_HOST@/${MYSQL_HOST}/" /etc/update-ip.ini
sed -i "s/@MYSQL_USER@/${MYSQL_USER}/" /etc/update-ip.ini
sed -i "s/@MYSQL_PASSWORD@/${MYSQL_PASSWORD}/" /etc/update-ip.ini
sed -i "s/@MYSQL_DATABASE@/${MYSQL_DATABASE}/" /etc/update-ip.ini
sed -i "s/@API_KEY@/${API_KEY}/" /etc/update-ip.ini

docker-php-entrypoint apache2-foreground
