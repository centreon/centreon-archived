#!/bin/bash

# Script used to update Centreon 3 after a git pull on master

centreonPath="/srv/centreon"

if [ -d $centreonPath ]
then
    cd $centreonPath
    
    # List of modules to reinstall
    # Order is important at the moment, see https://centreon.atlassian.net/browse/WEB-117
    moduleList="centreon-main centreon-administration centreon-configuration centreon-realtime centreon-performance centreon-bam centreon-customview"
    
    for module in $moduleList
    do
	echo "Reinstall module : $module " 
        ./external/bin/centreonConsole core:module:manage:upgrade module=$module
    done
    
    # Copying static
    # Could be improved with a find... no time...
    \cp -a modules/CentreonAdministrationModule/static/centreon-administration/ www/static/
    \cp -a modules/CentreonConfigurationModule/static/centreon-configuration/ www/static/
    \cp -a modules/CentreonBamModule/static/centreon-bam/ www/static/
    \cp -a modules/CentreonPerformanceModule/static/centreon-performance/ www/static/
else
    echo "Cannot find Centreon install path '$centreonPath'"
    exit 1
fi
