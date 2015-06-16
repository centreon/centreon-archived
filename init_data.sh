#!/bin/bash


############################################
# Set of centreonConsole commands to init test data
# No need for a timeperiod (default = all the time ~24x7)
############################################

echo " ==== Creating pollers ==== "
./external/bin/centreonConsole centreon-configuration:poller:create --name=central --template=Central --ip-address='127.0.0.1' --engine-init-script='/etc/init.d/centengine' --engine-binary='/usr/sbin/centengine' --engine-modules-dir='/usr/lib64/centreon-engine/' --engine-conf-dir='/etc/centreon-engine/' --engine-logs-dir='/var/log/centreon-engine/' --engine-var-lib-dir='/var/lib/centreon-engine/' --broker-conf-dir='/etc/centreon-broker/' --broker-modules-dir='/usr/share/centreon/lib/centreon-broker/' --broker-data-dir='/var/lib/centreon-broker' --broker-logs-dir='/var/log/centreon-broker/' --broker-cbmod-dir='/usr/lib64/nagios/' --broker-init-script='/etc/init.d/cbd'
#./external/bin/centreonConsole centreon-configuration:poller:create --name=poller6 --template=Poller --ip-address="127.0.0.1" --engine-init-script='/etc/init.d/centengine' --engine-binary='/usr/sbin/centengine' --engine-modules-dir='/usr/lib64/centreon-engine/' --engine-conf-dir='/etc/centreon-engine/' --engine-logs-dir='/var/log/centreon-engine/' --engine-var-lib-dir='/var/lib/centreon-engine/' --broker-conf-dir='/etc/centreon-broker/' --broker-modules-dir='/usr/share/centreon/lib/centreon-broker/' --broker-data-dir='/var/lib/centreon-broker' --broker-logs-dir='/var/log/centreon-broker/' --broker-cbmod-dir='/usr/lib64/nagios/' --broker-init-script='/etc/init.d/cbd' --broker-central-ip="10.30.2.34"

echo " ==== Creating notif commands ==== "
./external/bin/centreonConsole centreon-configuration:Command:create --command-name='Send mail' --command-type=1 --command-line='mail -s test test'

############################################
# Check commands
############################################
echo " ==== Creating check commands ==== "
./external/bin/centreonConsole centreon-configuration:Command:create --command-name='check_centreon_ping' --command-type=2 --command-line='$USER1$/check_icmp -H $HOSTADDRESS$ -n $_SERVICEPACKETNUMBER$ -w $_SERVICEWARNING$ -c $_SERVICECRITICAL$'
# TODO add check host alive
./external/bin/centreonConsole centreon-configuration:Command:create --command-name='OS-Linux-SNMP-load' --command-type=2 --command-line='$USER1$/centreon-plugins/centreon_plugins.pl --plugin=os::linux::snmp::plugin --mode=load --hostname=$HOSTADDRESS$ --snmp-version=$_HOSTSNMPVERSION$ --snmp-community=$_HOSTSNMPCOMMUNITY$ $_HOSTSNMPEXTRAOPTIONS$ --warning=$_SERVICEWARNING$ --critical=$_SERVICECRITICAL$ $_SERVICEEXTRAOPTIONS$'
./external/bin/centreonConsole centreon-configuration:Command:create --command-name='OS-Linux-SNMP-memory' --command-type=2 --command-line='$USER1$/centreon-plugins/centreon_plugins.pl --plugin=os::linux::snmp::plugin --mode=memory --hostname=$HOSTADDRESS$ --snmp-version=$_HOSTSNMPVERSION$ --snmp-community=$_HOSTSNMPCOMMUNITY$ $_HOSTSNMPEXTRAOPTIONS$ --warning=$_SERVICEWARNING$ --critical=$_SERVICECRITICAL$ $_SERVICEEXTRAOPTIONS$'
./external/bin/centreonConsole centreon-configuration:Command:create --command-name='OS-Linux-SNMP-cpu' --command-type=2 --command-line='$USER1$/centreon_plugins.pl --plugin=os::linux::snmp::plugin --mode=cpu --hostname=$HOSTADDRESS$ --snmp-version=$_HOSTSNMPVERSION$ --snmp-community=$_HOSTSNMPCOMMUNITY$ $_HOSTSNMPEXTRAOPTIONS$ --warning="$_SERVICEWARNING$"  --critical="$_SERVICECRITICAL$" $_SERVICEEXTRAOPTIONS$'
./external/bin/centreonConsole centreon-configuration:Command:create --command-name='OS-Linux-SNMP-swap' --command-type=2 --command-line='$USER1$/centreon_plugins.pl --plugin=os::linux::snmp::plugin --mode=swap --hostname=$HOSTADDRESS$ --snmp-version=$_HOSTSNMPVERSION$ --snmp-community=$_HOSTSNMPCOMMUNITY$ $_HOSTSNMPEXTRAOPTIONS$ --warning="$_SERVICEWARNING$" --critical="$_SERVICECRITICAL$" $_SERVICEEXTRAOPTIONS$'
./external/bin/centreonConsole centreon-configuration:Command:create --command-name='OS-Linux_SNMP-traffic-name' --command-type=2 --command-line='$USER1$/centreon_plugins.pl --plugin=os::linux::snmp::plugin --mode=traffic --hostname=$HOSTADDRESS$ --snmp-version=$_HOSTSNMPVERSION$ --snmp-community=$_HOSTSNMPCOMMUNITY$ $_HOSTSNMPEXTRAOPTIONS$ --interface="$_SERVICEINTERFACENAME$" --name --warning-in="$_SERVICEWARNINGIN$" --critical-in="$_SERVICECRITICALIN$" --warning-out="$_SERVICEWARNINGOUT$" --critical-out="$_SERVICECRITICALOUT$" $_SERVICEEXTRAOPTIONS$'
./external/bin/centreonConsole centreon-configuration:Command:create --command-name='OS-Linux-SNMP-disk-name' --command-type=2 --command-line='$USER1$/centreon_plugins.pl --plugin=os::linux::snmp::plugin --mode=storage --hostname=$HOSTADDRESS$ --snmp-version=$_HOSTSNMPVERSION$ --snmp-community=$_HOSTSNMPCOMMUNITY$ $_HOSTSNMPEXTRAOPTIONS$ --storage "$_SERVICEDISKNAME$" --name --display-transform-src="$_SERVICETRANSFORMSRC$" --display-transform-dst="$_SERVICETRANSFORMDST$" --warning="$_SERVICEWARNING$" --critical="$_SERVICECRITICAL$" $_SERVICEEXTRAOPTIONS$'

############################################
# Service templates
############################################
echo " ==== Creating service templates ==== "
# 1
# TODO cannot set service_max_check_attempts = 3 here during creation, possib le for update
./external/bin/centreonConsole centreon-configuration:ServiceTemplate:create --description='generic-service' 
./external/bin/centreonConsole centreon-configuration:ServiceTemplate:update --service-template='generic-service' --max-check-attempts=3

./external/bin/centreonConsole centreon-configuration:ServiceTemplate:create --description='ping-lan' --alias='ping' --template-model-stm='generic-service' --command='check-centreon-ping'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='ping-lan' --name='WARNING' --value='200,20%'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='ping-lan' --name='CRITICAL' --value='400,50%'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='ping-lan' --name='PACKETNUMBER' --value='5'

./external/bin/centreonConsole centreon-configuration:ServiceTemplate:create --description='OS-Linux-SNMP-load' --alias='load' --template-model-stm='generic-service' --command='os-linux-snmp-load'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='os-linux-snmp-load' --name='WARNING' --value='4,3,2'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='os-linux-snmp-load' --name='CRITICAL' --value='6,5,4'

./external/bin/centreonConsole centreon-configuration:ServiceTemplate:create --description='OS-Linux-SNMP-cpu' --alias='cpu' --template-model-stm='generic-service' --command='os-linux-snmp-cpu'
./external/bin/centreonConsole centreon-configuration:ServiceTemplate:update --service-template='os-linux-snmp-cpu' --domain='cpu'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='os-linux-snmp-cpu' --name='WARNING' --value='80'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='os-linux-snmp-cpu' --name='CRITICAL' --value='90'

./external/bin/centreonConsole centreon-configuration:ServiceTemplate:create --description='OS-Linux-SNMP-memory' --alias='memory' --template-model-stm='generic-service' --command='os-linux-snmp-memory'
./external/bin/centreonConsole centreon-configuration:ServiceTemplate:update --service-template='os-linux-snmp-memory' --domain='memory'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='os-linux-snmp-memory' --name='WARNING' --value='80'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='os-linux-snmp-memory' --name='CRITICAL' --value='90'

./external/bin/centreonConsole centreon-configuration:ServiceTemplate:create --description='OS-Linux-SNMP-swap' --alias='swap' --template-model-stm='generic-service' --command='os-linux-snmp-swap'
./external/bin/centreonConsole centreon-configuration:ServiceTemplate:update --service-template='os-linux-snmp-swap' --domain='swap'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='os-linux-snmp-swap' --name='WARNING' --value='10'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='os-linux-snmp-swap' --name='CRITICAL' --value='30'

./external/bin/centreonConsole centreon-configuration:ServiceTemplate:create --description='OS-Linux-SNMP-traffic-name' --alias='traffic-name' --template-model-stm='generic-service' --command='os-linux-snmp-traffic-name'
./external/bin/centreonConsole centreon-configuration:ServiceTemplate:update --service-template='os-linux-snmp-traffic' --domain='traffic'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='os-linux-snmp-traffic-name' --name='WARNING' --value='80'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='os-linux-snmp-traffic-name' --name='CRITICAL' --value='90'

./external/bin/centreonConsole centreon-configuration:ServiceTemplate:create --description='OS-Linux-SNMP-disk-name' --alias='disk-name' --template-model-stm='generic-service' --command='os-linux-snmp-disk-name'
./external/bin/centreonConsole centreon-configuration:ServiceTemplate:update --service-template='os-linux-snmp-disk-name' --domain='filesystem'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='os-linux-snmp-disk-name' --name='WARNING' --value='80'
./external/bin/centreonConsole  centreon-configuration:ServiceTemplate:addMacro --service-template='os-linux-snmp-disk-name' --name='CRITICAL' --value='90'

############################################
# Host templates
############################################
echo " ==== Creating host templates ==== "
./external/bin/centreonConsole centreon-configuration:HostTemplate:create --name='generic-host' 
./external/bin/centreonConsole centreon-configuration:HostTemplate:update --host-template='generic-host' --max-check-attempts=3 --service-templates='ping-lan'
./external/bin/centreonConsole centreon-configuration:HostTemplate:addMacro --host-template='generic-host' --name='WARNING' --value='3000,80%'
./external/bin/centreonConsole centreon-configuration:HostTemplate:addMacro --host-template='generic-host' --name='CRITICAL' --value='5000,100%'
./external/bin/centreonConsole centreon-configuration:HostTemplate:addMacro --host-template='generic-host' --name='PACKETNUMBER' --value='1'

./external/bin/centreonConsole centreon-configuration:HostTemplate:create --name='OS-Linux-SNMP'  --host-templates='generic-host' --service-templates='os-linux-snmp-cpu' --service-templates='os-linux-snmp-load' --service-templates='os-linux-snmp-memory' --service-templates='os-linux-snmp-swap'

############################################
# Hosts
############################################
echo " ==== Creating hosts ==== "
./external/bin/centreonConsole centreon-configuration:Host:create --name='Centreon-export' --address='10.30.2.87' --host-templates='generic-host' --poller='central'
./external/bin/centreonConsole centreon-configuration:Host:create --name='CES3-RWE-PP' --address='10.30.2.127' --host-templates='generic-host' --poller='central'
./external/bin/centreonConsole centreon-configuration:Host:create --name='CES3-QDE-PP-CES22' --address='10.50.1.84' --host-templates='generic-host' --poller='central'
./external/bin/centreonConsole centreon-configuration:Host:create --name='CES3-QDE-PP-CES3' --address='10.50.1.85' --host-templates='generic-host' --poller='central'


############################################
# Services
############################################
echo " ==== Creating services ==== "
#1

