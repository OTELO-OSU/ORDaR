FROM php:5.6.30-apache
RUN apt-get update
RUN apt-get install  php5-curl git -y
RUN a2enmod rewrite 
WORKDIR /var/www/html
RUN git clone https://github.com/OTELO-OSU/ORDaR_OAI-PMH.git



COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf


COPY config.ini /var/www/html/ORDaR_OAI-PMH/Backend/config.ini
