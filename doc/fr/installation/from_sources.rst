.. _centreon_install:

====================
A partir des sources
====================

*********
Prérequis
*********

CentOS
======

.. warning::
    Cette procédure n'a pas été testée sur les versions 6.x des environnements CentOS et RHEL. Cependant cette dernière doit être compatible en modifiant les noms des paquets pour les adapter à la version 6.

Les environnements CentOS et RHEL ne possèdent pas en standard sur
dépôts l'intégralité des dépendances nécessaires à l'installation
de Centreon. Vous devez ajouter le dépôt *RPM Forge*

Système 32-bits :

  ::

    $ wget http://packages.sw.be/rpmforge-release/rpmforge-release-0.5.1-1.el5.rf.i386.rpm
    $ wget http://dag.wieers.com/rpm/packages/RPM-GPG-KEY.dag.txt

Système 64-bits :

  ::

    $ wget http://packages.sw.be/rpmforge-release/rpmforge-release-0.5.1-1.el5.rf.x86_64.rpm
    $ wget http://dag.wieers.com/rpm/packages/RPM-GPG-KEY.dag.txt

Utilisez votre éditeur de texte favori et supprimez la première
ligne du fichier *RPM-GPG-KEY.dag.txt*. La première ligne doit
contenir :

  ::
  
    "-----BEGIN PGP PUBLIC KEY BLOCK-----"

Puis exécutez les commandes suivantes :

  ::

    $ rpm --import RPM-GPG-KEY.dag.txt
    $ rpm -Uvh rpmforge-release-0.5.1-1.el5.rf.i386.rpm

Vous pouvez maintenant installer les dépendances nécessaires :

  ::

    $ yum update
    $ yum upgrade
    $ yum install httpd gd fontconfig-devel libjpeg-devel libpng-devel gd-devel perl-GD \
        openssl-devel perl-DBD-MySQL mysql-server mysql-devel php php-mysql php-gd php-ldap php-xml php-mbstring \
        perl-Config-IniFiles perl-DBI perl-DBD-MySQL rrdtool perl-rrdtool perl-Crypt-DES perl-Digest-SHA1 \
        perl-Digest-HMAC net-snmp-utils perl-Socket6 perl-IO-Socket-INET6 net-snmp net-snmp-libs php-snmp \
        dmidecode lm_sensors perl-Net-SNMP net-snmp-perl fping cpp gcc gcc-c++ libstdc++ glib2-devel \
        php-pear

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

    $ apt-get install sudo tofrodos bsd-mailx lsb-release mysql-server libmysqlclient15-dev libdatetime-perl \
        apache2 apache2-mpm-prefork php5 php5-mysql php-pear php5-ldap php5-snmp php5-gd php5-sqlite \
        rrdtool librrds-perl libconfig-inifiles-perl libcrypt-des-perl libdigest-hmac-perl \
        libdigest-sha1-perl libgd-gd2-perl snmp snmpd libnet-snmp-perl libsnmp-perl

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
        php5-ldap php5-snmp php5-gd php5-soap php5-posix php5-gettext php5-mbstring mysql \
        libmysqlclient-devel perl-DBD-mysql mysql-community-server rrdtool perl-Config-IniFiles \
        net-snmp perl-Net-SNMP perl-SNMP gd libjpeg-devel libpng-devel fontconfig-devel \
        freetype2-devel sudo mailx fping iputils dos2unix cron dejavu

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

Centreon est compatible avec les logiciels suivants :

* :ref:`Centreon Engine <centreon-engine:user_installation_using_sources>`
* `Nagios <http://nagios.sourceforge.net/docs/3_0/quickstart.html>`_
* `Icinga <http://docs.icinga.org/latest/en/>`_

Installez un de ces moteurs avant de poursuivre l'installation. N'oubliez pas d'installer les `Plugins Nagios <http://nagios.sourceforge.net/docs/3_0/quickstart.html>`_.

********************
Multiplexeur de flux
********************

Centreon est compatible avec les logiciels suivants :

* :ref:`Centreon Broker <centreon-broker:user_installation_using_sources>`
* `NDOUtils <http://nagios.sourceforge.net/docs/ndoutils/NDOUtils.pdf>`_

Installez un de ces multiplexeurs de flux avant de poursuivre l'installation.

********
Centreon
********

Téléchargez la dernière version de Centreon :ref:`ici <download_web_src>`.

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
    Le script d'installation permet une configuration personnalisée, cette procédure vous montrera les meilleurs chemins à utiliser. En outre, les questions rapides Yes/No peuvent être répondu par [y] la plupart du temps.

Contrôle de prérequis
---------------------

Si l'étape d'installation des prérequis s'est déroulée avec succès vous devriez 
avoir aucun problème lors de cette étape. Sinon reprendre la procédure 
d'installation des prérequis :
  ::

    ###############################################################################
    #                                                                             #
    #                         Centreon (www.centreon.com)                         #
    #                          Thanks for using Centreon                          #
    #                                                                             #
    #                                    v2.5.0                                   #
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

    Do you accept GPL license ?
    [y/n], default to [n]:
    > y

Composants principaux
---------------------

Répondre [y] à toutes les questions

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

Définition des chemins d'installation
-------------------------------------

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

Le fichier **RRDs.pm** peut être localisé n'importe où sur le serveur. 
Utilisez les commandes suivantes :

::

    $ updatedb
    $ locate RRDs.pm

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


Utilisateur et group centreon
-----------------------------

Le groupe d'applications **centreon**: Ce groupe est utilisé pour les droits d'accès
entre les différents logiciels Centreon::

  What is the Centreon group ? [centreon]
  default to [centreon]
  > 

  What is the Centreon user ? [centreon]
  default to [centreon]
  > 


Utilisateur de la supervision
-----------------------------

Cet utilisateur exécute le moteur de supervision :

Si vous utilisez Centreon Engine::

  What is the Monitoring engine user ?
  > centreon-engine

Si vous utilisez Nagios::

  What is the Monitoring engine user ?
  > nagios

Cet utilisateur exécute le multiplexeur de flux :

Si vous utilisez Centreon Broker::

  What is the Broker user ? (optional)
  > centreon-broker

Si vous utilisez NDOUtils::
  
  What is the Broker user ? (optional)
  > nagios

Répertoire des journaux d'évènements
------------------------------------

Si vous utilisez Centreon Engine::

  What is the Monitoring engine log directory ?
  > /var/log/centreon-engine

Si vous utilisez Nagios::

  What is the Monitoring engine log directory ?
  > /var/log/nagios

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

  Where is sudo configuration file
  default to [/etc/sudoers]
  > 
  /etc/sudoers                                               OK


Si vous utilisez Centreon Engine::

  What is the Monitoring engine init.d script ?
  > /etc/init.d/centengine

  What is the Monitoring engine binary ?
  > /usr/sbin/centengine

  What is the Monitoring engine configuration directory ?
  > /etc/centreon-engine

Si vous utilisez Nagios ::

  What is the Monitoring engine init.d script ?
  > /etc/init.d/nagios

  What is the Monitoring engine binary ?
  > /usr/sbin/nagios

  What is the Monitoring engine configuration directory ?
  > /etc/nagios

Si vous utilisez Centreon Broker::

  Where is the configuration directory for broker module ?
  > /etc/centreon-broker

  Where is the init script for broker module daemon ?
  > /etc/init.d/cbd

Si vous utilisez NDOUtils::
  
  Where is the configuration directory for broker module ?
  > /etc/nagios

  Where is the init script for broker module daemon ?
  > /etc/init.d/ndo2db


Configuration des droits::

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


Installation des modules pear
-----------------------------

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


Installation du composant Centcore
----------------------------------

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


Installation des plugins
------------------------

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


Fin de l'installation
---------------------

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

  
.. _installation_web:

Installation web
================

.. note::

   Vérifiez que les serveurs Apache et MySQL sont en cours d'exécution avant de poursuivre.

Ouvrez votre navigateur web favori et rendez-vous à l'adresse :

  ::

    http://SERVER_ADDRESS/centreon

Vous devriez voir la page suivante :

.. image:: /images/installation/setup_1.png
   :align: center

Cliquez sur le bouton **Next** : 

.. image:: /images/installation/setup_2.png
   :align: center

S'il manque un paquet installez-le et cliquer sur le bouton **Refresh**. Cliquez sur le bouton **Next** dès que tout est **OK** :


.. image:: /images/installation/setup_3_1.png
   :align: center

Sélectionnez votre moteur de supervision. Suivant la sélection, le paramétrage est différent.

Pour Centreon Engine :

.. image:: /images/installation/setup_3_2.png
   :align: center
   
Pour Nagios :

.. image:: /images/installation/setup_3_3.png
   :align: center

Cliquez sur le bouton **Next** dès que tous les champs sont remplis.

.. image:: /images/installation/setup_4.png
   :align: center

Sélectionnez votre multiplexeur de flux. Suivant la sélection, le paramétrage est différent.

Pour Centreon Broker :

.. image:: /images/installation/setup_4_2.png
   :align: center

Pour NDOUtils :

.. image:: /images/installation/setup_4_3.png
   :align: center

Cliquez sur le bouton **Next** dès que tous les champs sont remplis.

.. image:: /images/installation/setup_5.png
   :align: center

Remplissez le formulaire avec vos informations. Soyez sûre de vous souvenir de votre mot de passe. Cliquez sur le bouton **Next**.

.. image:: /images/installation/setup_6.png
   :align: center

Remplissez le formulaire avec les informations concernant la base de données. Cliquez sur le bouton **Next**.

.. image:: /images/installation/setup_7.png
   :align: center

La structure des bases de données va être créée durant cette étape. Tous doit être validé par **OK**.

.. note::
  Le processus d'installation peut vous demander de modifier le paramétrage du serveur MySQL pour ajouter **innodb_file_per_table=1** dans le fichier de configuration.

Cliquez sur le bouton **Next**.

.. image:: /images/installation/setup_8.png
   :align: center

L'installation est maintenant terminée, cliquez sur le bouton **Next**, vous allez être redirigé vers la page de connexion :

.. image:: /images/guide_utilisateur/aconnection.png
   :align: center

Entrez votre pseudo et mot de passe pour vous connecter.
