FROM php:5.6.30-apache
RUN apt-get update
RUN apt-get install  php5-curl libssl-dev libssh2-1-dev ssmtp -y
RUN pecl install mongo
RUN pecl install ssh2
RUN a2enmod rewrite 
RUN echo 'extension=mongo.so' >> /usr/local/etc/php/php.ini
RUN echo 'extension=ssh2.so' >> /usr/local/etc/php/php.ini
RUN echo 'sendmail_path = /usr/sbin/ssmtp -t' >> /usr/local/etc/php/php.ini
RUN echo 'upload_max_filesize = 1G' >> /usr/local/etc/php/php.ini
RUN echo 'post_max_size = 1050M' >> /usr/local/etc/php/php.ini
RUN docker-php-ext-install pdo_mysql
RUN mkdir -p /data/applis/ORDaR/Uploads/
COPY . /var/www/html/ORDaR/
RUN chown  www-data:www-data /data/applis/ORDaR/Uploads/
COPY  ./Docker/Apache_PHP/ssmtp.conf  /etc/ssmtp/ssmtp.conf
COPY ./Docker/Apache_PHP/config.ini /var/www/html/ORDaR/Frontend/config.ini
COPY ./Docker/Apache_PHP/AuthDB.ini /var/www/html/ORDaR/Frontend/AuthDB.ini
COPY ./Docker/Apache_PHP/000-default.conf /etc/apache2/sites-enabled/000-default.conf
