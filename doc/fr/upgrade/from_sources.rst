.. _upgrade_from_sources:

====================
A partir des sources
====================

Pour mettre à jour Centreon depuis les sources, :ref:`télécharger <downloads>` la dernière version de Centreon.

******************
Installation shell
******************

Extraire le paquet ::

    $ tar zxf centreon-web-YY.MM.x.tar.gz

Se déplacer de répertoire ::

    $ cd centreon-web-YY.MM.x

Exécuter le script ::

  $ ./install -u /etc/centreon

Où **/etc/centreon** correspond au répertoire de configuration de centreon à rempalcer le cas échéant.

Contrôle des prérequis
----------------------

Si l'étape [Step 01] est réussie, vous devriez avoir aucun problème ici. Sinon
revenir à l'étape [Step 01] et installer les prérequis : ::

    ###############################################################################
    #                                                                             #
    #                         Centreon (www.centreon.com)                         #
    #                          Thanks for using Centreon                          #
    #                                                                             #
    #                                    vYY.MM.x                                 #
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

Choix des composants principaux
-------------------------------

Chargement des paramètres d'installation précédents : ::

    Do you want to use the last Centreon install parameters ?
    [y/n], default to [y]:
    > y

    Using:  /etc/centreon/instCentCore.conf
    /etc/centreon/instCentPlugins.conf
    /etc/centreon/instCentStorage.conf
    /etc/centreon/instCentWeb.conf

Sélectionner les composants à mettre à jour : ::

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

Mise à jour de l'interface web
------------------------------

De nouvelles informations sont nécessaires.

Le chemin vers les binaires de centreon : ::

   ------------------------------------------------------------------------
   	Start CentWeb Installation
   ------------------------------------------------------------------------

Le chemin des données supplémentaires de Centreon Web : ::

   Where is your Centreon data information directory
   default to [/usr/local/centreon/data]
   >

   Do you want me to create this directory ? [/usr/local/centreon/data]
   [y/n], default to [n]:
   > y
   Path /usr/local/centreon/data
   /usr/bin/composer                                          OK
   /usr/bin/perl                                              OK
   Check PHP version                                          OK
   Check PHP modules                                          OK
            ldap                                              OK
            xmlwriter                                         OK
            mbstring                                          OK
            pdo_mysql                                         OK
            pdo_sqlite                                        OK
            gd                                                OK
            intl                                              OK
   Finding Apache user :                                      www-data
   Finding Apache group :                                     www-data

Le chemin vers les sondes de supervision : ::

   Where is your monitoring plugins (libexec) directory ?
   default to [/usr/lib/nagios/plugins]
   >

   Path /usr/lib/nagios/plugins                               OK

   Where is your centreon plugins directory ?
   default to [/usr/lib/centreon/plugins]
   >
   Path /usr/lib/centreon/plugins                             OK
   Add group centreon to user www-data                        OK
   Add group centreon to user centreon-engine                 OK
   Add group centreon-engine to user www-data                 OK
   Add group centreon-engine to user centreon                 OK
   Add group www-data to user centreon                        OK

Configurer sudo
---------------

Remplacement ou non du fichier de droits utilisateurs.
Pour plus de sécurité, sauvegarder le fichier **/etc/sudoers**. ::

   ------------------------------------------------------------------------
   	Configure Sudo
   ------------------------------------------------------------------------

   What is the Monitoring engine init.d script ? [centengine]
   default to [centengine]
   >

   Where is your service command binary ?
   default to [/usr/sbin/service]
   >

   Your sudo has been configured previously

   Do you want me to reconfigure your sudo ? (WARNING)
   [y/n], default to [n]:
   > y
   Configuring Sudo                                           OK

Configuration d'Apache
----------------------

::

   ------------------------------------------------------------------------
   	Configure Apache server
   ------------------------------------------------------------------------
   Create '/etc/apache2/conf.d/centreon.conf'                 OK
   Configuring Apache                                         OK

   Do you want to reload your Apache ?
   [y/n], default to [n]:
   > y
   Reloading Apache service                                   OK

   What is the fpm-php service name ?
   default to [fpm-php]
   > php7.2-fpm
   The fpm-php service : php7.2-fpm

   Do you want to reload PHP FPM service ?
   [y/n], default to [n]:
   > y

   Preparing Centreon temporary files
   Change right on /var/log/centreon                          OK
   Change right on /etc/centreon                              OK
   Loading composer repositories with package information
   Updating dependencies
   Package operations: xx installs, yy updates, zz removals
   Writing lock file
   Generating autoload files
   Change macros for insertBaseConf.sql                       OK
   Change macros for sql update files                         OK
   Change macros for php files                                OK
   Change macros for php config files                         OK
   Change right on /etc/centreon-engine                       OK
   Add group centreon-broker to user www-data                 OK
   Add group centreon-broker to user centreon-engine          OK
   Add group centreon to user centreon-broker                 OK
   Change right on /etc/centreon-broker                       OK
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
   Change macros for centreon-backup.pl                       OK
   Install cron directory                                     OK
   Change right for eventReportBuilder.pl                     OK
   Change right for dashboardBuilder.pl                       OK
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

   ------------------------------------------------------------------------
   Pear Modules
   ------------------------------------------------------------------------
   Check PEAR modules
   PEAR                            1.4.9       1.10.6         OK
   DB                              1.7.6       1.9.2          OK
   Date                            1.4.6       1.4.7          OK
   All PEAR modules                                           OK

   ------------------------------------------------------------------------
   		Centreon Post Install
   ------------------------------------------------------------------------
   Create /usr/local/centreon/www/install/install.conf.php    OK
   Create /etc/centreon/instCentWeb.conf                      OK

Mise à jour de Centreon Storage
-------------------------------

De nouvelle informations sont nécessaires : ::

   ------------------------------------------------------------------------
         Start CentStorage Installation
   ------------------------------------------------------------------------
   Preparing Centreon temporary files
   /tmp/centreon-setup exists, it will be moved...
   install www/install/createTablesCentstorage.sql            OK
   CentStorage status Directory already exists                PASSED
   CentStorage metrics Directory already exists               PASSED
   Install logAnalyserBroker                                  OK
   Install nagiosPerfTrace                                    OK
   Change macros for centstorage.cron                         OK
   Install CentStorage cron                                   OK
   Change macros for centstorage.logrotate                    OK
   Install Centreon Storage logrotate.d file                  OK
   Create /etc/centreon/instCentStorage.conf                  OK

Mise à jour Centreon Storage
----------------------------

De nouvelle informations sont nécessaires : ::

   ------------------------------------------------------------------------
         Start CentStorage Installation
   ------------------------------------------------------------------------
   Preparing Centreon temporary files
   /tmp/centreon-setup exists, it will be moved...
   install www/install/createTablesCentstorage.sql            OK
   CentStorage status Directory already exists                PASSED
   CentStorage metrics Directory already exists               PASSED
   Install logAnalyserBroker                                  OK
   Install nagiosPerfTrace                                    OK
   Change macros for centstorage.cron                         OK
   Install CentStorage cron                                   OK
   Change macros for centstorage.logrotate                    OK
   Install Centreon Storage logrotate.d file                  OK
   Create /etc/centreon/instCentStorage.conf                  OK

Mise à jour Centreon Core
-------------------------

De nouvelle informations sont nécessaires : ::

   ------------------------------------------------------------------------
   	Start CentCore Installation
   ------------------------------------------------------------------------
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
   Create /etc/centreon/instCentCore.conf                     OK

Mise à jour des sondes Centreon
-------------------------------

De nouvelle informations sont nécessaires : ::

   ------------------------------------------------------------------------
   	  Starting Centreon Plugins Installation
   ------------------------------------------------------------------------

   Where is your monitoring plugins (libexec) directory ?
   default to [/usr/lib/nagios/plugins]
   >
   Path /usr/lib/nagios/plugins                               OK

   Where is your centreon plugins directory ?
   default to [/usr/lib/centreon/plugins]
   >
   Path /usr/lib/centreon/plugins                             OK
   Preparing Centreon temporary files
   Change macros for CentPlugins                              OK
   Installing the plugins                                     OK
   Change right on centreon.conf                              OK
   CentPlugins is installed
   Create /etc/centreon/instCentPlugins                       OK

Mise à jour de la gestion des traps SNMP
----------------------------------------

::

   ------------------------------------------------------------------------
   	Start CentPlugins Traps Installation
   ------------------------------------------------------------------------
   Finding Apache user :                                      www-data
   Preparing Centreon temporary files
   /tmp/centreon-setup exists, it will be moved...
   Change macros for snmptrapd.conf                           OK
   Replace CentreonTrapd init script macro                    OK
   Replace CentreonTrapd default script macro                 OK

   Do you want me to install CentreonTrapd init script ?
   [y/n], default to [n]:
   > y
   CentreonTrapd init script installed                        OK
   CentreonTrapd default script installed                     OK

   Do you want me to install CentreonTrapd run level ?
   [y/n], default to [n]:
   > y
   update-rc.d: using dependency based boot sequencing
   trapd Perl lib installed                                   OK

   Should I overwrite all your SNMP configuration files?
   [y/n], default to [n]:
   > y
   Install : snmptrapd.conf                                   OK
   Install : centreontrapdforward                             OK
   Install : centreontrapd                                    OK
   Change macros for centreontrapd.logrotate                  OK
   Install Centreon Trapd logrotate.d file                    OK
   Create /etc/centreon/instCentPlugins.conf                  OK

Fin de la mise à jour : ::

    ###############################################################################
    #                                                                             #
    #                 Go to the URL : http://localhost.localdomain/centreon/      #
    #                            to finish the setup                              #
    #                                                                             #
    #           Report bugs at https://github.com/centreon/centreon/issues        #
    #                                                                             #
    #                         Thanks for using Centreon.                          #
    #                          -----------------------                            #
    #                        Contact : infos@centreon.com                         #
    #                          http://www.centreon.com                            #
    #                                                                             #
    ###############################################################################

.. _upgrade_web:

****************
Installation Web
****************

Durant la mise à jour web suivre les instructions suivantes :

Présentation
------------

.. image:: /_static/images/upgrade/step01.png
   :align: center

Contrôle des dépendances
------------------------

Cette étape contrôle la liste des dépendances PHP.

.. image:: /_static/images/upgrade/step02.png
   :align: center

Notes de version
----------------

.. image:: /_static/images/upgrade/step03.png
   :align: center


Mise à jour des bases de données
--------------------------------

Cette étape met à jour le modèle des bases de données ainsi que les données, version par version.

.. image:: /_static/images/upgrade/step04.png
   :align: center

Finalisation
------------

.. image:: /_static/images/upgrade/step05.png
   :align: center
