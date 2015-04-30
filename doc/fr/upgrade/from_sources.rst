.. _upgrade_from_sources:

====================
A partir des sources
====================

.. warning::
    Avant de mettre à jour Centreon, veuillez sauvegarder vos bases de données.

Pour mettre à jour Centreon depuis les sources, :ref:`télécharger <downloads>` la dernière version de Centreon.

******************
Installation shell
******************

Extraire le paquet :
  ::

  $ tar xvfz centreon-2.x.x.tar.gz

Se déplacer de répertoire :
  ::

  $ cd centreon-2.x.x

Exécuter le script :
  ::

  $ ./install -u /etc/centreon

Où **/etc/centreon** correspond au répertoire de configuration de centreon à rempalcer le cas échéant.

Contrôle des prérequis
----------------------

Si l'étape [Step 01] est réussie, vous devriez avoir aucun problème ici. Sinon
revenir à l'étape [Step 01] et installer les prérequis :

  ::

    ###############################################################################
    #                                                                             #
    #                         Centreon (www.centreon.com)                         #
    #                          Thanks for using Centreon                          #
    #                                                                             #
    #                                    v2.5.0                                   #
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

Chargement des paramètres d'installation précédents :
  ::

    Do you want to use the last Centreon install parameters ?
    [y/n], default to [y]:
    > y
    
    Using:  /etc/centreon/instCentCore.conf
    /etc/centreon/instCentPlugins.conf
    /etc/centreon/instCentStorage.conf
    /etc/centreon/instCentWeb.conf

Sélectionner les composants à mettre à jour :
  ::

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

De nouvelle informaitons sont nécessaires.

Le chemin vers les binaires de centreon :
  ::

    ------------------------------------------------------------------------
    	Start CentWeb Installation
    ------------------------------------------------------------------------
    
    Where is your Centreon binaries directory
    default to [/usr/local/centreon/bin]
    >
    Path /usr/local/centreon/bin                               OK

Le chemin de données supplémentaires pour Centreon Web :
  ::

    Where is your Centreon data information directory
    default to [/usr/local/centreon/data]
    > 
    
    Do you want me to create this directory ? [/usr/local/centreon/data]
    [y/n], default to [n]:
    > y
    Path /usr/local/centreon/data 
    /usr/bin/perl                                              OK
    Finding Apache user :                                      www-data
    Finding Apache group :                                     www-data

Le groupe applicatif Centreon. Ce groupe est utilisé pour les droits 
d'accès entre les applications Centreon :
  ::
    
    What is the Centreon group ? [centreon]
    default to [centreon]
    > 

    Do you want me to create this group ? [centreon]
    [y/n], default to [n]:
    > y

L'utilisateur applicatif Centreon :
  ::
    
    What is the Centreon user ? [centreon]
    default to [centreon]
    > 
    
    Do you want me to create this user ? [centreon]
    [y/n], default to [n]:
    > y


L'utilisateur du module broker. Cet utilisateur est utilisé pour ajouter 
des droits à Centreon sur les répertoires de configuration et journaux. 
Si vide, l'utilisateur du moteur de supervision sera utilisé.

Par exemple :

* Centreon Broker : *centreon-broker*

  :: 

    What is the Broker user ? (optional)
    > 

Le chemin vers les journaux. Par exmeple :

* Centeron Engine : */var/log/centreon-engine*

Le chemin vers les sondes de supervision :
  ::

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

Le chemin vers le script de démarrage de l'ordonnanceur. Par exemple :

* Centreon Engine : */etc/init.d/centengine*


Le chemin vers le répertoire de configuratino du broker. Par exemple : 

* Centreon Broker : */etc/centreon-broker*

Le chemin vers e script de démarrage du broker. Par exemple :

* Centreon Broker : */etc/init.d/cbd*


Remplacement ou non du fichier de droits utilisateurs.
Pour plus de sécurité, sauvegarder le fichier **/etc/sudoers**.

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

Mise à jour de Centreon Storage
-------------------------------

De nouvelle informaitons sont nécessaires.

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

Mise à jour Centreon Core
-------------------------

De nouvelle informations sont nécessaires.

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

Mise à jour des sondes Centreon
-------------------------------

De nouvelle informations sont nécessaires.

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

Fin de la mise à jour :
  ::

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

This step checks the dependencies on php modules.

.. image:: /_static/images/upgrade/step02.png
   :align: center

Notes de version
----------------

.. image:: /_static/images/upgrade/step03.png
   :align: center


Mise à jour des bases
---------------------

Cette étape met à jour le modèle des bases de données ainsi que les données, version par version.

.. image:: /_static/images/upgrade/step04.png
   :align: center

Finalisation
------------

.. image:: /_static/images/upgrade/step05.png
   :align: center
