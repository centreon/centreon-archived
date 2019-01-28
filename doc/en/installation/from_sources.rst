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

Debian Stretch / Ubuntu 18.04
=============================

Add the php 7.1 repository :
For Debian Stretch :

  ::

    $ apt-get install apt-transport-https lsb-release ca-certificates
    $ wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
    $ echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" >> /etc/apt/sources.list.d/php.list
    $ apt-get update

For Ubuntu 18.04 :
.. note::
It is necessary to add sudo in front of the following commands
  ::

    $ apt-get install software-properties-common
    $ add-apt-repository ppa:ondrej/php
    $ apt update

Install the following prerequisites

  ::

    $ apt-get install php7.1 php7.1-opcache libapache2-mod-php7.1 php7.1-mysql php7.1-curl php7.1-json \
        php7.1-gd php7.1-mcrypt php7.1-intl php7.1-mbstring php7.1-xml php7.1-zip php7.1-fpm php7.1-readline \
        php7.1-sqlite3 php-pear sudo tofrodos bsd-mailx lsb-release mariadb-server libconfig-inifiles-perl \
        libcrypt-des-perl libdigest-hmac-perl libdigest-sha-perl libgd-perl php7.1-ldap php7.1-snmp php-db php-date

Activate the modules :

  ::

    $ a2enmod proxy_fcgi setenvif proxy rewrite
    $ a2enconf php7.1-fpm
    $ a2dismod php7.1
    $ systemctl restart apache2 php7.1-fpm

Additional commands are necessary to configure the environment correctly:

  ::

    $ groupadd -g 6000 centreon
    $ useradd -u 6000 -g centreon -m -r -d /var/lib/centreon -c "Centreon Admin" -s /bin/bash centreon

To finish, you should install SNMP MIBs. Because of a license problem the MIB files are not available by default in Debian. To add them, change the /etc/apt/sources.list file and add the *non-free* category.

Execute the following commands::

  ::

    $ apt-get update
    $ apt-get install snmp-mibs-downloader

Then modify the SNMP configuration file */etc/default/snmpd*
By adding :

  ::

    export MIBDIRS=/usr/share/snmp/mibs
    export MIBS=ALL

And commenting :

  ::

    #mibs ALL

Restart SNMP service :

  ::

    $ service snmpd restart
    $ service snmptrapd restart


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

  $ /etc/init.d/apache2 restart

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

  ::

    $ tar zxf centreon-web-18.10.x.tar.gz

Change directory::

  ::
    $ cd centreon-web-18.10.x

Run the installation script::

  ::
    $ ./install.sh -i

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
  #                                    v18.10.0                                 #
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

  Do you want to install : Centreon Web Front
  [y/n], default to [n]:
  > y

  Do you want to install : Centreon Centcore
  [y/n], default to [n]:
  > y

  Do you want to install : Centreon Nagios Plugins
  [y/n], default to [n]:
  > y

  Do you want to install : CentreonTrapd process
  [y/n], default to [n]:
  > y


Definition of installation paths
--------------------------------

  ::

  ------------------------------------------------------------------------
          Start CentWeb Installation
  ------------------------------------------------------------------------

  Do you want me to remove the centreon temporary working space to continue installation ?
  [y/n], default to [y]:
  > y

  Where is your Centreon directory ?
  default to [/usr/local/centreon]
  > /usr/share/centreon

  ::

  Do you want me to create this directory ? [/usr/share/centreon]
  [y/n], default to [n]:
  > y
  Path /usr/share/centreon                                   OK

  Where is your Centreon log directory ?
  default to [/usr/local/centreon/log]
  > /var/log/centreon

  Do you want me to create this directory ? [/var/log/centreon]
  [y/n], default to [n]:
  > y
  Path /var/log/centreon                                     OK

  ::

  Where is your Centreon etc directory ?
  default to [/etc/centreon]
  >

  Do you want me to create this directory ? [/etc/centreon]
  [y/n], default to [n]:
  > y
  Path /etc/centreon                                         OK

  Where is your Centreon variable state information directory ?
  default to [/var/lib/centreon]
  >

  Do you want me to create this directory ? [/var/lib/centreon]
  [y/n], default to [n]:
  > y
  Path /var/lib/centreon                                     OK

  Where is rrdtool
  default to [/usr/bin/rrdtool]
  > /opt/rrdtool-broker/bin/rrdtool
  /opt/rrdtool-broker/bin/rrdtool                            OK

  ::

  /usr/bin/mail                                              OK

  Where is your php binary ?
  default to [/usr/bin/php]
  >
  /usr/bin/php                                               OK

  Where is PEAR [PEAR.php]
  default to [/usr/share/pear/PEAR.php]
  >
  Path to /usr/share/php/PEAR.php                            OK
  /usr/bin/perl                                              OK
  Composer dependencies are installed                        OK
  Frontend application is built                              OK
  Enable Apache configuration                                OK
  Conf centreon already enabled
  Finding Apache user :                                      www-data
  Finding Apache group :                                     www-data



Centreon user and group
-----------------------

Le groupe d'applications **centreon** est utilisé pour les droits d'accès
entre les différents logiciels de la suite Centreon::

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

  What is the Monitoring engine user ? [centreon-engine]
  default to [centreon-engine]
  >

This is the user used to run the stream broker (Centreon Broker). If you followed the
`Centreon Broker official installation procedure <https://documentation.centreon.com/docs/centreon-broker/en/3.0/installation/index.html#using-sources>`_
the user will likely be *centreon-broker*.

  ::

  What is your Centreon Broker user ? [centreon-broker]
  default to [centreon-broker]
  >


Monitoring logs directory
-------------------------

  ::

  What is the Monitoring engine log directory ?[/var/log/centreon-engine]
  default to [/var/log/centreon-engine]
  >


Plugin path
-----------

  ::

  Where is your monitoring plugins (libexec) directory ?
  default to [/usr/lib/nagios/plugins]
  >
  Path /usr/lib/nagios/plugins                               OK
  Add group centreon to user www-data                        OK
  Add group centreon to user centreon-engine                 OK
  Add group centreon-engine to user www-data                 OK
  Add group centreon-engine to user centreon                 OK
  Add group www-data to user centreon                        OK


Sudo configuration
------------------

  ::

  ------------------------------------------------------------------------
  	  Configure Sudo
  ------------------------------------------------------------------------

  Where is sudo configuration file ?
  default to [/etc/sudoers.d/centreon]
  >
  /etc/sudoers.d/centreon                                    OK

  What is the Monitoring engine binary ? [/usr/sbin/centengine]
  default to [/usr/sbin/centengine]
  >

  Where is the Monitoring engine configuration directory ? [/etc/centreon-engine]
  default to [/etc/centreon-engine]
  >

  Where is the configuration directory for broker module ? [/etc/centreon-broker]
  default to [/etc/centreon-broker]
  >

  Where is your service command binary ?
  default to [/usr/sbin/service]
  >

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

  Finding Apache Centreon configuration file
  '/etc/apache2/conf-available/centreon.conf' :              OK

  Do you want to update Centreon Apache sub configuration file ?
  [y/n], default to [n]:
  > y
  Backup Centreon Apache configuration completed
  Create '/etc/apache2/conf-available/centreon.conf'         OK
  Configuring Apache                                         OK

  Do you want to reload your Apache ?
  [y/n], default to [n]:
  > y
  Reloading Apache service                                   OK


PHP FPM configuration
---------------------

  ::

  ------------------------------------------------------------------------
    	  Configure PHP FPM service
  ------------------------------------------------------------------------

  Finding PHP FPM Centreon configuration file
  'etc/php/7.1/fpm/pool.d/centreon.conf' :                   OK

  Do you want to update Centreon PHP FPM sub configuration file ?
  [y/n], default to [n]:
  > y
  Backup Centreon PHP FPM configuration completed
  Create 'etc/php/7.1/fpm/pool.d/centreon.conf'              OK
  Configuring PHP FPM                                        OK

  Do you want to reload PHP FPM service ?
  [y/n], default to [n]:
  > y
  Reloading PHP FPM service                                  OK

  Preparing Centreon temporary files
  Change right on /var/log/centreon                          OK
  Change right on /etc/centreon                              OK
  Change macros for insertBaseConf.sql                       OK
  Change macros for sql update files                         OK
  Change macros for php files                                OK
  Change macros for php config file                          OK
  Change macros for perl binary                              OK
  Change right on /etc/centreon-engine                       OK
  Add group centreon-broker to user www-data                 OK
  Add group centreon-broker to user centreon-engine          OK
  Add group centreon to user centreon-broker                 OK
  Change right on /etc/centreon-broker                       OK
  Copy CentWeb in system directory                           OK
  Install CentWeb (web front of centreon)                    OK
  Change right for install directory                         OK
  Install libraries                                          OK
  Write right to Smarty Cache                                OK
  Change macros for centreon.cron                            OK
  Install Centreon cron.d file                               OK
  Change macros for centAcl.php                              OK
  Change macros for downtimeManager.php                      OK
  Change macros for centreon-backup.pl                       OK
  Install cron directory                                     OK
  Change right for eventReportBuilder                        OK
  Change right for dashboardBuilder                          OK
  Change right for centreon-backup.pl                        OK
  Change right for centreon-backup-mysql.pl                  OK
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
  PEAR                            1.4.9       1.10.6         OK
  DB                              1.7.6       1.9.2          OK
  Date                            1.4.6       1.4.7          OK
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

  Where is your Centreon Run Dir directory?
  default to [/var/run/centreon]
  >

  Do you want me to create this directory ? [/var/run/centreon]
  [y/n], default to [n]:
  > y
  Path /var/run/centreon                                     OK

  Where is your CentStorage binary directory ?
  default to [/usr/share/centreon/bin]
  >
  Path /usr/share/centreon/bin                               OK

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
  Create /etc/centreon/instCentStorage.conf                  OK


Poller communication subsystem (Centcore) installation
------------------------------------------------------

  ::

  ------------------------------------------------------------------------
  	  Starting CentCore Installation
  ------------------------------------------------------------------------
  Where is your Centreon binary directory
  default to [/usr/share/centreon/bin]
  >

  Do you want me to create this directory ? [/usr/share/centreon/bin]
  [y/n], default to [n]:
  > y
  Path /usr/share/centreon/bin                               OK
  Preparing Centreon temporary files
  /tmp/centreon-setup exists, it will be moved...
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
  CentCore Perl lib installed                                OK
  Create /etc/centreon/instCentCore.conf                     OK


Plugin installation
-------------------

  ::

  ------------------------------------------------------------------------
  	  Starting Centreon Plugins Installation
  ------------------------------------------------------------------------
  Path /var/lib/centreon/centplugins                         OK
  Path                                                       OK
  Path                                                       OK

  Where is your CentPlugins lib directory
  default to [/var/lib/centreon/centplugins]
  >

  Do you want me to create this directory ? [/var/lib/centreon/centplugins]
  [y/n], default to [n]:
  > y
  Path /var/lib/centreon/centplugins                         OK
  Create /etc/centreon/instCentPlugins.conf                  OK


Centreon SNMP trap management installation
------------------------------------------

  ::

  ------------------------------------------------------------------------
   	  Starting CentreonTrapD Installation
  ------------------------------------------------------------------------

  Do you want me to remove the centreon temporary working space to continue installation ?
  [y/n], default to [y]:
  > y
  Path                                                       OK
  Path                                                       OK

  Where is your SNMP configuration directory ?
  default to [/etc/snmp]
  >
  /etc/snmp                                                  OK

  Where is your CentreonTrapd binaries directory ?
  default to [/usr/local/centreon/bin]
  > /usr/share/centreon/bin
  /usr/share/centreon/bin                                    OK

  Finding Apache user :                                      www-data
  Preparing Centreon temporary files
  Change macros for snmptrapd.conf                           OK
  Replace CentreonTrapd init script Macro                    OK
  Replace CentreonTrapd default script Macro                 OK

  Do you want me to install CentreonTrapd init script ?
  [y/n], default to [n]:
  > y
  CentreonTrapd init script installed                        OK
  CentreonTrapd default script installed                     OK

  Do you want me to install CentreonTrapd run level ?
  [y/n], default to [n]:
  > y
  trapd Perl lib installed                                   OK
  Install : snmptrapd.conf                                   OK
  Install : centreontrapdforward                             OK
  Install : centreontrapd                                    OK
  Change macros for centreontrapd.logrotate                  OK
  Install Centreon Trapd logrotate.d file                    OK
  Create /etc/centreon/instCentPlugins.conf                  OK


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


Macro modifications
------------------

Macros may not have been properly replaced. Run the following commands to correct their values :

  ::

    $ sed -i -e 's/_CENTREON_PATH_PLACEHOLDER_/centreon/g' /usr/share/centreon/www/index.html
    $ sed -i -e 's/@PHP_BIN@/\/usr\/bin\/php/g' /usr/share/centreon/bin/centreon
    $ sed -i -e 's/@PHP_BIN@/\/usr\/bin\/php/g' /usr/share/centreon/bin/export-mysql-indexes
    $ sed -i -e 's/@PHP_BIN@/\/usr\/bin\/php/g' /usr/share/centreon/bin/generateSqlLite
    $ sed -i -e 's/@PHP_BIN@/\/usr\/bin\/php/g' /usr/share/centreon/bin/import-mysql-indexes

Javascript dependencies installation
------------------------------------

First, you need to install javascript runtime **nodejs**.
Installation instructions are available `here <https://nodejs.org/en/download/package-manager/>`.

Once nodejs is installed, copy the JSON files to the installation folder :

  ::

    $ cp /usr/local/src/centreon-web-18.10.2/package* /usr/share/centreon/

Then go to the centreon directory (usually /usr/share/centreon/) and run the following commands :

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
