===============================
Mise à jour depuis Centreon 3.4
===============================

Ce chapitre décrit la procédure de mise à jour de votre plate-forme vers
Centreon 19.10.

.. warning::
    A la fin de cette procédure, les utilisateurs de Centreon EMS devront demander de
    nouvelles licenses au `Support Centreon <https://centreon.force.com>`_.

.. warning::
    Cette procédure ne s'applique que pour une plate-forme Centreon installée à
    partir des dépôts Centreon 3.4 sur des distributions **Red Hat / CentOS en
    version 7**.

    Si cela n'est pas le cas, se référer à la procédure de :ref:`migration <upgradecentreon1904>`.

Pour mettre à jour votre serveur Centreon Map, référez-vous à la `documentation associée
<https://documentation.centreon.com/docs/centreon-map-4/en/latest/upgrade/index.html>`_.

Pour mettre à jour votre serveur Centreon MBI, référez-vous à la `documentation associée
<https://documentation-fr.centreon.com/docs/centreon-bi-2/en/latest/update/index.html>`_.

**********
Sauvegarde
**********

Avant toute chose, il est préférable de s’assurer de l’état et de la consistance
des sauvegardes de l’ensemble des serveurs centraux de votre plate-forme :

* Serveur(s) Centreon central(aux) ;
* Serveur(s) de gestion de base de données ;

***************************************
Mise à jour du serveur Centreon Central
***************************************

Mise à jour du système d’exploitation
=====================================

Pensez à mettre à jour votre système d'exploitation via la commande : ::

    # yum update

.. note::
    Acceptez toutes les clés GPG proposées et pensez a redémarrer votre serveur si une mise à jour du noyau est
    proposée.

Mise à jour des dépôts
======================

Afin d’installer les logiciels Centreon, le dépôt Software collections de Red
Hat doit être activé.

.. note::
    Le dépôt *Software collections* est nécessaire pour l’installation de PHP 7
    et les librairies associées.

Exécutez la commande suivante : ::

    # yum install centos-release-scl

Il est maintenant nécessaire de mettre à jour le dépôt Centreon.

Exécutez la commande suivante : ::

    # yum install -y http://yum.centreon.com/standard/19.10/el7/stable/noarch/RPMS/centreon-release-19.10-1.el7.centos.noarch.rpm

Mise à jour de la solution Centreon
===================================

Mettez à jour le cache de yum : ::

    # yum clean all

Mettez à jour l'ensemble des composants : ::

    # yum update centreon\*

.. note::
    Acceptez les nouvelles clés GPG des dépôts si nécessaire.

Actions complémentaires
=======================

Mise à jour de la version de PHP
--------------------------------

Centreon 19.10 utilise un nouveau paquet PHP

Le fuseau horaire par défaut de PHP 7 doit être configurée. Executer la commande
suivante : ::

    # echo "date.timezone = Europe/Paris" > /etc/opt/rh/rh-php72/php.d/php-timezone.ini

.. note::
    Changez **Europe/Paris** par votre fuseau horaire.

Réalisez les actions suivantes : ::

    # systemctl start rh-php72-php-fpm
    # systemctl enable rh-php72-php-fpm

Mise à jour du serveur Web Apache
---------------------------------

Centreon 19.10 utilise un nouveau paquet pour le serveur Web Apache.

.. note::
    Si vous avez modifié la configuration, reportez celle-ci dans le répertoire
	    **/opt/rh/httpd24/root/etc/httpd/conf.d/**.
		    
    Si SSL était activé, installer le paquet suivant : ::
				    
	# yum install httpd24-mod_ssl

Puis réalisez les actions suivantes : ::

    # systemctl disable httpd
    # systemctl stop httpd
    # systemctl enable httpd24-httpd
    # systemctl start httpd24-httpd
    # systemctl enable centreon
    # systemctl restart centreon

Finalisation de la mise à jour
==============================

Connectez-vous à l'interface web Centreon pour démarrer le processus de mise à
jour :

Cliquez sur **Next** :

.. image:: /_static/images/upgrade/web_update_1.png
    :align: center

Cliquez sur **Next** :

.. image:: /_static/images/upgrade/web_update_2.png
    :align: center

La note de version présente les principaux changements, cliquez sur **Next** :

.. image:: /_static/images/upgrade/web_update_3.png
    :align: center

Le processus réalise les différentes mises à jour, cliquez sur **Next** :

.. image:: /_static/images/upgrade/web_update_4.png
    :align: center

Votre serveur Centreon est maintenant à jour, cliquez sur **Finish** pour accéder
à la page de connexion :

.. image:: /_static/images/upgrade/web_update_5.png
    :align: center

Pour mettre à jour votre module Centreon BAM, référez-vous à la `documentation associée
<https://documentation-fr.centreon.com/docs/centreon-bam/en/latest/update/index.html>`_.

***************************
Mise à jour des collecteurs
***************************

Installation des dépôts
=======================

Exécutez la commande suivante : ::

    # yum install -y http://yum.centreon.com/standard/19.10/el7/stable/noarch/RPMS/centreon-release-19.10-1.el7.centos.noarch.rpm

Mise à jour de la solution Centreon
===================================

Mettez à jour l'ensemble des composants : ::

    # yum update centreon\*

.. note::
    Acceptez les nouvelles clés GPG des dépôts si nécessaire.

Actions complémentaires
=======================

Redémarrez le service centengine en exécutant la commande suivante : ::

    # systemctl restart centengine

***************************************
Mise à jour des serveurs Poller Display
***************************************

Référez-vous à la documentation de :ref:`migration d'un serveur Poller Display
vers Remote Server 19.10 <migratefrompollerdisplay>`.
