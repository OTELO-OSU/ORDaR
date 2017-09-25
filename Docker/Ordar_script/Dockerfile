FROM php:5.6.30-cli
RUN apt-get update
RUN apt-get install  php5-curl libssl-dev git cron -y
RUN pecl install mongo
RUN echo 'extension=mongo.so' >> /usr/local/etc/php/php.ini
RUN docker-php-ext-install zip 
RUN git clone https://x-token-auth:ACCESSTOKENHERE@bitbucket.org/arnouldpy/ordar_scripts.git
COPY config.ini /ordar_scripts/src/config/config.ini
COPY start.sh /ordar_scripts/src/start.sh
RUN chmod +x /ordar_scripts/src/start.sh
RUN rm -R /ordar_scripts/vendor/katzgrau/* && cd ordar_scripts && php composer.phar update  --prefer-source


COPY cronjob /etc/cron.d/cronjob
COPY startup.sh /startup.sh
RUN crontab /etc/cron.d/cronjob
RUN chmod +x /startup.sh
ENTRYPOINT /startup.sh
