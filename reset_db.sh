#!/bin/bash

# Crappy reset table script, temporary, never use it useless you're part of dev team

/etc/init.d/centengine stop
/etc/init.d/cbd stop
rm -f /var/lib/centreon-broker/*

mysql -u root -e "drop database centreon;"
mysql -u root -e "create database centreon;"

external/bin/centreonConsole core:internal:install
external/bin/centreonConsole core:module:manage:install --module=centreon-broker
external/bin/centreonConsole core:module:manage:install --module=centreon-engine
external/bin/centreonConsole core:module:manage:install --module=centreon-performance 
external/bin/centreonConsole core:module:manage:install --module=centreon-bam


