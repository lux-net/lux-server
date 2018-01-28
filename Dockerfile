FROM ubuntu:14.04
RUN apt-get upgrade
RUN apt-get update

RUN apt-get install -y software-properties-common python-software-properties
RUN add-apt-repository -y ppa:ondrej/php
RUN apt-get update

RUN apt-get install -y apache2 acl php7.0 php7.0-mbstring php7.0-mcrypt php7.0-mysql php7.0-xml php7.0-soap php7.0-curl php7.0-gd php7.0-bcmath php7.0-zip --force-yes

COPY docker/lux_apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/Settings.yaml /root/Settings.yaml
COPY docker/lux_init.sh /usr/local/bin/init.sh

RUN a2enmod headers rewrite php7.0

RUN sed -i -- 's/display_errors = Off/display_errors = On/g' /etc/php/7.0/apache2/php.ini \
&& sed -i -- 's/display_startup_errors = Off/display_startup_errors = On/g' /etc/php/7.0/apache2/php.ini \
&& sed -i -- 's/;date.timezone =/date.timezone = "America\/Bahia"/g' /etc/php/7.0/apache2/php.ini \
&& sed -i -- 's/memory_limit = 128M/memory_limit = 1024M/g' /etc/php/7.0/apache2/php.ini \
&& sed -i -- 's/;date.timezone =/date.timezone = "America\/Bahia"/g' /etc/php/7.0/cli/php.ini

COPY docker/composer-installer.php /tmp/composer-installer.php
RUN php /tmp/composer-installer.php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/lux

COPY . .

ENTRYPOINT ["/bin/bash"]
CMD ["/usr/local/bin/init.sh"]

EXPOSE 80