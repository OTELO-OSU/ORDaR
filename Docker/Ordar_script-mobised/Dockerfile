FROM php:5.6.30-cli
RUN apt-get update
RUN apt-get install  php5-curl libssl-dev git cron -y
RUN pecl install mongo
RUN echo 'extension=mongo.so' >> /usr/local/etc/php/php.ini
RUN docker-php-ext-install zip 
RUN git clone https://x-token-auth:ACCESSTOKENHERE@bitbucket.org/arnouldpy/ordar_scripts-mobised.git
COPY config.ini /ordar_scripts-mobised/src/config/config.ini
COPY start.sh /ordar_scripts-mobised/src/start.sh
RUN chmod +x /ordar_scripts-mobised/src/start.sh
RUN rm -R /ordar_scripts-mobised/vendor/katzgrau/* && cd ordar_scripts-mobised && php composer.phar update  --prefer-source


COPY cronjob /etc/cron.d/cronjob
COPY startup.sh /startup.sh
RUN crontab /etc/cron.d/cronjob
RUN chmod +x /startup.sh
ENTRYPOINT /startup.sh
