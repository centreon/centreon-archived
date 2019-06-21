.. _upgrade_from_packages:

=================================
Mise à jour depuis Centreon 18.10
=================================

Ce chapitre décrit la procédure de mise à jour de votre plate-forme vers
Centreon 19.04.

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

Mise à jour des dépôts
======================

Il est nécessaire de mettre à jour le dépôt Centreon.

Exécutez la commande suivante : ::

    # yum install -y http://yum.centreon.com/standard/19.04/el7/stable/noarch/RPMS/centreon-release-19.04-1.el7.centos.noarch.rpm

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

Centreon 19.04 utilise un nouveau paquet pour le serveur Web Apache.

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

    # yum install -y http://yum.centreon.com/standard/19.04/el7/stable/noarch/RPMS/centreon-release-19.04-1.el7.centos.noarch.rpm

Mise à jour de la solution Centreon
===================================

Mettez à jour l'ensemble des composants : ::

    # yum update centreon*

.. note::
    Acceptez les nouvelles clés GPG des dépôts si nécessaire.

Actions complémentaires
=======================

Redémarrez le service centengine en exécutant la commande suivante : ::

    # systemctl restart centengine

******************************
Mise à jour des Remote Servers
******************************

Cette procédure est identique à la mise à jour d'un serveur Centreon Central.
