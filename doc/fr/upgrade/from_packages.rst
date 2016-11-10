.. _upgrade_from_packages:

===============
Mise à jour RPM
===============

La version 3.4 de Centreon Entreprise Server (CES) est l'ensemble Centreon Web 2.8, Centreon Engine 1.6, Centreon Broker 3.0.
Deux versions de CES 3.4 sont disponibles, en fonction du système d'exploitation d'origine : CentOS 6 ou CentOS 7.

.. warning::
   Cette release n'est pas encore intégralement compatible avec la
   totalité des logiciels commerciaux de Centreon tels que Centreon MBI,
   Centreon BAM ou Centreon Map. Si vous utilisez l'un de ces produits
   nous vous recommandons fortement de **NE PAS** mettre à jour Centreon
   Web tant que de nouvelles versions des produits précédents, indiquant
   clairement leur compatibilité avec Centreon Web 2.8, ne sont pas
   disponibles.

*********
Prérequis
*********

Les prérequis nécessaires au fonctionnement de Centreon Web 2.8 ont
évolué par rapport aux précédentes versions. Il est important de suivre
les recommandations suivantes pour avoir une plate-forme fonctionnelle.

**Centreon vous recommande d'utiliser MariaDB** à la place de MySQL.

+----------+-----------+
| Software | Version   |
+==========+===========+
| MariaDB  | >= 5.5.35 |
+----------+-----------+
| MySQL    | >= 5.1.73 |
+----------+-----------+

***********************
Dépendances logicielles
***********************

The following table describes the dependent software:

+----------+-----------+
| Software | Version   |
+==========+===========+
| Apache   | 2.2       |
+----------+-----------+
| GnuTLS   | >= 2.0    |
+----------+-----------+
| Net-SNMP | 5.5       |
+----------+-----------+
| openssl  | >= 1.0.1e |
+----------+-----------+
| PHP      | >= 5.3.0  |
+----------+-----------+
| Qt       | >= 4.7.4  |
+----------+-----------+
| RRDtools | 1.4.7     |
+----------+-----------+
| zlib     | 1.2.3     |
+----------+-----------+

***********
Mise à jour
***********

1. Arrêt des instances de collecte
==================================

.. warning::
   Avant de commencer la mise à jour, assurez vous de ne pas avoir de fichier de rétention
   actif pour Centreon-Broker.

Stoppez Centreon Broker et Centreon Engine sur l’ensemble des pollers

   ::

   # /etc/init.d/centengine stop
   # /etc/init.d/cbd stop

2. Mise à jour l’ensemble des paquets
=====================================

Pour installer la nouvelle version de Centreon depuis une CES 3.4, lancez la commande suivante :

   ::

   # yum update centreon

.. warning::
   Si vous rencontrez des problèmes de dépendances avec le package centreon-engine-webservices, merci de le supprimer car il est maintenant obsolète. Lancez la commande suivante :
   # yum remove centreon-engine-webservices

Si vous venez de la version 2.7.0-RC2 de Centreon, pour contourner le problème de nom des RPM qui vous provoque des problème de dépendances RPM, tappez la commande suivante :

  ::

  # yum downgrade centreon-2.7.0 centreon-plugins-2.7.0 centreon-base-config-centreon-engine-2.7.0 centreon-plugin-meta-2.7.0 centreon-common-2.7.0 centreon-web-2.7.0 centreon-trap-2.7.0 centreon-perl-libs-2.7.0


3. Redémarrez le serveur Apache
===============================

Suite à l’installation de PHP-intl, il est nécessaire de redémarrer le serveur apache afin de prendre en compte la nouvelle extension.

   ::

   # /etc/init.d/httpd restart

4. Réalisez la mise à jour Web de Centreon 2.8
==============================================

Suivez le wizard de mise à jour Web afin de terminer les mises à jours pour les modifications au niveau de la base SQL soient appliquées. Durant cette phase, un nouveau fichier de configuration va être également créé.

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

5. Exportez la configuration vers l’ensemble des pollers
========================================================

Pour terminer l’installation, il est nécessaire de générer une première fois les configurations de Centreon Engine et Centreon Broker. Pour cela, allez dans Configuration > Poller et cliquer sur l’icone de génération.

6. Redémarrez les moteurs Centreon Engine et Centreon Broker sur l’ensemble des pollers
=======================================================================================

Vous pouvez maintenant redémarrer les instances de collecte afin de remettre le service en place. Pour ceci, lancez les commandes suivantes :

  ::

   # /etc/init.d/centengine start
   # /etc/init.d/cbd start

*********************************************
Les risques identifiés lors de la mise à jour
*********************************************

Afin de vous aider à éviter le plus possible des problèmes éventuels liés à la mise à jour de votre plate-forme en version 2.8 de Centreon couplée à la version 1.6 de Engine et 3.0 de Broker, nous souhaitons vous partager la liste des risques potentiels suite à cette action. Cela ne veut pas dire que vous rencontrerez ces problèmes lors de la mise à jour. Cependant, ce sont des points que nous vous incitons à surveiller après la mise à jour. Cette liste de risque nous aidera je l’espère valider que tout se passe bien de votre côté.

Les risques sont les suivants :
===============================

* Problèmes de dépendances avec Centreon Engine et Centreon Broker : les deux dernières versions (Centreon Broker 3.0 et Centreon Engine 1.6) sont des prérequis au fonctionnement de Centreon Web 2.8
* Problèmes de mise à jour des schémas de base de données
* Passage de toutes les tables MySQL en InnoDB (sauf logs et data_bin qui ne seront pas migrées automatiquement)
* Changement au niveau de la table hostgroup et servicegroup dans la base storage
* Les temporaries et les failovers sont définis par défaut sur Centreon Broker : Il est donc possible que cela entre en conflit avec la configuration existant avant la mise à jour. Bien vérifier après la mise à jour qu’il ne reste pas des anciens fichiers et que cela n’a pas bloqué le broker générant ainsi des pertes de données
* Problème de cache navigateur : le cache du navigateur doit être vidée à la fin de la mise à jour et web et également après la première connexion.
* Problème avec des dépendances php (intl) : un nouveau prérequis PHP a été ajouté. Suite à la mise à jour RPM, il est nécessaire de redémarrer Apache pour que celui-ci soit chargé.
* Problème de compatibilité avec des modules installés : le style de la 2.7.0 change complètement des versions précédentes. Les modules Centreon doivent donc être adaptés en conséquence. Ne migrez pas si vos modules ne sont pas compatibles.
* Génération de conf qui ne se génère pas normalement : le moteur de génération de la configuration a été réécrit. Il y a donc un risque d’erreurs dans les configurations exportées.
* Bascule direct de NDOutils vers Centreon Broker au passage de la version 2.7 : Centreon 2.7.0 n’est plus compatible avec Nagios et NDOutils. Des problèmes surviendront en cas de tentative de mise à jour d’une machine fonctionnant avec Nagios/NDOutils vers la version 2.7.0.
