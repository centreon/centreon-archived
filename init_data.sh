#!/bin/bash


############################################
# Set of centreonConsole commands to init test data
# WARNING: we still need to create manualy
# 1) a first timeperiod (id=1) 24x7 / 00:00-24:00
# 2) a central poller (id=1, ip=127.0.0.1)
############################################


# Notif
./external/bin/centreonConsole centreon-configuration:Command:create params="command_name[Send mail];command_type[1];command_line[mail -s test test]"

############################################
# Check commands
############################################
#1 FIXME Macros not handled by centreonConsole yet
./external/bin/centreonConsole centreon-configuration:Command:create params="command_name[check_centreon_ping];command_type[2];command_line[/usr/lib/nagios/plugins/check_icmp -H \$HOSTADDRESS\$ -n \$_SERVICEPACKETNUMBER\$ -w \$_SERVICEWARNING\$ -c \$_SERVICECRITICAL\$]"
#2
./external/bin/centreonConsole centreon-configuration:Command:create params="command_name[check_host_alive];command_type[2];command_line[/usr/lib/nagios/plugins/check_icmp -H \$HOSTADDRESS\$ -w 3000.0,80% -c 5000.0,100% -p 1]"
#3
./external/bin/centreonConsole centreon-configuration:Command:create params="command_name[check_disk_/];command_type[2];command_line[/usr/lib/nagios/plugins/check_disk -w 70% -c 15% -p /]"
#4
./external/bin/centreonConsole centreon-configuration:Command:create params="command_name[check_eth0];command_type[2];command_line[/usr/lib/nagios/plugins/check_centreon_snmp_traffic -H localhost -i eth0 -n]"
#5
./external/bin/centreonConsole centreon-configuration:Command:create params="command_name[check_cpu_snmp];command_type[2];command_line[/usr/lib/nagios/plugins/check_centreon_snmp_cpu -H $HOSTADDRESS$ -v 2c -C public -d / -n]"
#6
./external/bin/centreonConsole centreon-configuration:Command:create params="command_name[check_swap_snmp];command_type[2];command_line[/usr/lib/nagios/plugins/check_centreon_snmp_remote_storage -H \$HOSTADDRESS\$ -v 2c -C public -d "Swap space" -n]"
#7
./external/bin/centreonConsole centreon-configuration:Command:create params="command_name[check_memory_snmp];command_type[2];command_line[/usr/lib/nagios/plugins/check_centreon_snmp_remote_storage -H \$HOSTADDRESS\$ -v 2c -C public -d "Physical memory" -n]"

############################################
# Service templates
############################################
# 1
./external/bin/centreonConsole centreon-configuration:ServiceTemplate:create params="service_description[generic-service];timeperiod_tp_id[1];service_max_check_attempts[3]"
# 2
./external/bin/centreonConsole centreon-configuration:ServiceTemplate:create params="service_description[ping_lan];timeperiod_tp_id[1];command_command_id[1];service_template_model_stm_id[1]"
# 3
./external/bin/centreonConsole centreon-configuration:ServiceTemplate:create params="service_description[memory];timeperiod_tp_id[1];command_command_id[7]"

############################################
# Host templates
############################################
# 1
./external/bin/centreonConsole centreon-configuration:HostTemplate:create params="host_name[generic-host];host_activate[1];timeperiod_tp_id[1];host_max_check_attempts[3];command_command_id[3]"
# 2

# 3


############################################
# Hosts
############################################
# 2, with an host template and inheriting max check attempts 
./external/bin/centreonConsole centreon-configuration:Host:create params="host_name[Host1];host_activate[1];host_address[10.30.2.38];host_hosttemplates[1];poller_id[1]"
# 3, without host template
./external/bin/centreonConsole centreon-configuration:Host:create params="host_name[Centreon-export];host_activate[1];host_max_check_attempts[5];host_address[10.30.2.87];poller_id[1]"


############################################
# Services
############################################
#1

