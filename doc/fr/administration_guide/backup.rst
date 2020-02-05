============================
Sauvegarde de la plate-forme
============================

**************
Fonctionnement
**************

Exécution journalière
=====================

Le script de sauvegarde est exécuté de manière journalière par une tâche planifiée située dans **/etc/cron.d/centreon**::

    ##########################
    # Cron for Centreon-Backup
    30 3 * * * root /usr/share/centreon/cron/centreon-backup.pl >> /var/log/centreon/centreon-backup.log 2&>1

Chaque jour à 3H30, Le script de sauvegarde vérifie sur une sauvegarde doit être réalisée ce jour.

Types de sauvegarde
===================

Il y a deux types de sauvegarde : base de données et fichiers de configuration.

Sauvegarde de la base de données
--------------------------------

La sauvegarde de la base de données peut être réalisée sur deux bases : **centreon** et **centreon_storage**

Il y a deux types de sauvegarde :

* MySQLdump : la commande mysqldump est utilisée pour sauvegarder la base de données. Attention, cette commande peut prendre un certain temps si la base est volumineuse.
* LVM Snapshot : Copie binaire des fichiers MySQL. Vous devez avoir un volume logique dédiée à MySQL (ex: /var/lib/mysql) et 1Go d'espace disponible dans son groupe de volumes.

Format de la sauvegarde :

* yyyy-mm-dd-centreon.sql.gz
* yyyy-mm-dd-centreon_storage.sql.gz

Sauvegarde des fichiers de configuration
----------------------------------------

Tous les fichiers de configuration du serveur central sont sauvegardés : MySQL, Apache, PHP, SNMP, centreon, centreon-broker

Format de la sauvegarde :

* yyyy-mm-dd-Monitoring-Engine.tar.gz (fichiers de configuration centreon-engine)
* yyyy-mm-dd-Central.tar.gz (autres fichiers de configuration)

*************
Configuration
*************

Ce chapitre décrit la configuration de centreon-backup.

#. Se rendre dans le menu: **Administration > Paramètres > Backup**

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
* **SCP export enabled** Activer l'export des sauvegardes par SCP
* **Remote user** Utilisateur distant pour l'export SCP
* **Remote host** Hôte distant pour l'export SCP
* **Remote directory** Répertoire distant pour l'export SCP

.. warning::
    **Temporary directory** ne peut pas être un sous répertoire de **Backup directory**. 

******************************************
Restauration d'un serveur central Centreon
******************************************

Le processus de restauration consiste en deux étapes :

* Réinstaller la plate-forme suivant le documentation d'installation de Centreon. Ne pas oublier de faire la mise à jour du système.
* Restaurer les différents fichiers de configuration, puis les bases de données Centreon.

Restauration des fichiers de configuration de Centreon
======================================================

Avant de restaurer les bases de données, il faudra restaurer certains fichiers de configuration dans un premier temps::

    # cd /var/backup
    # tar -xvf AAAA-MM-JJ-central.tar.gz
    # cd backup/central/etc/centreon
    # cp * /etc/centreon/

Restauration des bases de données
=================================

Une fois le serveur Centreon réinstallé (**même version de Centreon**), il suffit de décompresser les sauvegardes des bases de données centreon et centreon_storage::

    # mysql
    mysql> drop database centreon;
    mysql> drop database centreon_storage;
    mysql> CREATE database centreon;
    mysql> CREATE database centreon_storage;
    mysql> GRANT ALL ON centreon.* TO 'centreon'@'<adresse_ip_centreon>' IDENTIFIED BY 'password' ;
    mysql> GRANT ALL ON centreon_storage.* TO 'centreon'@'<adresse_ip_centreon>' IDENTIFIED BY 'password' ;
    mysql> exit;
    # gzip -d AAAA-MM-JJ-centreon.sql.gz
    # mysql centreon < AAAA-MM-JJ-centreon.sql
    # gzip -d AAAA-MM-JJ-centreon_storage.sql.gz
    # mysql centreon_storage < AAAA-MM-JJ-centreon_storage.sql

Ces opérations peuvent prendre un certain temps du fait de la taille de la base "centreon_storage".

.. note::
    Le mot de passe (**password** ci-dessus), est stocké dans les fichiers de configuration restaurés précédemment. Par exemple le champ **$mysql_passwd** dans le fichier "/etc/centreon/conf.pm".


.. note::
    Par défaut, il n'y a pas de mot de passe pour le compte root de mysql lors de l'installation d'un serveur via Centreon ISO.

La manipulation ci-dessus est valide pour des versions identiques de Centreon.

Restauration des clés SSH
=========================

Cette étape consiste à restaurer les clés SSH de l'utilisateur **centreon**, voir **centreon-engine** dans le cadre d'un environnement distribué.
Leur restauration doit être manuelle. Il faut donc dans un premier temps extraire cette archive dans un répertoire temporaire puis déplacer un à un les fichiers suivant leur emplacement.

Sur le serveur central::

    # cd /var/backup
    # tar -xvf AAAA-MM-JJ-centreon-engine.tar.gz
    # cd backup/ssh
    # mkdir -p /var/spool/centreon/.ssh/
    # chmod 700 /var/spool/centreon/.ssh/
    # cp -p id_rsa /var/spool/centreon/.ssh/
    # cp -p id_rsa.pub /var/spool/centreon/.ssh/

Test de connexion du central central vers les satellites::

    # su - centreon
    # ssh <adresse_ip_poller>

Répondre "Oui" à la question.

.. note::
    Cette opération est à effectuer si et seulement si votre plate-forme est en mode distribuée.

Restauration des plugins
========================

Les plugins ont été sauvegardés dans l'archive : "AAAA-MM-JJ-centreon-engine.tar.gz". Leur restauration doit être manuelle.
Il faut donc dans un premier temps extraire cette archive dans un répertoire temporaire puis déplacer un à un les fichiers suivant leur emplacement.

Sur chaque collecteur, il faudra réaliser l'action suivante :

::

 # cd /var/backup
 # tar -xvf AAAA-MM-JJ-centreon-engine.tar.gz
 # cd backup/plugins
 # cp -pRf * /usr/lib/nagios/plugins

Restauration des scripts d'initialisation
=========================================

Certains points de contrôles concernant Oracle ou SAP entraînent la modification du script d'initialisation de l'ordonnanceur afin d'y ajouter des variables d'environnements.
Si vous avez modifié le script d'initialisation de votre ordonnanceur, il faudra le restaurer.

Dans un premier temps extraire cette archive dans un répertoire temporaire puis déplacer un à un les fichiers suivant leurs emplacements::

    # cd /var/backup
    # tar -xvf AAAA-MM-JJ-centreon-engine.tar.gz
    # cd backup
    # cp init_d_centengine /etc/init.d/centengine

Restauration des agents de supervision
======================================

Si vous utilisez les agents NRPE, ou NSCA il faudra les réinstaller puis restaurer leur configuration::

    # cd /var/backup
    # tar -xvf YYYY-MM-DD-centreon-engine.tar.gz
    # cd backup/etc
    # cp  nrpe.cfg /etc/centreon-engine/
    # cp  nsca.cfg /etc/centreon-engine/

.. note::
    Cette manipulation est à utiliser si et seulement si vous utilisez les agents NRPE ou NSCA. Si vous utiliser NSCA le fichier de configuration à copier n'est pas nrpe.cfg mais nsca.cfg.

Génération de la configuration du central
=========================================

Une fois toutes les étapes (nécessaires) effectuées, il faudra générer la configuration de chaque collecteur.

Reconstruction des graphiques
=============================

Une fois que vous avez restauré votre plate-forme de supervision et que tout est en ordre, il faudra reconstruire les fichiers RRD afin de retrouver tous vos "anciens" graphiques de performance.

Pour reconstruire les graphiques de performance, il faudra vous rendre dans le menu **Administration > Options > Centstorage > Manage**.
Sur cette page, il faudra sélectionner tous les services et cliquer sur **Rebuild RRD Database**.

**Le serveur central est maintenant restauré.**
