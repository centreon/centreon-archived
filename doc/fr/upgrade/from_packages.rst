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
   disponibles. La seule exception à cette exclusion concerne EMS/EPP.

.. warning::
   Avant d'installer la mise à jour, vérifiez que votre plateforme n'utilise 
   pas le module centreon knowledgebase (aussi appelé Centreon KB). Si c'est le 
   cas, veuillez le désinstaller. Le module Centreon KB est maintenant intégré 
   à la version 2.8.

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

*********
Dépôt CES
*********

Si vous êtes déjà un utilisateur de CES, vous devez mettre à jour votre
fichier .repo pour utiliser les logiciels faisant partie de CES 3.4
(essentiellement Centreon Web 2.8 et les composants associés). Entrez
les commandes suivantes en fonction de votre système d'exploitation.

CentOS 6
========

::

   $ rm -f /etc/yum.repos.d/ces-standard.repo
   $ wget http://yum.centreon.com/standard/3.4/el6/stable/centreon-stable.repo -O /etc/yum.repos.d/centreon-stable.repo


CentOS 7
========

::

   $ rm -f /etc/yum.repos.d/ces-standard.repo
   $ wget http://yum.centreon.com/standard/3.4/el7/stable/centreon-stable.repo -O /etc/yum.repos.d/centreon-stable.repo


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

   # service centengine stop
   # service cbd stop

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

   # service httpd restart

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

   # service centengine start
   # service cbd start


**********************
Mise à jour de EMS/EPP
**********************

.. note::
   Pas utilisateur de EMS/EPP ? Vous trouverez cependant les Plugins
   Packs Centreon extrêmement utiles pour vous aider à configurer votre
   supervision en quelques minutes. Vous trouverez les informations
   d'installation dans notre :ref:`documentation en ligne <installation_ppm>`.


Si vous utilisez des modules Centreon, vous devrez les mettre à jour
également pour qu'ils continuent de fonctionner de manière
satisfaisante. Cela est particulièrement vrai pour les utilisateurs
de EMS/EPP.

Mise à jour du dépôt
====================

Comme pour CES, le fichier .repo doit être mis à jour pour utiliser la
version 3.4. N'hésitez pas à contacter le support Centreon si vous ne
savez pas comment réaliser cette opération.

Mise à jour des paquets
=======================

Entrez la commande suivante sur le serveur central pour mettre à jour
Centreon Plugin Pack Manager, les Plugin Packs et leurs plugins
associés.

::

   # yum update centreon-pp-manager ces-plugins-* ces-pack-*


Vous devrez également lancer la commande suivante sur chaque collecteur
utilisant les Plugin Packs.

::

   # yum update ces-plugins-*


Mise à jour web
===============

Vous devez maintenant lancer la mise à jour via l'interface web. Pour
cela rendez-vous à la page Administration -> Extensions -> Modules.

.. image:: /_static/images/upgrade/ppm_1.png
   :align: center

Installez tout d'abord Centreon License Manager (dépendance de PPM)
puis Centreon Plugin Pack Manager.

.. image:: /_static/images/upgrade/ppm_2.png
   :align: center

Bien, votre module fonctionne de nouveau.

*********************************************
Les risques identifiés lors de la mise à jour
*********************************************

Afin de vous aider à éviter le plus possible des problèmes éventuels liés à la mise à jour de votre plate-forme en version 2.8 de Centreon couplée à la version 1.6 de Engine et 3.0 de Broker, nous souhaitons vous partager la liste des risques potentiels suite à cette action. Cela ne veut pas dire que vous rencontrerez ces problèmes lors de la mise à jour. Cependant, ce sont des points que nous vous incitons à surveiller après la mise à jour. Cette liste de risque nous aidera je l’espère valider que tout se passe bien de votre côté.

Les risques sont les suivants :
===============================

* Incompatibilité avec la plupart des produits commerciaux : Centreon MBI, Centreon BAM et Centreon Map ne sont pas encore compatible avec Centreon Web 2.8.
* Problèmes de dépendances avec Centreon Engine et Centreon Broker : les deux dernières versions (Centreon Broker 3.0 et Centreon Engine 1.6) sont des prérequis au fonctionnement de Centreon Web 2.8
* Problèmes de mise à jour des schémas de base de données
* Les nouveau graphiques de performances ont des échelles affichant trop de détails
* Des erreurs PHP de type warning apparaissent dans le journal d'évènement d'Apache
* Le zoom affecte tous les graphiques
* Le retour arrière du zoom des graphiques est absent
* L'export CSV ne fonctionne pas pour les eventlogs
