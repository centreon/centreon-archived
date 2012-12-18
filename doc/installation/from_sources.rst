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
repository. 

On 32-bits::

  $ wget http://packages.sw.be/rpmforge-release/rpmforge-release-0.5.1-1.el5.rf.i386.rpm
  $ wget http://dag.wieers.com/rpm/packages/RPM-GPG-KEY.dag.txt

On 64-bits::

  $ wget http://packages.sw.be/rpmforge-release/rpmforge-release-0.5.1-1.el5.rf.x86_64.rpm
  $ wget http://dag.wieers.com/rpm/packages/RPM-GPG-KEY.dag.txt

Use your favourite editor to open *RPM-GPG-KEY.dag.txt*, and remove
the first few lines. The file should start with::

  "-----BEGIN PGP PUBLIC KEY BLOCK-----"

Then, execute the following::

  $ rpm --import RPM-GPG-KEY.dag.txt
  $ rpm -Uvh rpmforge-release-0.5.1-1.el5.rf.i386.rpm

Now, you're ready to install the prerequisites::

  $ yum update
  $ yum upgrade
  $ yum install httpd gd fontconfig-devel libjpeg-devel libpng-devel gd-devel perl-GD \
      openssl-devel perl-DBD-MySQL mysql-server mysql-devel php php-mysql php-gd php-ldap php-xml php-mbstring \
      perl-Config-IniFiles perl-DBI perl-DBD-MySQL rrdtool perl-rrdtool perl-Crypt-DES perl-Digest-SHA1 \
      perl-Digest-HMAC net-snmp-utils perl-Socket6 perl-IO-Socket-INET6 net-snmp net-snmp-libs php-snmp \
      dmidecode lm_sensors perl-Net-SNMP net-snmp-perl fping cpp gcc gcc-c++ libstdc++ glib2-devel \
      php-pear

Additionnal commands are required to configure the environment properly::

  $ usermod -U apache
  $ pear channel-update pear.php.net

If you can't access the Internet directly but throught a proxy, run the following::

  $ pear config-set http_proxy http://my_proxy.com:port

And finally::

  $ pear upgrade-all 

Debian / Ubuntu
===============

Install the following prerequisites::

  $ apt-get install sudo tofrodos bsd-mailx lsb-release mysql-server libmysqlclient15-dev \
      apache2 apache2-mpm-prefork php5 php5-mysql php-pear php5-ldap php5-snmp php5-gd \
      rrdtool librrds-perl libconfig-inifiles-perl libcrypt-des-perl libdigest-hmac-perl \
      libdigest-sha1-perl libgd-gd2-perl snmp snmpd libnet-snmp-perl libsnmp-perl

To finish, you must install SNMP mibs. Due to a licensing issue, those
mibs are not available by default on Debian. To add them, first edit
the */etc/apt/sources.list* file and add the ``non-free`` category.

Then, run the following commands::

  $ apt-get update
  $ apt-get install snmp-mibs-installer

Suse
====

Packages
--------

Install the following prerequisites::

  $ yast -i gcc gcc-c++ make automake apache2 php5 php5-mysql apache2-mod_php5 php5-pear \
      php5-ldap php5-snmp php5-gd php5-soap php5-posix php5-gettext php5-mbstring mysql \
      libmysqlclient-devel perl-DBD-mysql mysql-community-server rrdtool perl-Config-IniFiles \
      net-snmp perl-Net-SNMP perl-SNMP gd libjpeg-devel libpng-devel fontconfig-devel \
      freetype2-devel sudo mailx fping iputils dos2unix cron dejavu

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

******************
Monitoring engine
******************

Centreon is compatible with the following monitoring engines:

* :ref:`Centreon Engine <centreon-engine:user_installation_using_sources>`
* `Nagios <http://nagios.sourceforge.net/docs/3_0/quickstart.html>`_
* `Icinga <http://docs.icinga.org/latest/en/>`_

Install one of these engines before going futher and make sure to install the `Nagios plugins <http://nagios.sourceforge.net/docs/3_0/quickstart.html>`_.


**************
Broker module
**************

Centreon is compatible with the following broker modules:

* :ref:`Centreon Broker <centreon-broker:user_installation_using_sources>`
* `NDOUtils <http://nagios.sourceforge.net/docs/ndoutils/NDOUtils.pdf>`_

Install one of these broker modules before going further.


********
Centreon
********

Download the newest Centreon package :ref:`here <download_web_src>`.


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
  #                                    v2.4.0                                   #
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

Answer [y] to all

::

  ------------------------------------------------------------------------
  	    Please choose what you want to install
  ------------------------------------------------------------------------

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

Installation paths
------------------

::

  ------------------------------------------------------------------------ 
          Start CentWeb Installation
  ------------------------------------------------------------------------

  Where is your Centreon directory?
  default to [/usr/local/centreon]
  > /usr/share/centreon

::

  Do you want me to create this directory ? [/usr/share/centreon]
  [y/n], default to [n]:
  > y
  Path /usr/share/centreon                                   OK



  Where is your Centreon log directory
  default to [/usr/local/centreon/log/]
  > /var/log/centreon

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

  Where is your Centreon binaries directory
  default to [/usr/local/centreon/bin]
  > /usr/share/centreon/bin

  Do you want me to create this directory ? [/usr/share/centreon/bin]
  [y/n], default to [n]:
  > y
  Path /usr/share/centreon/bin                               OK

  Where is your Centreon data information directory
  default to [/usr/local/centreon/data]
  > /usr/share/centreon/data 

  Do you want me to create this directory ? [/usr/share/centreon/data]
  [y/n], default to [n]:
  > y

  Where is your Centreon generation_files directory?
  default to [/usr/local/centreon/]
  > /usr/share/centreon
  Path /usr/share/centreon/                                  OK

  Where is your Centreon variable library directory?
  default to [/var/lib/centreon]
  >

  Do you want me to create this directory ? [/var/lib/centreon]
  [y/n], default to [n]:
  > y
  Path /var/lib/centreon                                     OK

  Where is your CentPlugins Traps binary
  default to [/usr/local/centreon/bin]
  > /usr/share/centreon/bin
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
  /usr/bin/php                                               OK
  /usr/bin/perl                                              OK
  Finding Apache user :                                      apache
  Finding Apache group :                                     apache


Centreon user and group
-----------------------

The group of Centreon applications: This group is used for access rights
between monitoring applications::

  What is the Centreon group ? [centreon]
  default to [centreon]
  > 

  What is the Centreon user ? [centreon]
  default to [centreon]
  > 


Monitoring user
---------------

User that will be used for running the monitoring engine

If you are using Centreon Engine::

  What is the Monitoring engine user ?
  > centreon-engine

If you are using Nagios::

  What is the Monitoring engine user ?
  > nagios

User that will be used for running the broker daemon:

If you are using Centreon Broker::

  What is the Broker user ? (optional)
  > centreon-broker

If you are using NDOUtils::
  
  What is the Broker user ? (optional)
  > nagios


Monitoring log directory
------------------------

If you are using Centreon Engine::

  What is the Monitoring engine log directory ?
  > /var/log/centreon-engine

If you are using Nagios::

  What is the Monitoring engine log directory ?
  > /var/log/nagios


Plugin path
-----------

::

  Where is your monitoring plugins (libexec) directory ?
  default to [/usr/lib/nagios/plugins]
  >
  Path /usr/lib/nagios/plugins                               OK
  Add group centreon to user apache                          OK
  Add group centreon to user centreon-engine                 OK
  Add group centreon-engine to user apache                   OK
  Add group centreon-engine to user centreon                 OK


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


If you are using Centreon Engine::

  What is the Monitoring engine init.d script ?
  > /etc/init.d/centengine

  What is the Monitoring engine binary ?
  > /usr/sbin/centengine

  What is the Monitoring engine configuration directory ?
  > /etc/centreon-engine

If you are using Nagios ::

  What is the Monitoring engine init.d script ?
  > /etc/init.d/nagios

  What is the Monitoring engine binary ?
  > /usr/sbin/nagios

  What is the Monitoring engine configuration directory ?
  > /etc/nagios

If you are using Centreon Broker::

  Where is the configuration directory for broker module ?
  > /etc/centreon-broker

  Where is the init script for broker module daemon ?
  > /etc/init.d/cbd

If you are using NDOUtils::
  
  Where is the configuration directory for broker module ?
  > /etc/nagios

  Where is the init script for broker module daemon ?
  > /etc/init.d/ndo2db


Sudo configuration::
  Do you want me to reconfigure your sudo ? (WARNING) 
  [y/n], default to [n]:
  >  y
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
  Create '/etc/httpd/conf.d/centreon.conf'                   OK
  Configuring Apache                                         OK

  Do you want to reload your Apache ?
  [y/n], default to [n]:
  > y
  Reloading Apache service                                   OK
  Preparing Centreon temporary files
  Change right on /var/log/centreon                          OK
  Change right on /etc/centreon                              OK
  Change macros for insertBaseConf.sql                       OK
  Change macros for sql update files                         OK
  Change macros for php files                                OK
  Change right on /usr/local/etc                             OK
  Add group centreon to user apache                          OK
  Add group centreon to user centreon-engine                 OK
  Add group centreon to user centreon                        OK
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


Pear module installation
------------------------

::

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
  Archive_Tar                     1.1         1.3.1          OK
  Auth_SASL                       1.0.1       1.0.6          OK
  Console_Getopt                  1.2         1.2            OK
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


Configuration file installation
-------------------------------

::

  ------------------------------------------------------------------------
  		  Centreon Post Install
  ------------------------------------------------------------------------
  Create /usr/share/centreon/www/install/install.conf.php    OK
  Create /etc/centreon/instCentWeb.conf                      OK



Centstorage installation
------------------------

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
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
  Change CentCore Macro                                      OK
  Copy CentCore in binary directory                          OK
  Change right : /var/run/centreon                           OK
  Change right : /var/lib/centreon                           OK
  Change macros for centcore.logrotate                       OK
  Install Centreon Core logrotate.d file                     OK
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
  > /usr/share/centreon/bin
  /usr/share/centreon/bin                                    OK
  Finding Apache user :                                      apache
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
  Change macros for CentPluginsTraps                         OK
  Change macros for init scripts                             OK
  Installing the plugins Trap binaries                       OK
  Change macros for snmptrapd.conf                           OK
  Change macros for snmptt.ini                               OK
  SNMPTT init script installed                               OK
  Install : snmptrapd.conf                                   OK
  Install : snmp.conf                                        OK
  Install : snmptt.ini                                       OK
  Install : snmptt                                           OK
  Install : snmptthandler                                    OK
  Install : snmpttconvertmib                                 OK
  Create /etc/centreon/instCentPlugins.conf                  OK


End
---

::

  ###############################################################################
  #                                                                             #
  #                 Go to the URL : http://localhost.localdomain/centreon/      #
  #                   	     to finish the setup                                #
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

   Make sure that your Apache and MySQL servers are up and running before
   going any further.

Open your favorite web browser and go to:::

  http://SERVER_ADDRESS/centreon

You should see the following page:

.. image:: /_static/images/installation/setup_1.png
   :align: center

Press ``Next``:

.. image:: /_static/images/installation/setup_2.png
   :align: center

If there is any missing package, install them first then click on the ``Refresh`` button. Click on the ``Next`` button once everything is ``OK``


.. image:: /_static/images/installation/setup_3_1.png
   :align: center

Select your monitoring engine.

Depending on the chosen monitoring engine, you will be asked to enter some specific parameters.

Case of Centreon Engine:

.. image:: /_static/images/installation/setup_3_2.png
   :align: center

Case of Nagios:

.. image:: /_static/images/installation/setup_3_3.png
   :align: center

Click on the ``Next`` button when all parameters are filled.

.. image:: /_static/images/installation/setup_4.png
   :align: center

Select your event broker module.

Depending on the chosen module, you will be asked to enter some specific parameters. 

Case of Centreon Broker:

.. image:: /_static/images/installation/setup_4_2.png
   :align: center

Case of NDOUtils:

.. image:: /_static/images/installation/setup_4_3.png
   :align: center

Click on the ``Next`` button when all parameters are filled.

.. image:: /_static/images/installation/setup_5.png
   :align: center

Fill the form with your information. Make sure to remember your password as it will be used for logging in. Click on the ``Next`` button.


.. image:: /_static/images/installation/setup_6.png
   :align: center

Fill the form with information regarding your database setup and credentials that will be used for connection. Click on the ``Next`` button.

.. image:: /_static/images/installation/setup_7.png
   :align: center

The database structure will be installed during this process. Everything should pass and display ``OK``.

.. note::
  You can be asked to add the ``innodb_file_per_table=1`` parameter in the MySQL configuration file.

Click on the ``Next`` button.

.. image:: /_static/images/installation/setup_8.png
   :align: center

The installation is now finished, click on the ``Finish`` button, you will be redirected to the login screen:

.. image:: /_static/images/installation/login.png
   :align: center

Enter your credentials, you can now start configuring your monitoring system.
