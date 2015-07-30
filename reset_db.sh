#!/bin/bash

# Crappy reset table script, temporary, never use it useless you're part of dev team

/etc/init.d/centengine stop
/etc/init.d/cbd stop
rm -f /var/lib/centreon-engine/status.sav
rm -f /var/lib/centreon-broker/*
rm -f /etc/centreon-engine/objects.d/resources/*
rm -f /etc/centreon-broker/correlation*

mysql -u root -e "drop database centreon;"
mysql -u root -e "create database centreon;"

php external/bin/centreonConsole core:internal:install
php external/bin/centreonConsole core:module:manage:install --module=centreon-broker
php external/bin/centreonConsole core:module:manage:install --module=centreon-engine
php external/bin/centreonConsole core:module:manage:install --module=centreon-performance 
php external/bin/centreonConsole core:module:manage:install --module=centreon-bam

sed -i -e 's/<poller_id>.*<\/poller_id>/<poller_id>1<\/poller_id>/' /etc/centreon-broker/poller-module.xml
sed -i -e 's/<poller_name>.*<\/poller_name>/<poller_name>Central<\/poller_name>/' /etc/centreon-broker/poller-module.xml
sed -i -e 's/<broker_id>.*<\/broker_id>/<broker_id>3<\/broker_id>/' /etc/centreon-broker/poller-module.xml
sed -i -e 's/<broker_name>.*<\/broker_name>/<broker_name>poller-module-3<\/broker_name>/' /etc/centreon-broker/poller-module.xml

/etc/init.d/cbd start
/etc/init.d/centengine start
