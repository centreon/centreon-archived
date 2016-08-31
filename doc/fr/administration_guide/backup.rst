======
Backup
======

**************
Fonctionnement
**************

Exécution journalière
=====================

Le script de sauvegarde est exécuté de manière journalière par une tâche planifiée située dans **/etc/cron.d/centreon**::

    ##########################
    # Cron for Centreon-Backup
    30 3 * * * root /usr/share/centreon/cron/centreon-backup.pl >> /var/log/centreon/centreon-backup.log 2&>1

Chaque jour à 3H30, Le zscript de sauvegarde vérifie sur une sauvegarde doit être réalisée ce jour.

Types de sauvegarde
===================

Il y a deux types de sauvegarde : base de données et fichiers de configuration.

Sauvegarde de la base de données
--------------------------------

La sauvegarde de la base de données peut être réalisée sur deux bases : **centreon** and **centreon_storage**

Il y a deux types de sauvegarde :

* MySQLdump : la commande mysqldump est utilisée pour sauvegarder la base de donnees. Attention, cette commande peut prendre un certain temps si la base est volumineuse.
* LVM Snapshot : Copie binaire des fichiers MySQL. Vous devez avoir un volume logique dédiée à MySQL (ex: /var/lib/mysql) et 1Go d'espace disponible dans son groupe de volumes.

Format de la sauvegarde :

* yyyy-mm-dd-centreon.sql.gz
* yyyy-mm-dd-centreon_storage.sql.gz

Sauvegarde des fichiers de configuration
----------------------------------------

Tous les fichiers de configuration du serveur central sont sauvegardés : MySQL, Zend, Apache, PHP, SNMP, centreon, centreon-broker

Format de la sauvegarde :

* yyyy-mm-dd-Monitoring-Engine.tar.gz (fichiers de configuration centreon-engine)
* yyyy-mm-dd-Central.tar.gz (autres fichiers de configuration)


*************
Configuration
*************

Ce chapitre décrit la configuration de centreon-backup.

#. Se rendre dans le menu: **Administration** ==> **Paramètres** ==> **Backup**

La fenêtre suivante est affichée:

.. image:: /images/guide_exploitation/backup.png
   :align: center

* **Backup enabled** Activer/Désactiver la sauvegarde
* **Backup directory** Répertoire de stockage des sauvegardes
* **Temporary directory** Répertoire utilisé durant le processus de sauvegarde
* **Backup database centreon** Activer la sauvegarde de la base de données centreon
* **Backup database centreon_storage** Activer la sauvegarde de la base de données centreon_storage
* **Backup type** Type de sauvegarde (MySQLdump or LVM snapshot)
* **Full backup** Période pour la sauvegarde complète
* **Partial backup** Période pour la sauvegarde partielle (seulement disponible pour la sauvegarde par LVM snapshot)
* **Backup retention** Durée de rétention des sauvegardes (en jours)
* **Backup configuration files** Activer la sauvegarde des fichiers de configuration
* **MySQL configuration file path** Chemin d'accès au fichier de configuration MySQL
* **Zend configuration file path** Chemin d'accès au fichier de configuration Zend
* **SCP export enabled** Activer l'export des sauvegardes par SCP
* **Remote user** Utilisateur distant pour l'export SCP
* **Remote host** Hôte distant pour l'export SCP
* **Remote directory** Répertoire distant pour l'export SCP
