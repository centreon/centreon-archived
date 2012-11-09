=============
Using sources
=============

*************
Prerequisites
*************

CentOS
======

In CentOS and RHEL5, following packages are not included in standard
repositories. To install pre-requisites, you need to add *RPM Forge*
repository. On 32-bits::

  # wget http://packages.sw.be/rpmforge-release/rpmforge-release-0.5.1-1.el5.rf.i386.rpm
  # wget http://dag.wieers.com/rpm/packages/RPM-GPG-KEY.dag.txt

On 64-bits::

  # wget http://packages.sw.be/rpmforge-release/rpmforge-release-0.5.1-1.el5.rf.x86_64.rpm
  # wget http://dag.wieers.com/rpm/packages/RPM-GPG-KEY.dag.txt

Use your favourite editor to open "RPM-GPG-KEY.dag.txt", and remove
the first few lines. The file should start with::

  "-----BEGIN PGP PUBLIC KEY BLOCK-----"

Then, execute the following::

  # rpm --import RPM-GPG-KEY.dag.txt
  # rpm -Uvh rpmforge-release-0.5.1-1.el5.rf.i386.rpm

============== =============================================================================
Program groups Commands
============== =============================================================================
Updates        ::

                 yum update
                 yum upgrade

Apache2        ::

                 yum install httpd 
                 usermod -U apache

GD modules     ::

                 yum install gd fontconfig-devel libjpeg-devel libpng-devel gd-devel perl-GD

MySQL          ::

                 yum install openssl-devel perl-DBD-MySQL mysql-server mysql-devel

PHP            ::

                 yum install php php-mysql php-gd php-ldap php-xml php-mbstring

Perl modules   ::

                 yum install perl-Config-IniFiles perl-DBI perl-DBD-MySQL

RRDTools       ::

                 yum install rrdtool perl-rrdtool

SNMP           ::

                 yum install perl-Crypt-DES perl-Digest-SHA1 perl-Digest-HMAC net-snmp-utils
                 yum install perl-Socket6 perl-IO-Socket-INET6 net-snmp net-snmp-libs 
                 yum install php-snmp dmidecode lm_sensors perl-Net-SNMP net-snmp-perl

Misc           ::

                 yum install fping cpp gcc gcc-c++ libstdc++ glib2-devel

PEAR           Installation::

                 yum install php-pear

               Configuration::

                 pear channel-update pear.php.net

               Using a proxy with PEAR::

                 pear config-set http_proxy http://my_proxy.com:port

               Update Pear package::

                 pear upgrade-all

============== =============================================================================

Debian / Ubuntu
===============

================================ ==================================================================================================================
Program groups                   Command
================================ ==================================================================================================================
System base                      ::

                                   apt-get install sudo tofrodos bsd-mailx lsb-release

Database server                  ::

                                   apt-get install mysql-server libmysqlclient15-dev

WebServer and PHP 5 installation ::

                                   apt-get install apache2 apache2-mpm-prefork php5 php5-mysql php-pear php5-ldap php5-snmp php5-gd

RRDTools                         ::

                                   apt-get install rrdtool librrds-perl

Perl modules                     ::

                                   apt-get install libconfig-inifiles-perl libcrypt-des-perl libdigest-hmac-perl libdigest-sha1-perl libgd-gd2-perl

SNMP                             ::

                                   apt-get install snmp snmpd libnet-snmp-perl libsnmp-perl

================================ ==================================================================================================================

Suse
====

Packages
--------

=================== ==========================================================================
Program groups      Command
=================== ==========================================================================
Compilers           ::

                     yast -i gcc gcc-c++ make automake

Web server and PHP5 ::

                      yast -i apache2
                      yast -i php5 php5-mysql apache2-mod_php5 php5-pear php5-ldap php5-snmp
                      yast -i php5-gd php5-soap php5-posix php5-gettext php5-mbstring

MySQL               ::

                      yast -i mysql libmysqlclient-devel perl-DBD-mysql mysql-community-server

RRDTools            ::

                      yast -i rrdtool

Perl                ::

                      yast -i perl-Config-IniFiles

SNMP                ::

                      yast -i net-snmp perl-Net-SNMP perl-SNMP

GD modules          ::

                      yast -i gd libjpeg-devel libpng-devel fontconfig-devel freetype2-devel

Misc                ::

                      yast -i sudo mailx fping iputils dos2unix cron dejavu
                      
=================== ==========================================================================

Configuring MIME types
----------------------

On some OpenSuse distributions, the default mime types are not
properly configured to work with the Centreon user interface. Edit the
*/etc/mime.types* file and look for the lines::

  text/x-xsl xsl
  text/x-xslt xslt xsl

Replace them with the following::

  text/xml xsl
  text/xml xslt xsl

Save the file and restart apache::

  /etc/init.d/apache2 restart

*********
Scheduler
*********

You may choose between Nagios and Centreon Engine as scheduling engine.

Centreon Engine
===============

.. note::

   FIXME : ajouter des permaliens vers les docs des deux moteurs

Nagios
======

=================  ======================================
 Distribution       Command                                
=================  ======================================
 CentOS             ``yum install nagios``
 Debian / Ubuntu    ``apt-get install nagios3``
 OpenSuse           ``yast -i nagios``        
=================  ======================================

``nagios`` user will need a shell::

  usermod -s /bin/sh nagios

*************
Broker module
*************

You may choose between NDOUtils and Centreon Broker as broker module.

Centreon Broker
===============

.. note::

   FIXME : ajouter des permaliens vers les docs

NDOUtils
========

=============  ==========================================
Distribution   Command line
=============  ==========================================
Ubuntu/Debian  ``apt-get install ndoutils-nagios3-mysql``

CentOS         ``yum install ndoutils-mysql``

OpenSuse       ``yast -i ndoutils``
=============  ==========================================

********
Centreon
********

Download the newest Centreon package from the website:
`<http://www.centreon.com/Content-Download/donwload-centreon-monitoring-tools>`_.

Shell Installation
==================

Extract the Centreon package::

  tar zxf centreon-2.x.x.tar.gz

Change directory::

  cd centreon-2.x.x

Run the installation script::

  ./install.sh -i

The installation script allows custom configuration, this procedure
will show you the best paths to use. Also, the Yes/No prompt questions
will result in [y] answers most of the time.

Prerequisites check
-------------------

If [Step 01] is successful, you should not have any problem
here. Otherwise, go back to [Step 01] and install the prerequisites::

  ###############################################################################
  #                                                                             #
  #                         Centreon (www.centreon.com)                         #
  #                          Thanks for using Centreon                          #
  #                                                                             #
  #                                    v2.3                                     #
  #                                                                             #
  #                              infos@centreon.com                             #
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

License agreement
-----------------

::

    This General Public License does not permit incorporating your program into
    proprietary programs.  If your program is a subroutine library, you may
    consider it more useful to permit linking proprietary applications with the
    library.  If this is what you want to do, use the GNU Library General
    Public License instead of this License.

    Do you accept GPL license ?
    [y/n], default to [n]:
    > y

Main components
---------------

Answer [y] to all::

  Do you want to install Centreon Web Front
  [y/n], default to [n]:
  > y

  Do you want to install Centreon CentCore
  [y/n], default to [n]:
  > y

  Do you want to install Centreon Nagios Plugins
  [y/n], default to [n]:
  > y

  Do you want to install Centreon Snmp Traps process
  [y/n], default to [n]:
  > y

Installation paths
------------------

::

  ------------------------------------------------------------------------ 
          Start CentWeb Installation
  ------------------------------------------------------------------------

  Where is your Centreon directory?
  default to [/usr/local/centreon]
  >/usr/share/centreon

::

  Do you want me to create this directory ? [/usr/share/centreon]
  [y/n], default to [n]:
  > y
  Path /usr/share/centreon                                   OK



  Where is your Centreon log directory
  default to [/usr/local/centreon/log/]
  >/var/log/centreon

  Do you want me to create this directory ? [/var/log/centreon/]
  [y/n], default to [n]:
  > y
  Path /var/log/centreon/                                    OK

::

  Where is your Centreon etc directory
  default to [/etc/centreon]
  >

  Do you want me to create this directory ? [/etc/centreon]
  [y/n], default to [n]:
  > y
  Path /etc/centreon                                         OK

  Where is your Centreon generation_files directory?
  default to [/usr/local/centreon/]
  >/usr/share/centreon
  Path /usr/share/centreon/                                  OK

  Where is your Centreon variable library directory?
  default to [/var/lib/centreon]
  >

  Do you want me to create this directory ? [/var/lib/centreon]
  [y/n], default to [n]:
  > y
  Path /var/lib/centreon                  

  Where is your CentPlugins Traps binary
  default to [/usr/local/centreon/bin]
  >/usr/share/centreon/bin

  Do you want me to create this directory ? [/usr/share/centreon/bin]
  [y/n], default to [n]:
  > y
  Path /usr/share/centreon/bin                               OK

The RRDs.pm package can be located elsewhere. In order to locate it, run this in another terminal::

  updatedb
  locate RRDs.pm

::

  Where is the RRD perl module installed [RRDs.pm]
  default to [/usr/lib/perl5/RRDs.pm]
  >

::

  Path /usr/lib/perl5                                        OK
  /usr/bin/rrdtool                                           OK
  /usr/bin/mail                                              OK

The PEAR.php file can be located elsewhere. In order to locate it, run this in another terminal::

  updatedb
  locate PEAR.php

::

  Where is PEAR [PEAR.php]
  default to [/usr/share/php/PEAR.php]
  >

::

  Path /usr/share/php                                        OK

  Where is installed Nagios ?
  default to [/usr/local/nagios]
  >/usr/share/nagios
  Path /usr/share/nagios                                     OK

On Debian: /usr/share/nagios3/

::

  Where is your nagios config directory
  default to [/usr/local/nagios/etc]
  >/etc/nagios
  Path /etc/nagios                                           OK

On Debian: /etc/nagios3/

::

  Where is your Nagios var directory ?
  default to [/usr/local/nagios/var]
  >/var/log/nagios
  Path /var/log/nagios                                       OK

On Debian: /var/log/nagios3/

::

  Where is your Nagios plugins (libexec) directory ?
  default to [/usr/local/nagios/libexec]
  >/usr/lib/nagios/plugins/
  Path /usr/lib/nagios/plugins                               OK
  /usr/sbin/nagios                                           OK

  Where is your Nagios image directory ?
  default to [/usr/local/nagios/share/images/logos]
  >/usr/share/nagios/images/logos
  Path /usr/share/nagios/images/logos                        OK

On Debian: /usr/share/nagios3/htdocs/images/logos/

::

  /usr/sbin/nagiostats                                       OK
  p1_file : /usr/local/nagios/bin/p1.pl                      OK
  /usr/bin/php                                               OK
  /usr/bin/perl                                              OK
  Finding Apache group :                                     www-data
  Finding Apache user :                                      www-data
  Finding Nagios user :                                      nagios
  Finding Nagios group :                                     nagios

::

  Where is your NDO ndomod binary ?
  default to [/usr/sbin/ndomod.o]
  >/usr/lib/nagios/brokers/ndomod.o
  /usr/lib/nagios/brokers/ndomod.o                           OK

On Debian: /usr/lib/ndoutils/ndomod-mysql-3x.o

Sudo configuration
------------------

::

  ------------------------------------------------------------------------
          Configure Sudo
  ------------------------------------------------------------------------

  Where is sudo configuration file
  default to [/etc/sudoers]
  >
  /etc/sudoers                                               OK
  Nagios init script                                         OK
  Your sudo is not configured

  Do you want me to configure your sudo ? (WARNING)
  [y/n], default to [n]:
  > y
  Configuring Sudo                                           OK

Apache configuration
--------------------

::

  ------------------------------------------------------------------------
         Configure Apache server
  ------------------------------------------------------------------------

  Do you want to add Centreon Apache sub configuration file ?
  [y/n], default to [n]:
  > y
  Backup Centreon Apache configuration completed
  Create '/etc/apache2/conf.d/centreon.conf'                 OK
  Configuring Apache                                         OK

  Do you want to reload your Apache ?
  [y/n], default to [n]:
  > y
  Reloading Apache service                                   OK
  Preparing Centreon temporary files
  Change right on /usr/local/centreon/log                    OK
  Change right on /etc/centreon                              OK
  Change right on /usr/local/nagios/share/images/logos       OK
  Install nagios documentation                               OK
  Change macros for insertBaseConf.sql                       OK
  Change macros for php files                                OK
  Change right on /usr/local/nagios/etc                      OK
  Copy CentWeb in system directory
  Install CentWeb (web front of centreon)                    OK
  Install libraries                                          OK
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

Pear module installation
------------------------

The first check will probably show you NOK messages that refer to
outdated modules.

::

  ------------------------------------------------------------------------
  Pear Modules
  ------------------------------------------------------------------------
  Check PEAR modules
  PEAR                            1.4.9       1.6.1          OK
  DB                              1.7.6                      NOK
  DB_DataObject                   1.8.4                      NOK
  DB_DataObject_FormBuilder       1.0.0RC4                   NOK
  MDB2                            2.0.0                      NOK
  Date                            1.4.6                      NOK
  HTML_Common                     1.2.2                      NOK
  HTML_QuickForm                  3.2.5                      NOK
  HTML_QuickForm_advmultiselect   1.1.0                      NOK
  HTML_Table                      1.6.1                      NOK
  Archive_Tar                     1.1         1.3.2          OK
  Auth_SASL                       1.0.1                      NOK
  Console_Getopt                  1.2         1.2.3          OK
  Net_SMTP                        1.2.8                      NOK
  Net_Socket                      1.0.1                      NOK
  Net_Traceroute                  0.21                       NOK
  Net_Ping                        2.4.1                      NOK
  Validate                        0.6.2                      NOK
  XML_RPC                         1.4.5                      NOK
  SOAP                            0.10.1                     NOK
  Log                             1.9.11                     NOK

Accept the installation and upgrade of the required PEAR modules::

  Do you want me to install/upgrade your PEAR modules
  [y/n], default to [y]:

Now everything should be OK::

  Installing PEAR modules
  DB                              1.7.6       1.7.13         OK
  DB_DataObject                   1.8.4       1.8.12         OK
  DB_DataObject_FormBuilder       1.0.0RC4    1.0.0          OK
  MDB2                            2.0.0       2.4.1          OK
  HTML_QuickForm_advmultiselect   1.1.0       1.5.1          OK
  HTML_Table                      1.6.1       1.8.2          OK
  Auth_SASL                       1.0.1       1.0.3          OK
  Net_SMTP                        1.2.8       1.3.3          OK
  Net_Traceroute                  0.21        0.21.1         OK
  Net_Ping                        2.4.1       2.4.4          OK
  Validate                        0.6.2       0.8.2          OK
  XML_RPC                         1.4.5       1.5.2          OK
  SOAP                            0.10.1      0.12.0         OK
  Log                             1.9.11      1.11.5         OK
  Check PEAR modules
  PEAR                            1.4.9       1.6.1          OK
  DB                              1.7.6       1.7.13         OK
  DB_DataObject                   1.8.4       1.8.12         OK
  DB_DataObject_FormBuilder       1.0.0RC4    1.0.0          OK
  MDB2                            2.0.0       2.4.1          OK
  Date                            1.4.6       1.4.7          OK
  HTML_Common                     1.2.2       1.2.5          OK
  HTML_QuickForm                  3.2.5       3.2.11         OK
  HTML_QuickForm_advmultiselect   1.1.0       1.5.1          OK
  HTML_Table                      1.6.1       1.8.2          OK
  Archive_Tar                     1.1         1.3.2          OK
  Auth_SASL                       1.0.1       1.0.3          OK
  Console_Getopt                  1.2         1.2.3          OK
  Net_SMTP                        1.2.8       1.3.3          OK
  Net_Socket                      1.0.1       1.0.9          OK
  Net_Traceroute                  0.21        0.21.1         OK
  Net_Ping                        2.4.1       2.4.4          OK
  Validate                        0.6.2       0.8.2          OK
  XML_RPC                         1.4.5       1.5.2          OK
  SOAP                            0.10.1      0.12.0         OK
  Log                             1.9.11      1.11.5         OK
  All PEAR modules                                           OK

Configuration file installation
-------------------------------

::

  ------------------------------------------------------------------------
                  Centreon Post Install
  ------------------------------------------------------------------------
  Create /usr/local/centreon/www/install/install.conf.php    OK
  Create /etc/centreon/instCentWeb.conf                      OK

Centstorage installation
------------------------

.. note::

   Centstorage stop process will **fail**, for centstorage is not even
   started at this point, there is no need to worry about it.

::

  ------------------------------------------------------------------------
          Start CentStorage Installation
  ------------------------------------------------------------------------

  Where is your Centreon Run Dir directory?
  default to [/var/run/centreon]
  >

  Do you want me to create this directory ? [/var/run/centreon]
  [y/n], default to [n]:
  > y
  Path /var/run/centreon                                     OK

  Where is your CentStorage binary directory
  default to [/usr/share/centreon/bin]
  >
  Path /usr/share/centreon/bin                               OK

  Where is your CentStorage RRD directory
  default to [/var/lib/centreon]
  >
  Path /var/lib/centreon                                     OK
  Finding Nagios group :                                     nagios
  Finding Nagios user :                                      nagios
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
  install www/install/createTablesCentstorage.sql            OK
  Creating Centreon Directory '/var/lib/centreon/status'     OK
  Creating Centreon Directory '/var/lib/centreon/metrics'    OK
  Change macros for centstorage binary                       OK
  Install CentStorage binary                                 OK
  Install library for centstorage                            OK
  Change right : /var/run/centreon                           OK
  Change macros for centstorage init script                  OK

  Do you want me to install CentStorage init script ?
  [y/n], default to [n]:
  > y
  CentStorage init script installed                          OK

  Do you want me to install CentStorage run level ?
  [y/n], default to [n]:
  > y
  Stopping centreon data collector Collector : centstorage
  Waiting for centstorage to exit . done.
  CentStorage stop                                           FAIL
  Change macros for logAnalyser                              OK
  Install logAnalyser                                        OK
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
  Create /etc/centreon/instCentStorage.conf                  OK

Centcore installation
---------------------

::

  ------------------------------------------------------------------------
          Start CentCore Installation
  ------------------------------------------------------------------------

  Where is your CentCore binary directory
  default to [/usr/share/centreon/bin]
  >
  Path /usr/share/centreon/bin                               OK
  /usr/bin/ssh                                               OK
  /usr/bin/scp                                               OK
  Finding Nagios group :                                     nagios
  Finding Nagios user :                                      nagios
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
  Change CentCore Macro                                      OK
  Copy CentCore in binary directory                          OK
  Change right : /var/run/centreon                           OK
  Change right : /var/lib/centreon                           OK
  Replace CentCore init script Macro                         OK

  Do you want me to install CentCore init script ?
  [y/n], default to [n]:
  > y
  CentCore init script installed                             OK

  Do you want me to install CentCore run level ?
  [y/n], default to [n]:
  > y
  Create /etc/centreon/instCentCore.conf                     OK

Plugin installation
-------------------

::

  ------------------------------------------------------------------------
         Start CentPlugins Installation
  ------------------------------------------------------------------------

  Where is your CentPlugins lib directory
  default to [/var/lib/centreon/centplugins]
  >

  Do you want me to create this directory ? [/var/lib/centreon/centplugins]
  [y/n], default to [n]:
  > y
  Path /var/lib/centreon/centplugins                         OK
  Finding Nagios user :                                      nagios
  Finding Nagios group :                                     nagios
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
  Change macros for CentPlugins                              OK
  Installing the plugins                                     OK
  Change right on centreon.conf                              OK
  CentPlugins is installed

  ------------------------------------------------------------------------
          Start CentPlugins Traps Installation
  ------------------------------------------------------------------------

  Where is your SNMP configuration directory
  default to [/etc/snmp]
  >
  /etc/snmp                                                  OK

  Where is your SNMPTT binaries directory
  default to [/usr/local/centreon/bin/]
  >/usr/share/centreon/bin
  /usr/share/centreon/bin/                                   OK
  Finding Nagios group :                                     nagios
  Finding Apache user :                                      www-data
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
  Change macros for CentPluginsTraps                         OK
  Installing the plugins Trap binaries                       OK
  Backup all your snmp files                                 OK
  Change macros for snmptrapd.conf                           OK
  Change macros for snmptt.ini                               OK
  Install : snmptrapd.conf                                   OK
  Install : snmp.conf                                        OK
  Install : snmptt.ini                                       OK
  Install : snmptt                                           OK
  Install : snmpttconvertmib                                 OK
  Generate SNMPTT configuration                              OK
  Create /etc/centreon/instCentPlugins.conf                  OK

End
---

::

  ###############################################################################
  #                                                                             #
  #                 Go to the URL : http://your-server/centreon/                #
  #                            to finish the setup                              #
  #                                                                             #
  #                  Report bugs at http://forge.centreon.com                   #
  #                                                                             #
  #                         Thanks for using Centreon.                          #
  #                          -----------------------                            #
  #                        Contact : infos@centreon.com                         #
  #                          http://www.centreon.com                            #
  #                                                                             #
  ###############################################################################

Web Installation
================

.. note::

   Make sure your Apache and MySQL servers are up and running before
   going any further.

Open your favorite web browser and go to:::

  http://SERVER_ADDRESS/centreon

You should see the following page:

.. image:: /_static/images/installation/setup_1.png
   :align: center

Accept the license:

.. image:: /_static/images/installation/setup_2.png
   :align: center

Leave the default settings:

.. image:: /_static/images/installation/setup_3.png
   :align: center

If Step 01 went well, everything should be OK:



.. image:: /_static/images/installation/setup_4.png
   :align: center

Pear modules must be up to date:

.. image:: /_static/images/installation/setup_5.png
   :align: center

Fill the MySQL access information, filling the password should be
enough. However, if your MySQL database is located in a remote server,
specify the IP address at *Database Location*

.. image:: /_static/images/installation/setup_6.png
   :align: center

If the MySQL access information is correct, everything should be OK at this point

.. image:: /_static/images/installation/setup_7.png
   :align: center

Fill the first administrator login information.

.. image:: /_static/images/installation/setup_8.png
   :align: center

You can choose to enable the LDAP authenticataion or you can enable it later.

..  image:: /_static/images/installation/setup_9.png
   :align: center

Creation of configuration files:

.. image:: /_static/images/installation/setup10.png
   :align: center

Check up of MySQL table creation:

.. image:: /_static/images/installation/setup_11.png
   :align: center

Finish the web installation by clicking on the button:

.. image:: /_static/images/installation/setup_12.png
   :align: center

The installation is done, you should see the login screen:

.. image:: /_static/images/installation/login.png
   :align: center

Enter your credentials, you can now start configuring your monitoring system.
