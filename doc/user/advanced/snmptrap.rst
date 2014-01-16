.. _snmptrap:

**********
SNMP TRAPS
**********

Overview
=========

Centreon has evolved with a eaysiest way to handle SNMP traps.  
Some advantages of the new system:

   * No more 'snmptt'
   * More advanced configuration in SQL Database
   * Local database (sqlite) on Pollers

In Centreon Architecture, SNMP Traps can be received on Central and/or Pollers.
The process is quite the same for both architectures:

   * A Trap is received by daemon 'snmptrapd'
   * 'snmptrapd' execute 'centreontrapdforward' script
   * 'centreontrapdforward' write a file with trap informations in a spool directory ('/var/spool/centreontrapd/' by default) ;
   * Daemon 'centreontrapd' read files in spool directory. The daemon checks if the trap must be handled (according to the trap definition in database: local database or distant database).

A trap is not handled by 'centreontrapd' daemon if:

   * trap is not defined in centreon UI
   * trap is not linked to a service (or a service template) in centreon UI
   * trap sender can't be associated with a centreon host
 
Important changes
------------------

The new version managed traps in parallels (not sequential) by default. 
Don't worry, you can set in centreon UI for each traps the mode: 'sequential' or 'parrallel'. 
In next version, you can groups traps and set a 'sequential' mode. So in the current version, if the error trap and ok trap is an different OID, it might be a problem (if error trap and ok trap are sent without a second delay).
 
Central Architecture
---------------------

.. image:: /_static/images/user/advanced/central_architecture.png
   :align: center

Poller Architecture
--------------------

.. image:: /_static/images/user/advanced/poller_architecture.png
   :align: center

Daemon Configurations
=====================

The section describes each daemon configurations on centos 6.x.

snmptrapd
----------

Create '/etc/snmp/snmptrapd.conf' with the following lines::

  disableAuthorization yes
  traphandle default su -l centreon -c "/usr/share/centreon/bin/centreontrapdforward"

You can optimize performance of 'snmptrapd' with following options:
 
   * '-On': Don't try to transform OIDs
   * '-t': Do not log traps to syslog
   * '-n': Do not attempt to translate source addresses of incoming packets into hostnames (reverse-dns resolution)

Options can be changed in '/etc/sysconfig/snmptrapd' file::

  OPTIONS="-On -d -t -n -p /var/run/snmptrapd.pid"
  
Eventually, you can set 'snmptrapd' spool directory in RAM. Add in '/etc/fstab' file::

  tmpfs /var/run/snmpd                     tmpfs defaults,size=128m 0 0
  
centreontrapdforward
---------------------

By default, 'centreontrapdforward' write snmp trap files in directory '/var/spool/centreontrapd'.
You can set configuration in '/etc/centreon/centreontrapd.pm' file (the configuration file can be changed with '--config-extra=xxx')::

  our %centreontrapd_config = (
        spool_directory => '/var/spool/centreontrapd/',
  );

  1;

To optimize performance, spool directory can be in RAM. Add in '/etc/fstab' file::

  tmpfs /var/spool/centreontrapd    tmpfs  defaults,size=512m   0 0 

centreontrapd
--------------

'centreontrapd' daemon use two configuration files:
 
   * '/etc/centreon/centreon-config.pm': database configuration
   * '/etc/centreon/centreontrapd.pm': internal configuration

You shouldn't change internal configuration except:
 
   * 'mode': 1 to set 'centreontrapd' in poller mode (default: 0)
   * 'centreon_user': user which submit actions (default: centreon)

But we'll describe internal daemon configuration. You can set configuration in '/etc/centreon/centreontrapd.pm' file (the configuration file can be changed with '--config-extra=xxx')::

    our %centreontrapd_config = (
       # Time in seconds before killing not gently sub process
       timeout_end => 30,
       spool_directory => "/var/spool/centreontrapd/",
       # Delay between spool directory check new files
       sleep => 2,
       # 1 = use the time that the trap was processed by centreontrapdforward
       use_trap_time => 1,
       net_snmp_perl_enable => 1,
       mibs_environment => '',
       remove_backslash_from_quotes => 1,
       dns_enable => 0,
       # Separator for arguments substitution
       separator => ' ',
       strip_domain => 0,
       strip_domain_list => [],
       duplicate_trap_window => 1,
       date_format => "",
       time_format => "",
       date_time_format => "",
       # Internal OID cache from database
       cache_unknown_traps_enable => 1,
       # Time in seconds before cache reload
       cache_unknown_traps_retention => 600,
       # 0 = central, 1 = poller
       mode => 0,
       cmd_timeout => 10,
       centreon_user => "centreon",
       # 0 => skip if MySQL error | 1 => dont skip (block) if MySQL error (and keep order)
       policy_trap => 1,
       # Log DB
       log_trap_db => 0,
       log_transaction_request_max => 500,
       log_transaction_timeout => 10,
       log_purge_time => 600
    );

    1;

In central architecture, 'centreontrapd' uses MySQL database in file '/etc/centreon/centreon-config.pm'::

  $centreon_config = {
       VarLib => "/var/lib/centreon",
       CentreonDir => "/usr/share/centreon/",
       "centreon_db" => "centreon",
       "centstorage_db" => "centreon_storage",
       "db_host" => "localhost:3306",
       "db_user" => "centreon",
       "db_passwd" => "centreon"
  };

  1;

In poller architecture, 'centreontrapd' can use MySQL database (see above) or local sqlite database in file '/etc/centreon/centreon-config.pm'::

  $centreon_config = {
       VarLib => "/var/lib/centreon",
       CentreonDir => "/usr/share/centreon/",
       "centreon_db" => "dbname=/etc/snmp/centreon_traps/centreontrapd.sdb",
       "centstorage_db" => "dbname=/etc/snmp/centreon_traps/centreontrapd.sdb",
       "db_host" => "",
       "db_user" => "",
       "db_passwd" => "",
       "db_type" => 'SQLite',
  };

  1;

The local sqlite database is generated by the following command on central server::

   # php /usr/share/centreon/bin/generateSqlLite POLLER_ID /etc/snmp/centreon_traps/centreontrapd.sdb

Exploitation
=============

Howto: Which variables can i use in Centreon UI
-----------------------------------------------

The listing of variables:

========================  ==============================================================================
 Variable name             Description
========================  ==============================================================================
 @{NUMERIC_OID}            Argument value

 $1, $2,...                Argument value

 $p1, $p2,...              Preexec value ($p1 = returns of first preexec command, 
                           $p2 = returns of second preexec command,...)

 $*                        All arguments separated by space

 @HOSTNAME@                Centreon Hostname

 @HOSTADDRESS@             Ip Address of trap sender

 @HOSTADDRESS2@            Hostname of trap sender 
                           (if 'centreontrapd' succeed to reverse DNS. 
                           Otherwise, like  @HOSTADDRESS@)

 @SERVICEDESC@             Service Name

 @TRAPOUTPUT@, @OUTPUT@    Trap Message

 @STATUS@                  Status (0, 1, 2, 3)

 @SEVERITYNAME@            Severity Name

 @SEVERITYLEVEL@           Severity Level

 @TIME@                    Trap Time received

 @POLLERID@                Poller ID (useful for special execution command)

 @POLLERADDRESS@           Ip Address of the poller (useful for special execution command)

 @CMDFILE@                 'centcore.cmd' file or centengine external command file 
                           (useful for special execution command)

========================  ==============================================================================
   
Moreover, there are some specials function. Specials functions can be used in 'advanced routing':

========================  ==============================================================================
 Function name             Description
========================  ==============================================================================
 @GETHOSTBYADDR($1)@       Reverse DNS resolution (127.0.0.1 -> localhost)
 @GETHOSTBYNAME($1)@       DNS resolution (localhost -> 127.0.0.1)
========================  ==============================================================================

Howto: I add a new trap in centreon, centreontrapd skip it
-----------------------------------------------------------

'centreontrapd' had a internal cache to optimize traps treatment. By default, 'centreontrapd' reload his cache every 10min.
You can update 'centreontrapd' cache with a reload::

   # /etc/init.d/centreontrapd reload

Howto: send an example trap
----------------------------

You can send a trap with the following script::

   # perl /usr/share/centreon/bin/centreon_trap_send --help
  
Howto: centreontrapd in debug mode
-----------------------------------

If 'centreontrapd' uses MySQL Database:

   * Connect to Centreon UI
   * Set 'centreontrapd' in debug equals 'yes'
   * Reload 'centreontrapd'::

      # /etc/init.d/centreontrapd reload


.. image:: /_static/images/user/advanced/centreontrapd_debug.png
   :align: center
   
If 'centreontrapd' uses local sqlite database:

   * Set daemon argument option::

      # /usr/share/centreon/bin/centreontrapd --severity=debug xxxx