#!/usr/bin/env bash
#----
## @Synopsis	Install script for CentPlugins
## @Copyright	Copyright 2008, Guillaume Watteeux
## @license	GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## Install script for CentPlugins
#----
# install script for CentPlugins
#################################
# SVN: $Id$

echo -e "\n$line"
echo -e "\t$(gettext "Starting Centreon Plugins Installation")"
echo -e "$line"

###### Check disk space
check_tmp_disk_space
if [ "$?" -eq 1 ] ; then
  if [ "$silent_install" -eq 1 ] ; then
    purge_centreon_tmp_dir "silent"
  else
    purge_centreon_tmp_dir
  fi
fi

## Where is nagios_pluginsdir
locate_plugindir
locate_centreon_plugins

## Locale for sed
locate_centplugins_tmpdir

## check centreon user and group
check_centreon_user
check_centreon_group

###### Post Install
#################################
createCentPluginsInstallConf
