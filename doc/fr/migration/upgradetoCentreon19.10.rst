.. _migrate_to_1810:

=============================================
Migration depuis une plate-forme Centreon 3.4
=============================================

*********
Prérequis
*********

Cette procédure ne s'applique que pour une plate-forme **Centreon 3.4**,
installé sur une distribution GNU/Linux 64 bits autre que CentOS ou Red Hat 7 
et disposant des prérequis suivants :

+-----------------+---------+
| Composants      | Version |
+=================+=========+
| Centreon Web    | 2.8.x   |
+-----------------+---------+
| Centreon Broker | 3.0.x   |
+-----------------+---------+
| Centreon Engine | 1.8.x   |
+-----------------+---------+

.. note::
    Si votre plate-forme a été installé à partir de l'ISO Centreon ou des
    dépôts Centreon 3.4 sur CentOS ou Red Hat en version 7, référez-vous à
    la documentation de :ref:`mise à jour<upgrade>`.

*********
Migration
*********

.. warning::
    En cas de migration d'une plate-forme disposant du système de redondance
    Centreon, il est nécessaire de contacter votre `support Centreon 
    <https://centreon.force.com>`_.

.. warning::
    En cas de migration d'une plate-forme disposant du module **Centreon Poller
    Display 1.6.x**, référez-vous à la :ref:`procédure de migration suivante
    <migratefrompollerdisplay>`.

Installation du nouveau serveur
===============================

Réalisez les actions suivantes :

#. Installez un nouveau serveur Centreon à partir de :ref:`l'ISO<installisoel7>` ou :ref:`des paquets<install_from_packages>` jusqu'à terminer le processus d'installation en vous connectant à l'interface web.
#. Réalisez les mises à jour logicielle et système : ::

    # yum update

.. note::
    Il est préférable de saisir le même mot de passe pour l'utilisateur
    'centreon' lors du processus d'installation web.
 
Synchronisation des données
===========================

Connectez-vous à votre ancien serveur Centreon et synchronisez les répertoires
suivants : ::

    # rsync -avz /etc/centreon root@IP_New_Centreon:/etc
    # rsync -avz /etc/centreon-broker root@IP_New_Centreon:/etc
    # rsync -avz /var/log/centreon-engine/archives/ root@IP_New_Centreon:/var/log/centreon-engine
    # rsync -avz --exclude centcore/ --exclude log/ /var/lib/centreon root@IP_New_Centreon:/var/lib
    # rsync -avz /var/spool/centreon/.ssh root@IP_New_Centreon:/var/spool/centreon

.. note::
    Remplacez **IP_New_Centreon** par l'adresse IP de votre nouveau serveur Centreon.

.. warning::
    En cas de migration d'une plate-forme CES 3.4.x, Centreon-web 2.8.x sous CentOS 6 avec MariaDB 5.X, ne pas
    synchroniser les dossiers /var/lib/mysql avec RSYNC vers la database Centreon 19.10 en MariaDB 10.2.
    
    #. Faire un dump des bases de données sources : ::
    
        # mysqldump -u root -p centreon > /tmp/export/centreon.sql
        # mysqldump -u root -p centreon_storage > /tmp/export/centreon_storage.sql
      
    #. Arreter le serveur MariaDB source : ::
    
        # service mysql stop
    
    #. Transférer les exports vers le nouveau serveur de base de données Centreon 19.10 : ::
    
        # rsync -avz /tmp/centreon.sql root@IP_New_Centreon:/tmp/
        # rsync -avz /tmp/centreon_storage.sql root@IP_New_Centreon:/tmp/
        
    #. Sur le serveur de base de données Centreon 19.10 supprimer les bases de données vierges et les recréer : ::
    
        # mysql -u root -p
        # drop database centreon;
        # drop database centreon_storage;
        # create database centreon;
        # create database centreon_storage;
        
    #. Importer les dumps : ::
    
        # mysql -u root centreon -p </tmp/centreon.sql
        # mysql -u root centreon_storage -p </tmp/centreon_storage.sql
        
    #. Executer l'upgrade des tables : ::
    
        # mysql_upgrade
        
    #. Reprendre la procédure de migration
    
Si le SGBD MySQL/MariaDB est installé sur même serveur que le serveur Centreon,
exécutez les commandes suivantes :

#. Arrêtez le processus **mysqld** sur les deux serveurs (ancien et nouveau) : ::

    # service mysqld stop

#. Sur le nouveau serveur, supprimer le contenu du répertoire /var/lib/mysql/ : ::

    # rm -Rf /var/lib/mysql/*

#. Sur l'ancien serveur, lancer la synchronisation des données : ::

    # rsync -avz /var/lib/mysql/ root@IP_New_Centreon:/var/lib/mysql/

#. En cas de migration d'un SGBD MySQL/MariaDB 5.x vers 10.x, il est nécessaire de lancer la commande suivante sur le nouveau serveur : ::

    # mysql_upgrade

#. Redémarrage du processus mysqld sur nouveau serveur : ::

    # systemctl start mysqld

Synchronisation des plugins
===========================

La synchronisation des sondes de supervision (plugins) est plus délicate et
dépend de votre installation. Les principaux répertoires à synchroniser sont :

#. /usr/lib/nagios/plugins/
#. /usr/lib/centreon/plugins/

.. note::
    Il est important d'installer les dépendances nécessaires au fonctionnement
    des sondes de supervision.

.. note::
    Si vous avez des pollers en centreon engine 1.8.1 que vous comptez migrer plus tard en centreon engine 19.10, attention au dossier des plugins nagios. La ressource $USER1$ ce Centreon 19.10 pointe sur /usr/lib64/nagios/plugins
    
    A éxécuter sur vos collecteurs en centreon-engine 1.8.1 : ::
    
        # mv /usr/lib64/nagios/plugins/* /usr/lib/nagios/plugins/
        # rmdir /usr/lib64/nagios/plugins/
        # ln -s -t /usr/lib64/nagios/ /usr/lib/nagios/plugins/
    
    De cette façon un lien symbolique est créé : ::
    
        # ls -alt /usr/lib64/nagios/
        lrwxrwxrwx   1 root root      24  1 nov.  17:59 plugins -> /usr/lib/nagios/plugins/
        -rwxr-xr-x   1 root root 1711288  6 avril  2018 cbmod.so
    
    Et vous permet de pousser les configuration de collecteur depuis Centreon 19.10 indifféremment vers un collecteur en 19.10 ou 1.8.1

Mise à jour de la suite Centreon
================================

Forcez la mise à jour du nouveau serveur en déplacant le contenu du répertoire
**/usr/share/centreon/installDir/install-19.10.0-YYYYMMDD_HHMMSS** dans le
repértoire **/usr/share/centreon/www/install** : ::

    # cd /usr/share/centreon/installDir/
    # mv install-19.10.0-YYYYMMDD_HHMMSS/ ../www/install/

.. note::
    Si vous utilisez la meme adresse IP ou le même nom DNS entre l'ancien serveur web Centreon et le nouveau, videz completement le cache de votre navigateur pour éviter des problemes de scripts JS.

Se connecter à l'url http://[ADRESSE_IP_DE_VOTRE_SERVEUR]/centreon et suivre
les étapes de mise à jour.

.. note::
    Si vous avez modifié le mot de passe de l'utilisateur 'centreon' lors de
    l'installation de votre nouveau serveur Centreon pour accéder aux bases de
    données, il sera nécessaire de réaliser les actions suivantes sur le nouveau
    serveur Centreon :
    
    #. Modifiez le fichier /etc/centreon/centreon.conf.php
    #. Modifiez le fichier /etc/centreon/conf.pm
    #. Éditer la configuration du Centreon Broker central, via l'interface web
       Centreon et modifier le mot de passe pour les deux output broker **Perfdata
       generator** et **Broker SQL database**.

Si l'adresse IP de votre serveur Centreon a changé, éditez la configuration
de l'ensemble des modules broker de vos collecteurs et modifiez l'adresse IP
de connexion au serveur Centreon central (output IPv4).

Puis :ref:`générez <deployconfiguration>` la configuration de l'ensemble de la
plate-forme et exportez là.

Mise à jour des modules
=======================

Référez-vous à la documentation des modules installés afin de connaître
leur compatibilité avec Centreon 19.10, et pour mettre à jour ces derniers.
