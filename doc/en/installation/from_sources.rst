.. _centreon_install:

=============
Using sources
=============

*************
Prerequisites
*************

CentOS
======

Most CentOS users will find easier to install Centreon Web by using
:ref:`packages provided by Centreon <install_from_packages>`.

CentOS and RHEL environments do not possess as standard on archives all the
dependencies necessary for the installation of Centreon. You should add the
*RPM Forge* repository.

el7 system:

 ::

    $ wget http://repository.it4i.cz/mirrors/repoforge/redhat/el7/en/x86_64/rpmforge/RPMS/rpmforge-release-0.5.3-1.el7.rf.x86_64.rpm
    $ wget https://repository.it4i.cz/mirrors/repoforge/RPM-GPG-KEY.dag.txt


Use your favorite text editor and delete the first line of the RPM-GPG-KEY.dag.txt file. The first line should contain:

 ::

  "-----BEGIN PGP PUBLIC KEY BLOCK-----"

Then perform the following commands:

 ::

  $ rpm --import RPM-GPG-KEY.dag.txt
  $ rpm -Uvh rpmforge-release-0.5.3-1.el7.rf.x86_64.rpm

You can now install the necessary prerequisites::

  $ yum update
  $ yum upgrade
  $ yum install httpd gd fontconfig-devel libjpeg-devel libpng-devel gd-devel perl-GD perl-DateTime \
      openssl-devel perl-DBD-MySQL mysql-server mysql-devel php php-mysql php-gd php-ldap php-xml php-mbstring \
      perl-Config-IniFiles perl-DBI perl-DBD-MySQL rrdtool perl-rrdtool perl-Crypt-DES perl-Digest-SHA1 \
      perl-Digest-HMAC net-snmp-utils perl-Socket6 perl-IO-Socket-INET6 net-snmp net-snmp-libs php-snmp \
      dmidecode lm_sensors perl-Net-SNMP net-snmp-perl fping cpp gcc gcc-c++ libstdc++ glib2-devel \
      php-pear nagios-plugins

Additional commands are necessary to configure the environment correctly:

 ::

  $ usermod -U apache
  $ pear channel-update pear.php.net

If you can’t access the Internet directly but have to pass via a proxy, perform the following command:

 ::

  $ pear config-set http_proxy http://my_proxy.com:port

Then execute::

  $ pear upgrade-all

Debian jessie / Ubuntu 14.04
============================

.. note::
   Debian and Ubuntu latest version not yet supported.

Install the following prerequisites::

  $ apt-get install sudo tofrodos bsd-mailx lsb-release mysql-server libmysqlclient18 libdatetime-perl \
      apache2 apache2-mpm-prefork php5 php5-mysql php-pear php5-intl php5-ldap php5-snmp php5-gd php5-sqlite \
      rrdtool librrds-perl libconfig-inifiles-perl libcrypt-des-perl libdigest-hmac-perl \
      libdigest-sha-perl libgd-perl snmp snmpd libnet-snmp-perl libsnmp-perl nagios-plugins

To finish, you should install SNMP MIBs. Because of a license problem the MIB files are not available by default in Debian. To add them, change the /etc/apt/sources.list file and add the *non-free* category.

To Debian, then execute the following commands::

  $ apt-get update
  $ apt-get install snmp-mibs-downloader

Suse
====

Packages
--------

Install the following prerequisites::

  $ yast -i gcc gcc-c++ make automake apache2 php5 php5-mysql apache2-mod_php5 php5-pear \
      php5-ldap php5-snmp php5-gd php5-soap php5-intl php5-posix php5-gettext php5-mbstring mysql \
      libmysqlclient-devel perl-DBD-mysql mysql-community-server rrdtool perl-Config-IniFiles \
      net-snmp perl-Net-SNMP perl-SNMP gd libjpeg-devel libpng-devel fontconfig-devel \
      freetype2-devel sudo mailx fping iputils dos2unix cron dejavu nagios-plugins

On some OpenSuse distributions, the default settings of the **mine** type are not valid to function with the Centreon web interface. Edit the */etc/mime.types* file and find the lines:

 ::

  text/x-xsl xsl
  text/x-xslt xslt xsl

Replace them by:

 ::

  text/xml xsl
  text/xml xslt xsl

Save the file and restart Apache:

 ::

  /etc/init.d/apache2 restart

******************
Monitoring engine
******************


Centreon is tested and approved only for the monitoring engine :ref:`Centreon Engine <centreon-engine:user_installation_using_sources>`.

You can install it following the procedure in documentation. Don’t forget to install the
`Nagios plugins <http://nagios.sourceforge.net/docs/3_0/quickstart.html>`_ if you have not already done so.

******************
Stream Multiplexer
******************

Centreon is tested and approved only for the stream multiplexer :ref:`Centreon Broker <centreon-broker:user_installation_using_sources>`.

Install this Stream Multiplexers before continuing with the installation.

.. warning::
   Centreon Web is not compatible with Nagios monitoring engine.

********
Centreon
********

Download the latest version of Centreon-web `here <https://download.centreon.com>`_.


Shell Installation
==================

Extract the Centreon archive::

  tar zxf centreon-web-18.10.x.tar.gz

Change directory::

  cd centreon-web-18.10.x

Run the installation script::

  ./install.sh -i

.. note::

  The installation script allows customized configuration; this process will show you the best paths to use. Furthermore quick yes/no questions can be replied to by [y] most of the time.

.. note::

  If centreon sources have been downloaded from github, run those commands :
  composer install --no-dev --optimize-autoloader
  npm install
  npm run build

Prerequisites check
-------------------

If the Prerequisites installation step has been run successfully you should have no problem during this stage. Otherwise repeat the Prerequisites installation process:

 ::

  ###############################################################################
  #                                                                             #
  #                         Centreon (www.centreon.com)                         #
  #                          Thanks for using Centreon                          #
  #                                                                             #
  #                                    v2.8.0                                   #
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

    Do you accept GPLv2 license ?
    [y/n], default to [n]:
    > y

Main components
---------------

Answer [y] to all the questions.

::

  ------------------------------------------------------------------------
  	    Please choose what you want to install
  ------------------------------------------------------------------------

  Do you want to install Centreon Nagios Plugins ?
  [y/n], default to [n]:
  > y


  Definition of installation paths
  --------------------------------

::

  ------------------------------------------------------------------------
          Starting Centreon Web Installation
  ------------------------------------------------------------------------

  Where is your Centreon directory ?
  default to [/usr/local/share/centreon]
  >

::

  Do you want me to create this directory ? [/usr/local/share/centreon]
  [y/n], default to [n]:
  > y
  Path /usr/local/share/centreon                             OK

  Where is your Centreon log directory ?
  default to [/var/log/centreon]
  >

  Do you want me to create this directory ? [/var/log/centreon]
  [y/n], default to [n]:
  > y
  Path /var/log/centreon                                     OK

::

  Where is your Centreon configuration directory ?
  default to [/usr/local/etc/centreon]
  >

  Do you want me to create this directory ? [/usr/local/etc/centreon]
  [y/n], default to [n]:
  > y
  Path /usr/local/etc/centreon                               OK

  Where is your Centreon binaries directory ?
  default to [/usr/local/bin]
  >

  Where is your Centreon variable state information directory ?
  default to [/var/lib/centreon]
  >
  Path /var/lib/centreon/                                    OK

  Do you want me to create this directory ? [/var/lib/centreon]
  [y/n], default to [n]:
  > y
  Path /var/lib/centreon                                     OK

::

  /usr/bin/rrdtool                                           OK
  /usr/bin/mail                                              OK
  /usr/bin/php                                               OK
  /usr/share/php                                             OK
  /usr/bin/perl                                              OK
  Finding Apache user :                                      apache
  Finding Apache group :                                     apache


Centreon user and group
-----------------------

The Centreon applications group: this group is used for the access
rights between the various Centreon components.

::

  What is the Centreon group ? [centreon]
  default to [centreon]
  >

  What is the Centreon user ? [centreon]
  default to [centreon]
  >


Monitoring user
---------------

This is the user used to run the monitoring engine (Centreon Engine). If you followed the
`Centreon Engine official installation procedure <https://documentation.centreon.com/docs/centreon-engine/en/latest/installation/index.html#using-sources>`_
the user will likely be *centreon-engine*.

 ::

  What is your Centreon Engine user ?
  default to [centreon-engine]
  >

This is the user used to run the stream broker (Centreon Broker). If you followed the
`Centreon Broker official installation procedure <https://documentation.centreon.com/docs/centreon-broker/en/3.0/installation/index.html#using-sources>`_
the user will likely be *centreon-broker*.

 ::

  What is your Centreon Broker user ?
  default to [centreon-broker]
  >


Monitoring logs directory
-------------------------

 ::

  What is your Centreon Engine log directory ?
  default to [/var/log/centreon-engine]
  >


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

  Where is sudo configuration file ?
  default to [/etc/sudoers]
  >
  /etc/sudoers                                               OK

  What is your Centreon Engine startup command (init.d, service, ...) ?
  default to [service centengine]
  >

  Are you sure ? [service centengine]
  [y/n], default to [n]:
  > y

  Where is your Centreon Engine binary ?
  default to [/usr/sbin/centengine]
  >

  Where is your Centreon Engine configuration directory ?
  default to [/etc/centreon-engine]
  >

  Where is your Centreon Broker configuration directory ?
  default to [/etc/centreon-broker]
  >

  What is your Centreon Broker startup command (init.d, service, ...) ?
  default to [service cbd]
  >

  Are you sure ? [service cbd]
  [y/n], default to [n]:
  > y

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
  Change right on /usr/local/etc/centreon                    OK
  Change macros for insertBaseConf.sql                       OK
  Change macros for sql update files                         OK
  Change macros for php files                                OK
  Change macros for php config file                          OK
  Change macros for perl binary                              OK
  Change right on /etc/centreon-engine                       OK
  Change right on /etc/centreon-broker                       OK
  Add group centreon to user apache                          OK
  Add group centreon to user centreon-engine                 OK
  Add group centreon to user centreon                        OK
  Copy CentWeb in system directory                           OK
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
  Install cron directory                                     OK
  Change right for eventReportBuilder                        OK
  Change right for dashboardBuilder                          OK
  Change macros for centreon.logrotate                       OK
  Install Centreon logrotate.d file                          OK
  Prepare centFillTrapDB                                     OK
  Install centFillTrapDB                                     OK
  Prepare centreon_trap_send                                 OK
  Install centreon_trap_send                                 OK
  Prepare centreon_check_perfdata                            OK
  Install centreon_check_perfdata                            OK
  Prepare centreonSyncPlugins                                OK
  Install centreonSyncPlugins                                OK
  Prepare centreonSyncArchives                               OK
  Install centreonSyncArchives                               OK
  Prepare generateSqlLite                                    OK
  Install generateSqlLite                                    OK
  Install changeRrdDsName.pl                                 OK
  Prepare export-mysql-indexes                               OK
  Install export-mysql-indexes                               OK
  Prepare import-mysql-indexes                               OK
  Install import-mysql-indexes                               OK
  Prepare clapi binary                                       OK
  Install clapi binary                                       OK
  Centreon Web Perl lib installed                            OK


Pear module installation
------------------------

::

  ------------------------------------------------------------------------
  Pear Modules
  ------------------------------------------------------------------------
  Check PEAR modules
  PEAR                            1.4.9       1.10.1         OK
  DB                              1.7.6       1.9.2          OK
  DB_DataObject                   1.8.4       1.11.5         OK
  DB_DataObject_FormBuilder       1.0.0RC4    1.0.2          OK
  MDB2                            2.0.0       2.4.1          OK
  Date                            1.4.6       1.4.7          OK
  Archive_Tar                     1.1         1.3.11         OK
  Auth_SASL                       1.0.1       1.0.6          OK
  Console_Getopt                  1.2         1.3.1          OK
  Validate                        0.6.2       0.8.5          OK
  Log                             1.9.11      1.12.9         OK
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


Performance data component (Centstorage) installation
-----------------------------------------------------

::

  ------------------------------------------------------------------------
  	  Starting CentStorage Installation
  ------------------------------------------------------------------------

  Where is your Centreon Run Dir directory ?
  default to [/var/run/centreon]
  >

  Do you want me to create this directory ? [/var/run/centreon]
  [y/n], default to [n]:
  > y
  Path /var/run/centreon                                     OK

  Where is your CentStorage RRD directory ?
  default to [/var/lib/centreon]
  >
  Path /var/lib/centreon                                     OK
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
  install www/install/createTablesCentstorage.sql            OK
  Creating Centreon Directory '/var/lib/centreon/status'     OK
  Creating Centreon Directory '/var/lib/centreon/metrics'    OK
  Change right : /var/run/centreon                           OK
  Install logAnalyserBroker                                  OK
  Install nagiosPerfTrace                                    OK
  Change macros for centstorage.cron                         OK
  Install CentStorage cron                                   OK
  Change macros for centstorage.logrotate                    OK
  Install Centreon Storage logrotate.d file                  OK
  Create /usr/local/etc/centreon/instCentStorage.conf        OK


Poller communication subsystem (Centcore) installation
------------------------------------------------------

::

  ------------------------------------------------------------------------
  	  Starting CentCore Installation
  ------------------------------------------------------------------------
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
  Copy CentCore in binary directory                          OK
  Change right : /var/run/centreon                           OK
  Change right : /var/lib/centreon                           OK
  Change macros for centcore.logrotate                       OK
  Install Centreon Core logrotate.d file                     OK
  Replace CentCore init script Macro                         OK
  Replace CentCore sysconfig script Macro                    OK

  Do you want me to install CentCore init script ?
  [y/n], default to [n]:
  > y
  CentCore init script installed                             OK
  CentCore sysconfig script installed                        OK

  Do you want me to install CentCore run level ?
  [y/n], default to [n]:
  > y
  CentCore Perl lib installed                                OK
  Create /usr/local/etc/centreon/instCentCore.conf           OK


Centreon SNMP trap management installation
------------------------------------------

::

  ------------------------------------------------------------------------
   	  Starting CentreonTrapD Installation
  ------------------------------------------------------------------------

  Where is your SNMP configuration directory ?
  default to [/etc/snmp]
  >
  /etc/snmp                                                  OK
  Finding Apache user : apache
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
  Change macros for snmptrapd.conf                           OK
  Replace CentreonTrapd init script Macro                    OK
  Replace CentreonTrapd sysconfig script Macro               OK

  Do you want me to install CentreonTrapd init script ?
  [y/n], default to [n]:
  > y
  CentreonTrapd init script installed                        OK
  CentreonTrapd sysconfig script installed                   OK

  Do you want me to install CentreonTrapd run level ?
  [y/n], default to [n]:
  > y
  trapd Perl lib installed                                   OK
  Install : snmptrapd.conf                                   OK
  Install : centreontrapdforward                             OK
  Install : centreontrapd                                    OK
  Change macros for centreontrapd.logrotate                  OK
  Install Centreon Trapd logrotate.d file                    OK
  Create /usr/local/etc/centreon/instCentPlugins.conf        OK


Plugin installation
-------------------

::

  ------------------------------------------------------------------------
  	  Starting Centreon Plugins Installation
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
  Create /usr/local/etc/centreon/instCentPlugins.conf        OK


End
---

::

  ###############################################################################
  #                                                                             #
  #                 Go to the URL : http://localhost.localdomain/centreon/      #
  #                   	     to finish the setup                                #
  #                                                                             #
  #          Report bugs at https://github.com/centreon/centreon/issues         #
  #          Read documentation at https://documentation.centreon.com           #
  #                                                                             #
  #                         Thanks for using Centreon.                          #
  #                          -----------------------                            #
  #                        Contact : infos@centreon.com                         #
  #                          http://www.centreon.com                            #
  #                                                                             #
  ###############################################################################

PHP dependencies installation
-----------------------------

First, you need to install PHP dependency installer **composer**.
Composer can be downloaded `here <https://getcomposer.org/download/>` (it is also available in EPEL repository).

Once composer is installed, go to the centreon directory (usually /usr/share/centreon/) and run the following command :

 ::

    composer install --no-dev --optimize-autoloader


Javascript dependencies installation
------------------------------------

First, you need to install javascript runtime **nodejs**.
Installation instructions are available `here <https://nodejs.org/en/download/package-manager/>`.

Once nodejs is installed, go to the centreon directory (usually /usr/share/centreon/) and run the following commands :

 ::

    npm install
    npm run build
    npm prune --production


Any operating system
--------------------

SELinux should be disabled; for this, you have to modify the file "/etc/sysconfig/selinux" and replace "enforcing" by "disabled":

 ::

    SELINUX=disabled

After saving the file, please reboot your operating system to apply the changes.

PHP timezone should be set: go to `/etc/php.d` directory and create a file named `php-timezone.ini` which contains the following line:

 ::

    date.timezone = Europe/Paris

After saving the file, please don't forget to restart apache server.

The Mysql database server should be available to complete installation (locally or not). MariaDB is recommended.

After this step you should connect to Centreon to finalize the installation process.

.. include:: common/web_install.rst
