#!/bin/bash

# Crappy reset table script, temporary, never use it useless you're part of dev team

mysql -u root -e "drop database centreon;"
mysql -u root -e "create database centreon;"

external/bin/centreonConsole core:internal:install
external/bin/centreonConsole core:module:manage:install moduleName=centreon-broker
external/bin/centreonConsole core:module:manage:install moduleName=centreon-engine
external/bin/centreonConsole core:module:manage:install moduleName=centreon-performance 

# Purge previously generated conf
rm -rf /tmp/engine/generate/*
rm -rf /tmp/engine/apply/*
rm -rf /tmp/broker/apply/*
rm -rf /tmp/broker/generate/*

