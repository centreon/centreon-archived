.. _upgrade_from_sources:
============
From sources
============

.. warning::

  Before upgrading Centreon, please make a database backup.

In order to upgrade Centreon from sources, :ref:`download <download>` the
latest Centreon package.

******************
Shell installation
******************

Extract the package::

  $ tar xvfz centreon-2.x.x.tar.gz

Change the directory::

  $ cd centreon-2.x.x

Run the upgrade script::

  $ ./install -u /etc/centreon

Where /etc/centreon is to be replaced by configuration directory.

Prerequisites check
-------------------

If [Step 01] is successful, you should not have any problem here. Otherwise,
go back to [Step 01] and install the prerequisites::

  ###############################################################################
  #                                                                             #
  #                         Centreon (www.centreon.com)                         #
  #                          Thanks for using Centreon                          #
  #                                                                             #
  #                                    v2.3                                     #
  #                                                                             #
  #                               infos@centreon.com                            #
  #                                                                             #
  #                   Make sure you have installed and configured               #
  #                   sudo - sed - php - apache - rrdtool - mysql               #
  #                                                                             #
  ###############################################################################
  ------------------------------------------------------------------------
  	Checking all needed binaries
  ------------------------------------------------------------------------
  rm                                                         OK
  cp                                                         OK
  mv                                                         OK
  /bin/chmod                                                 OK
  /bin/chown                                                 OK
  echo                                                       OK
  more                                                       OK
  mkdir                                                      OK
  find                                                       OK
  /bin/grep                                                  OK
  /bin/cat                                                   OK
  /bin/sed                                                   OK
  ------------------------------------------------------------------------
  	Detecting old installation
  ------------------------------------------------------------------------
  Finding configuration file in: /etc/centreon               OK
  You seem to have an existing Centreon.

Main components
---------------

Load the previous installation parameters::

  Do you want to use the last Centreon install parameters ?
  [y/n], default to [y]:
  > y
  
  Using:  /etc/centreon/instCentCore.conf
  /etc/centreon/instCentPlugins.conf
  /etc/centreon/instCentStorage.conf
  /etc/centreon/instCentWeb.conf

Answer y to components you want to upgrade::

  Do you want to install : Centreon Web Front
  [y/n], default to [n]:
  > y
  
  Do you want to install : Centreon CentCore
  [y/n], default to [n]:
  > y
  
  Do you want to install : Centreon Nagios Plugins
  [y/n], default to [n]:
  > y
  
  Do you want to install : Centreon Snmp Traps process
  [y/n], default to [n]:
  > y
  Convert variables for upgrade:

Upgrade Centreon Web Front
--------------------------

New information is required.

The path to binaries for Centreon Web::

  ------------------------------------------------------------------------
  	Start CentWeb Installation
  ------------------------------------------------------------------------
  
  Where is your Centreon binaries directory
  default to [/usr/local/centreon/bin]
  >
  Path /usr/local/centreon/bin                               OK

The path for extra data for Centreon Web::

  Where is your Centreon data informations directory
  default to [/usr/local/centreon/data]
  > 
  
  Do you want me to create this directory ? [/usr/local/centreon/data]
  [y/n], default to [n]:
  > y
  Path /usr/local/centreon/data 
  /usr/bin/perl                                              OK
  Finding Apache user :                                      www-data
  Finding Apache group :                                     www-data

The group of Centreon applications : This group is used for access rights
between monitoring applications::

  What is the Centreon group ? [centreon]
  default to [centreon]
  > 

  Do you want me to create this group ? [centreon]
  [y/n], default to [n]:
  > y

The user of Centreon applications::

  What is the Centreon user ? [centreon]
  default to [centreon]
  > 
  
  Do you want me to create this user ? [centreon]
  [y/n], default to [n]:
  > y

The user of broker module.

This user is used for adding rights to Centreon on the configuration and logs
directories. If left empty, it will use the Monitoring Engine user instead.

For example:

* Centreon Broker : *centreon-broker*
* ndo2db : *nagios*

:: 

  What is the Broker user ? (optional)
  > 

The path to monitoring engine log directory.

For example:

* Centeron Engine : */var/log/centreon-engine*
* Nagios : */var/log/nagios*

::

  What is the Monitoring engine log directory ?
  > /var/log/nagios

The path to monitoring plugins::

  Where is your monitoring plugins (libexec) directory ?
  default to [/usr/lib/nagios/plugins]
  > 

::

  Path /usr/lib/nagios/plugins                               OK
  Add group centreon to user www-data                        OK
  Add group centreon to user nagios                          OK
  Add group nagios to user www-data                          OK
  Add group nagios to user centreon                          OK

  ------------------------------------------------------------------------
  	Configure Sudo
  ------------------------------------------------------------------------

The path to Monitoring engine init script.

For example :

* Centreon Engine : */etc/init.d/centengine*
* Nagios : */etc/init.d/nagios*

::

  What is the Monitoring engine init.d script ?
  > /etc/init.d/nagios

The path to broker module configuration directory.

For example :

* Centreon Broker : */etc/centreon-broker*
* NDO : */etc/nagios*

::

  Where is the configuration directory for broker module ?
  > /etc/nagios

The path to broker daemon init script.

For example :

* Centreon Broker : */etc/init.d/cbd*
* ndo2db : */etc/init.d/ndo2db*

::

  Where is the init script for broker module daemon ?
  > /etc/init.d/ndo2db
  Your sudo has been configured previously

Replace or not your sudoers file.
For more security, you can backup the file **/etc/sudoers**.

::

  Do you want me to reconfigure your sudo ? (WARNING) 
  [y/n], default to [n]:
  > y
  Configuring Sudo                                           OK
  
  ------------------------------------------------------------------------
  	Configure Apache server
  ------------------------------------------------------------------------
  Create '/etc/apache2/conf.d/centreon.conf'                 OK
  Configuring Apache                                         OK

  Do you want to reload your Apache ?
  [y/n], default to [n]:
  > y
  Reloading Apache service                                   OK
  Preparing Centreon temporary files
  Change right on /usr/local/centreon/log                    OK
  Change right on /etc/centreon                              OK
  Change macros for insertBaseConf.sql                       OK
  Change macros for sql update files                         OK
  Change macros for php files                                OK
  Change right on /etc/nagios3                               OK
  Disconnect users from WebUI
  All users are disconnected                                 OK
  Copy CentWeb in system directory
  Install CentWeb (web front of centreon)                    OK
  Change right for install directory
  Change right for install directory                         OK
  Install libraries                                          OK
  Write right to Smarty Cache                                OK
  Copying libinstall                                         OK
  Change macros for centreon.cron                            OK
  Install Centreon cron.d file                               OK
  Change macros for centAcl.php                              OK
  Change macros for downtimeManager.php                      OK
  Change macros for eventReportBuilder.pl                    OK
  Change macros for dashboardBuilder.pl                      OK
  Install cron directory                                     OK
  Change right for eventReportBuilder.pl                     OK
  Change right for dashboardBuilder.pl                       OK
  Change macros for centreon.logrotate                       OK
  Install Centreon logrotate.d file                          OK
  Prepare export-mysql-indexes                               OK
  Install export-mysql-indexes                               OK
  Prepare import-mysql-indexes                               OK
  Install import-mysql-indexes                               OK
  Prepare indexes schema                                     OK
  Install indexes schema                                     OK
  
  ------------------------------------------------------------------------
  Pear Modules
  ------------------------------------------------------------------------
  Check PEAR modules
  PEAR                            1.4.9       1.9.4          OK
  DB                              1.7.6       1.7.14         OK
  DB_DataObject                   1.8.4       1.10.0         OK
  DB_DataObject_FormBuilder       1.0.0RC4    1.0.2          OK
  MDB2                            2.0.0       2.4.1          OK
  Date                            1.4.6       1.4.7          OK
  HTML_Common                     1.2.2       1.2.5          OK
  HTML_QuickForm                  3.2.5       3.2.13         OK
  HTML_QuickForm_advmultiselect   1.1.0       1.5.1          OK
  HTML_Table                      1.6.1       1.8.3          OK
  Archive_Tar                     1.1         1.3.7          OK
  Auth_SASL                       1.0.1       1.0.6          OK
  Console_Getopt                  1.2         1.2.3          OK
  Net_SMTP                        1.2.8       1.6.1          OK
  Net_Socket                      1.0.1       1.0.10         OK
  Net_Traceroute                  0.21        0.21.3         OK
  Net_Ping                        2.4.1       2.4.5          OK
  Validate                        0.6.2       0.8.5          OK
  XML_RPC                         1.4.5       1.5.5          OK
  SOAP                            0.10.1      0.13.0         OK
  Log                             1.9.11      1.12.7         OK
  Archive_Zip                     0.1.2       0.1.2          OK
  All PEAR modules                                           OK
  
  ------------------------------------------------------------------------
  		Centreon Post Install
  ------------------------------------------------------------------------
  Create /usr/local/centreon/www/install/install.conf.php    OK
  Create /etc/centreon/instCentWeb.conf                      OK
  Convert variables for upgrade:

Upgrade Centreon Storage
------------------------

New information is required.

::

  ------------------------------------------------------------------------
        Start CentStorage Installation
  ------------------------------------------------------------------------
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
  install www/install/createTablesCentstorage.sql            OK
  CentStorage status Directory already exists                PASSED
  CentStorage metrics Directory already exists               PASSED
  Change macros for centstorage binary                       OK
  Install CentStorage binary                                 OK
  Install library for centstorage                            OK
  Change right : /var/run/centreon                           OK
  Change macros for centstorage init script                  OK
  Replace CentCore default script Macro                      OK
  
  Do you want me to install CentStorage init script ?
  [y/n], default to [n]:
  > y
  CentStorage init script installed                          OK
  CentStorage default script installed                       OK
  
  Do you want me to install CentStorage run level ?
  [y/n], default to [n]:
  > y
  update-rc.d: using dependency based boot sequencing
  insserv: warning: current start runlevel(s) (3 5) of script 'centstorage' overwrites defaults (2 3 4 5).
  Change macros for logAnalyser                              OK
  Install logAnalyser                                        OK
  Change macros for logAnalyser-cbroker                      OK
  Install logAnalyser-cbroker                                OK
  Change macros for nagiosPerfTrace                          OK
  Install nagiosPerfTrace                                    OK
  Change macros for purgeLogs                                OK
  Install purgeLogs                                          OK
  Change macros for purgeCentstorage                         OK
  Install purgeCentstorage                                   OK
  Change macros for centreonPurge.sh                         OK
  Install centreonPurge.sh                                   OK
  Change macros for centstorage.cron                         OK
  Install CentStorage cron                                   OK
  Change macros for centstorage.logrotate                    OK
  Install Centreon Storage logrotate.d file                  OK
  Create /etc/centreon/instCentStorage.conf                  OK
  Convert variables for upgrade:

Upgrade Centreon Core
---------------------

New information is required.

::

  ------------------------------------------------------------------------
  	Start CentCore Installation
  ------------------------------------------------------------------------
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
  Change CentCore Macro                                      OK
  Copy CentCore in binary directory                          OK
  Change right : /var/run/centreon                           OK
  Change right : /var/lib/centreon                           OK
  Change macros for centcore.logrotate                       OK
  Install Centreon Core logrotate.d file                     OK
  Replace CentCore init script Macro                         OK
  Replace CentCore default script Macro                      OK
  
  Do you want me to install CentCore init script ?
  [y/n], default to [n]:
  > y
  CentCore init script installed                             OK
  CentCore default script installed                          OK
  
  Do you want me to install CentCore run level ?
  [y/n], default to [n]:
  > y
  update-rc.d: using dependency based boot sequencing
  insserv: warning: current start runlevel(s) (3 5) of script 'centcore' overwrites defaults (2 3 4 5).
  Create /etc/centreon/instCentCore.conf                     OK
  Convert variables for upgrade:

Upgrade Centreon Plugins
------------------------

New information is required.

::

  ------------------------------------------------------------------------
  	Start CentPlugins Traps Installation
  ------------------------------------------------------------------------
  Finding Apache user :                                      www-data
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
  Change macros for CentPluginsTraps                         OK
  Change macros for init scripts                             OK
  Installing the plugins Trap binaries                       OK
  Backup all your snmp files                                 OK
  Change macros for snmptrapd.conf                           OK
  Change macros for snmptt.ini                               OK
  SNMPTT init script installed                               OK
  SNMPTT default script installed                            OK
  update-rc.d: using dependency based boot sequencing
  Install : snmptrapd.conf                                   OK
  Install : snmp.conf                                        OK
  Install : snmptt.ini                                       OK
  Install : snmptt                                           OK
  Install : snmptthandler                                    OK
  Install : snmpttconvertmib                                 OK
  Generate SNMPTT configuration                              OK
  Create /etc/centreon/instCentPlugins.conf                  OK

The end of upgrade::

  ###############################################################################
  #                                                                             #
  #                 Go to the URL : http://localhost/centreon/                  #
  #                   	     to finish the setup                              #
  #                                                                             #
  #                  Report bugs at http://forge.centreon.com                   #
  #                                                                             #
  #                         Thanks for using Centreon.                          #
  #                          -----------------------                            #
  #                        Contact : infos@centreon.com                         #
  #                          http://www.centreon.com                            #
  #                                                                             #
  ###############################################################################

****************
Web installation
****************

During the web installation, follow these steps.

Presentation
------------

.. image:: /_static/images/upgrade/step01.png
   :align: center

Check dependencies
------------------

This step checks the dependencies on php modules.

.. image:: /_static/images/upgrade/step02.png
   :align: center

Upgrade the database
--------------------

This step upgrades database model and data, version by version.

.. image:: /_static/images/upgrade/step03.png
   :align: center

Release notes
-------------

.. image:: /_static/images/upgrade/step04.png
   :align: center

Finish
------

.. image:: /_static/images/upgrade/step05.png
   :align: center
