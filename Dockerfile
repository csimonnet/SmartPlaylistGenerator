FROM ubuntu:16.04

WORKDIR /var/www/smart-playlist-generator

RUN apt-get update && apt-get install -y apache2
RUN apt-get update && apt-get -y install \
                          apache2 php7.0 php7.0-mysql libapache2-mod-php7.0 curl php7.0-xml php-curl

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

EXPOSE 80

ADD . /var/www/smart-playlist-generator

RUN mkdir /var/www/smart-playlist-generator/var/cache
RUN mkdir /var/www/smart-playlist-generator/var/logs
RUN mkdir /var/www/smart-playlist-generator/var/sessions

RUN chmod -R 777 /var/www/smart-playlist-generator/var/cache /var/www/smart-playlist-generator/var/logs /var/www/smart-playlist-generator/var/logs

ADD ./docker-conf/apache.conf /etc/apache2/sites-enabled/000-default.conf
ADD ./docker-conf/php.ini /etc/php/7.0/apache2/php.ini

CMD /usr/sbin/apache2ctl -D FOREGROUND
