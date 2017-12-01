FROM php:5.6.30-cli
RUN apt-get update
RUN apt-get install  php5-curl libssl-dev git cron -y
RUN pecl install mongo
RUN echo 'extension=mongo.so' >> /usr/local/etc/php/php.ini
RUN docker-php-ext-install zip 
RUN git clone https://x-token-auth:60pKSt-oiouq2Ie7AP5tSM16STN4veexygqvKrcX5e7xUffUPTP3k0xMk4RBGp7vNxFhGhApDoHNkrfGruE=@bitbucket.org/arnouldpy/harvester-geo-stations.git
COPY config.ini /harvester-geo-stations/src/config/config.ini
COPY start.sh /harvester-geo-stations/src/start.sh
RUN chmod +x /harvester-geo-stations/src/start.sh
RUN rm -R /harvester-geo-stations/vendor/katzgrau/* && cd harvester-geo-stations && php composer.phar update  --prefer-source


COPY cronjob /etc/cron.d/cronjob
COPY startup.sh /startup.sh
RUN crontab /etc/cron.d/cronjob
RUN chmod +x /startup.sh
ENTRYPOINT /startup.sh
