FROM php:5.6.30-cli
RUN apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 0C49F3730359A14518585931BC711F9BA15703C6
RUN apt update ; exit 0
RUN apt-get install  php5-curl libssl-dev cron -y
RUN pecl install mongo
RUN echo 'extension=mongo.so' >> /usr/local/etc/php/php.ini
COPY Check_Embargoed_access.php /root/Check_Embargoed_access.php
COPY cronjob /etc/cron.d/cronjob
COPY startup.sh /startup.sh
RUN crontab /etc/cron.d/cronjob
RUN chmod +x /startup.sh
ENTRYPOINT /startup.sh
