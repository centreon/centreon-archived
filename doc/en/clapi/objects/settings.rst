========
Settings
========

Overview
--------

Object name: **Settings**

Show
----

In order to list editable settings, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SETTINGS -a show
  parameter;value
  broker;ndo
  broker_correlator_script;
  centstorage;1
  debug_auth;0
  debug_ldap_import;0
  debug_nagios_import;0
  debug_path;/var/log/centreon/
  debug_rrdtool;0
  enable_autologin;1
  enable_gmt;0
  enable_logs_sync;1
  enable_perfdata_sync;1
  gmt;1
  interval_length;60
  mailer_path_bin;/bin/mail
  nagios_path_img;/usr/share/nagios/html/images/logos/
  perl_library_path;/usr/local/lib
  rrdtool_path_bin;/usr/bin/rrdtool
  snmpttconvertmib_path_bin;/usr/share/centreon/bin/snmpttconvertmib
  snmptt_unknowntrap_log_file;snmptrapd.log  


Setparam
--------

If you want to change a specific parameter of a Vendor, use the **SETPARAM** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SETTINGS -a setparam -v ";" 

Arguments are composed of the following columns:

======== =========================
Order	 Column description
======== =========================
1	     Parameter name

2	     Parameter value
======== =========================

Parameters that you may change are:

=========================== ===================================================== ================================================
Column                      Description                                           Possible values and examples
=========================== ===================================================== ================================================
broker                      Broker engine                                         'broker' for Centreon Broker, 'ndo' for NDOUtils

broker_correlator_script    This parameter is misleading (subject to changes)     i.e: /etc/init.d/cbd
                            Refers to the Centreon Broker init script

centstorage                 Enable/disable CentStorage                            Enable: '1', Disable: '0'

debug_auth                  Enable/disable authentication debug                   Enable: '1', Disable: '0'

debug_ldap_import           Enable/disable LDAP debug                             Enable: '1', Disable: '0'

debug_nagios_import         Enable/disable Nagios configuration import            Enable: '1', Disable: '0'

debug_path                  Debug log files directory                             i.e: /var/log/centreon/

debug_rrdtool               Enable/disable RRDTool debug                          Enable: '1', Disable: '0'

enable_autologin            Enable/disable autologin                              Enable: '1', Disable: '0'

enable_gmt                  Enable/disable GMT management                         Enable: '1', Disable: '0'

enable_logs_sync            Enable/disable CentCore log synchronization           Enable: '1', Disable: '0'
                            (not necessary when using Centreon Broker)

enable_perfdata_sync        Enable/disable Centcore PerfData synchronization      Enable: '1', Disable: '0'
                            (not necessary when using Centreon Broker)

gmt                         GMT timezone of monitoring system                     i.e: 2 (for GMT+2)

interval_length             Monitoring interval length in seconds                 i.e: 120
                            (default: 60)

mailer_path_bin             Mail client bin path                                  i.e: /bin/mail

nagios_path_img             Nagios image path                                     i.e: /usr/share/nagios/html/images/logos/

perl_library_path           Perl library path                                     i.e: /usr/local/lib

rrdtool_path_bin            RRDTool bin path                                      i.e: /usr/bin/rrdtool

snmpttconvertmib_path_bin   SNMPTT mib converter bin path                         i.e: /usr/share/centreon/bin/snmpttconvertmib

snmptt_unknowntrap_log_file SNMPTT unknown trap log file                          i.e: snmptrapd.log
=========================== ===================================================== ================================================
