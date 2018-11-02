.. _centreon_install:

====================
A partir des sources
====================

*********
Prérequis
*********

CentOS
======

La plupart des utilisateurs de CentOS préfèreront installer Centreon Web
en utilisant :ref:`les paquets fournis par Centreon <install_from_packages>`.

Les environnements CentOS et RHEL ne possèdent pas en standard sur
dépôts l'intégralité des dépendances nécessaires à l'installation
de Centreon. Vous devez ajouter le dépôt *RPM Forge*

Système el7 :

 ::

    $ wget http://repository.it4i.cz/mirrors/repoforge/redhat/el7/en/x86_64/rpmforge/RPMS/rpmforge-release-0.5.3-1.el7.rf.x86_64.rpm
    $ wget https://repository.it4i.cz/mirrors/repoforge/RPM-GPG-KEY.dag.txt


Utilisez votre éditeur de texte favori et supprimez la première
ligne du fichier *RPM-GPG-KEY.dag.txt*. La première ligne doit
contenir :

  ::

    "-----BEGIN PGP PUBLIC KEY BLOCK-----"

Puis exécutez les commandes suivantes :

  ::

    $ rpm --import RPM-GPG-KEY.dag.txt
    $ rpm -Uvh rpmforge-release-0.5.3-1.el7.rf.x86_64.rpm

Vous pouvez maintenant installer les dépendances nécessaires :

  ::

    $ yum update
    $ yum upgrade
    $ yum install httpd gd fontconfig-devel libjpeg-devel libpng-devel gd-devel perl-GD perl-DateTime \
        openssl-devel perl-DBD-MySQL mysql-server mysql-devel php php-mysql php-gd php-ldap php-xml php-mbstring \
        perl-Config-IniFiles perl-DBI perl-DBD-MySQL rrdtool perl-rrdtool perl-Crypt-DES perl-Digest-SHA1 \
        perl-Digest-HMAC net-snmp-utils perl-Socket6 perl-IO-Socket-INET6 net-snmp net-snmp-libs php-snmp \
        dmidecode lm_sensors perl-Net-SNMP net-snmp-perl fping cpp gcc gcc-c++ libstdc++ glib2-devel \
        php-pear nagios-plugins

Des commandes additionnelles sont nécessaires pour configurer correctement l'environnement :

  ::

    $ usermod -U apache
    $ pear channel-update pear.php.net

Si vous ne pouvez pas accéder directement à Internet directement mais passer par un proxy, exécutez la commande suivante :

  ::

    $ pear config-set http_proxy http://my_proxy.com:port

Puis exécutez :

  ::

    $ pear upgrade-all

Debian / Ubuntu
===============

Installez les dépendances nécessaires :

::

  $ apt-get install sudo tofrodos bsd-mailx lsb-release mysql-server libmysqlclient18 libdatetime-perl \
      apache2 apache2-mpm-prefork php5 php5-mysql php-pear php5-intl php5-ldap php5-snmp php5-gd php5-sqlite \
      rrdtool librrds-perl libconfig-inifiles-perl libcrypt-des-perl libdigest-hmac-perl \
      libdigest-sha-perl libgd-perl snmp snmpd libnet-snmp-perl libsnmp-perl nagios-plugins

Pour finir, vous devez installer des MIBs SNMP. En raison d'un problème de licence,
les fichiers MIBs ne sont pas disponibles par défaut sous Debian. Pour les ajouter,
modifiez le fichier */etc/apt/sources.list* et ajouter la catégorie **non-free**.

Puis exécutez les commandes suivantes :

  ::

    $ apt-get update
    $ apt-get install snmp-mibs-downloader

Suse
====

Installez les dépendances nécessaires :

  ::

    $ yast -i gcc gcc-c++ make automake apache2 php5 php5-mysql apache2-mod_php5 php5-pear \
        php5-ldap php5-snmp php5-gd php5-soap php5-posix php5-intl php5-gettext php5-mbstring mysql \
        libmysqlclient-devel perl-DBD-mysql mysql-community-server rrdtool perl-Config-IniFiles \
        net-snmp perl-Net-SNMP perl-SNMP gd libjpeg-devel libpng-devel fontconfig-devel \
        freetype2-devel sudo mailx fping iputils dos2unix cron dejavu nagios-plugins

Sur certaines distributions OpenSuse, le paramétrage par défaut des
type **mine** n'est pas valide pour fonctionner avec l'interface web
Centreon. Editez le fichier */etc/mime.types* et rechercher les lignes :

  ::

    text/x-xsl xsl
    text/x-xslt xslt xsl

Remplacez-les par :

  ::

    text/xml xsl
    text/xml xslt xsl

Sauvegardez le fichier et redémarrez apache :

  ::

    $ /etc/init.d/apache2 restart

*********************
Moteur de supervision
*********************

Centreon est testé et validé uniquement pour le moteur de supervision :ref:`Centreon Engine <centreon-engine:user_installation_using_sources>`.

Installez ce moteur avant de poursuivre l'installation. N'oubliez pas d'installer les
`Plugins Nagios <http://nagios.sourceforge.net/docs/3_0/quickstart.html>`_ si vous ne l'avez pas déjà fait.

.. warning::
   Centreon Web n'est pas compatible avec le moteur de supervision Nagios.

********************
Multiplexeur de flux
********************

Centreon est testé et validé uniquement pour le multiplexeur de flux :ref:`Centreon Broker <centreon-broker:user_installation_using_sources>`.

Installez ce multiplexeurs de flux avant de poursuivre l'installation.

********
Centreon
********

Téléchargez la dernière version de Centreon `<https://download.centreon.com/>`_ .

Installation shell
==================

Extraire Centreon de l'archive :

 ::

	$ tar zxf centreon-2.x.x.tar.gz

Déplacez-vous dans le répertoire extrait :

  ::

    $ cd centreon-2.x.x

Exécutez le script d'installation :

  ::

    $ ./install.sh -i

.. note::
    Le script d'installation permet une configuration personnalisée, cette procédure vous montrera les meilleurs chemins à utiliser. En outre, les questions rapides Yes/No peuvent être répondues par [y] la plupart du temps.

Contrôle de prérequis
---------------------

Si l'étape d'installation des prérequis s'est déroulée avec succès, vous ne devriez
avoir aucun problème lors de cette étape. Sinon, reprennez la procédure
d'installation des prérequis :

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

Acceptation de la licence
-------------------------

::

    This General Public License does not permit incorporating your program into
    proprietary programs.  If your program is a subroutine library, you may
    consider it more useful to permit linking proprietary applications with the
    library.  If this is what you want to do, use the GNU Library General
    Public License instead of this License.

    Do you accept GPLv2 license ?
    [y/n], default to [n]:
    > y


Composants principaux
---------------------

Répondez [y] à toutes les questions

::

  ------------------------------------------------------------------------
  	    Please choose what you want to install
  ------------------------------------------------------------------------

  Do you want to install Centreon Nagios Plugins ?
  [y/n], default to [n]:
  > y


Définition des chemins d'installation
-------------------------------------

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


Utilisateur et group centreon
-----------------------------

Le groupe d'applications **centreon** est utilisé pour les droits d'accès
entre les différents logiciels de la suite Centreon::

  What is the Centreon group ? [centreon]
  default to [centreon]
  >

  What is the Centreon user ? [centreon]
  default to [centreon]
  >


Utilisateur de la supervision
-----------------------------

Cet utilisateur exécute le moteur de supervision Centreon Engine. Si vous avez suivi
`la procédure d'installation officielle de Centreon Engine <https://documentation.centreon.com/docs/centreon-engine/en/latest/installation/index.html#using-sources>`_
l'utilisateur sera vraisemblablement *centreon-engine*.

::

  What is your Centreon Engine user ?
  default to [centreon-engine]
  >

Cet utilisateur exécute le multiplexeur de flux Centreon Broker. Si vous avez suivi
`la procédure d'installation officielle de Centreon Broker <https://documentation.centreon.com/docs/centreon-broker/en/3.0/installation/index.html#using-sources>`_
l'utilisateur sera vraisemblablement *centreon-broker*.

::

  What is your Centreon Broker user ?
  default to [centreon-broker]
  >

Répertoire des journaux d'évènements
------------------------------------

::

  What is your Centreon Engine log directory ?
  default to [/var/log/centreon-engine]
  >

Répertoire des plugins
----------------------

::

  Where is your monitoring plugins (libexec) directory ?
  default to [/usr/lib/nagios/plugins]
  >
  Path /usr/lib/nagios/plugins                               OK
  Add group centreon to user apache                          OK
  Add group centreon to user centreon-engine                 OK
  Add group centreon-engine to user apache                   OK
  Add group centreon-engine to user centreon                 OK


Configuration des droits sudo
-----------------------------

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


Configuration du serveur Apache
-------------------------------

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


Installation des modules pear
-----------------------------

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


Installation du fichier de configuration
----------------------------------------

::

  ------------------------------------------------------------------------
  		  Centreon Post Install
  ------------------------------------------------------------------------
  Create /usr/share/centreon/www/install/install.conf.php    OK
  Create /etc/centreon/instCentWeb.conf                      OK



Installation du composant Centstorage
-------------------------------------

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


Installation du composant Centcore
----------------------------------

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

Installation du système de gestion des traps SNMP (CentreonTrapD)
-----------------------------------------------------------------

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


Installation des plugins
------------------------

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


Fin de l'installation
---------------------

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

Installation des dépendances PHP
--------------------------------

Tout d'abord, vous devez installer l'installeur de dépendance PHP **composer**.
Composer peut être téléchargé `ici <https://getcomposer.org/download/>` (celui-ci est également disponible dans les dépôts EPEL).

Une fois que composer est installé, rendez-vous dans les répertoires Centreon (habituellement /usr/share/centreon/) et exécutez la commande suivante :

 ::

    composer install --no-dev --optimize-autoloader


Installation des dépendances Javascript
---------------------------------------

Tout d'abord, vous devez installer l'environnement d'exécution javscript **nodejs**.
Les instructions d'installation sont disponibles `ici <https://nodejs.org/en/download/package-manager/>`.

Une fois que nodejs est installé, rendez vous dans les répertoire centreon (habituellement /usr/share/centreon/) et exécutez les commandes suivantes :

 ::

    npm install
    npm run build
    npm prune --production


Pour tous les OS
----------------

SELinux doit être désactivé. Pour cela, vous devez modifier le fichier "/etc/sysconfig/selinux" et remplacer "enforcing" par "disabled" comme dans l'exemple suivant :

  ::

    SELINUX=disabled

Après avoir sauvegardé le fichier, veuillez redémarrer votre système d'exploitation pour prendre en compte les changements.

La timezone par défaut de PHP doit être configurée. Pour cela, allez dans le répertoire /etc/php.d et créez un fichier nommé php-timezone.ini contenant la ligne suivante :

  ::

    date.timezone = Europe/Paris

Après avoir sauvegardé le fichier, n'oubliez pas de redémarrer le service apache de votre serveur.

La base de données MySQL doit être disponible pour pouvoir continuer l'installation (localement ou non). Pour information, nous recommandons MariaDB.

.. include:: common/web_install.rst
