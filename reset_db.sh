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
sed -i -e 's/<broker_id>.*<\/broker_id>/<broker_id>2<\/broker_id>/' /etc/centreon-broker/poller-module.xml
sed -i -e 's/<broker_name>.*<\/broker_name>/<broker_name>poller-module-2<\/broker_name>/' /etc/centreon-broker/poller-module.xml

sed -i -e 's/<name>central-broker-extcommands-broker.*<\/name>/<name>central-broker-extcommands-broker-poller-module-1<\/name>/' /etc/centreon-broker/central-broker.xml

sed -i -e 's/<name>central-broker-extcommands-engine.*<\/name>/<name>central-broker-extcommands-engine-poller-module-1<\/name>/' /etc/centreon-broker/central-broker.xml

sed -i -e 's/<name>central-broker-cfg-engine.*<\/name>/<name>central-broker-cfg-engine-poller-module-1<\/name>/' /etc/centreon-broker/central-broker.xml

sed -i -e 's/<name>central-broker-cfg-broker.*<\/name>/<name>central-broker-cfg-broker-poller-module-1<\/name>/' /etc/centreon-broker/central-broker.xml

/etc/init.d/cbd start
/etc/init.d/centengine start
