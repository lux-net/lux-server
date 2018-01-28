#!/bin/bash

set -e
cd /var/www/lux/
rm -f /var/run/apache2/apache2.pid
rm -rf /var/www/lux/Data/Temporary/
source /etc/apache2/envvars
cp docker/Settings.yaml /var/www/lux/Configuration/Settings.yaml

chmod 755 /var/www/lux/Web
echo "Init Flow..."
exec apache2 -DFOREGROUND
