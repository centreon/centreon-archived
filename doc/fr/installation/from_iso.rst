.. _installisoel7:

============================
A partir de Centreon ISO el7
============================

.. note::
   L'installation à partir de l'image ISO el6 est décrite :ref:`ici<installisoel6>`

************
Installation
************

Etape 1 : Démarrage
====================

Afin d'installer Centreon, démarrez votre serveur sur l'image ISO de Centreon 
en version el7.
Démarrez avec l'option **Install CentOS 7** :

.. image :: /images/guide_utilisateur/01_bootmenu.png
   :align: center
   :scale: 65%

Etape 2 : Choix de la langue
============================

Choisissez la langue du processus d'installation puis cliquez sur **Done** :

.. image :: /images/guide_utilisateur/02_select_install_lang.png
   :align: center
   :scale: 65%

Etape 3 : Sélection des composants
==================================

Cliquez sur le menu **Installation Type** : 

.. image :: /images/guide_utilisateur/03_menu_type_install.png
   :align: center
   :scale: 65%

Il est possible de choisir différentes options :

.. image :: /images/guide_utilisateur/04_form_type_install.png
   :align: center
   :scale: 65%

|

 * **Central with database** : Installe Centreon (interface web + base de données) ainsi que l'ordonnanceur et le broker
 * **Central without database** : Installe Centreon (interface web uniquement) ainsi que l'ordonnanceur et le broker
 * **Poller** : Installe le serveur satellite (ordonnanceur et broker uniquement)
 * **Database only** : Installe le serveur de base de données (utilisé en complément avec l'option **Central server without database**)

Etape 4 : Configuration système
===============================

Partitionnement des disques
---------------------------

Cliquez sur le menu **Installation Destination** :

.. image :: /images/guide_utilisateur/05_menu_filesystem.png
   :align: center
   :scale: 65%

Sélectionnez le disque dur et l'option **I will configure partitioning** puis cliquez sur "**Done** :

.. image :: /images/guide_utilisateur/06_select_disk.png
   :align: center
   :scale: 65%

A l'aide du bouton **+** créez votre partitionnement suivant les :ref:`prérequis de la documentation<diskspace>` puis cliquez sur **Done** :

.. image :: /images/guide_utilisateur/07_partitioning_filesystem.png
   :align: center
   :scale: 65%

Une fenêtre de confirmation apparaît, cliquez sur **Accept Changes** pour valider le partitionnement :

.. image :: /images/guide_utilisateur/08_apply_changes.png
   :align: center
   :scale: 65%

Configuration réseau
--------------------

Cliquez sur le menu **Network & Hostname** :

.. image :: /images/guide_utilisateur/09_menu_network.png
   :align: center
   :scale: 65%

Activez toutes les cartes réseaux, saisissez le nom de votre serveur puis cliquez sur **Done** :

.. image :: /images/guide_utilisateur/10_network_hostname.png
   :align: center
   :scale: 65%

Configuration du fuseau horaire
-------------------------------

Cliquez sur le menu **Date & Time** :

.. image :: /images/guide_utilisateur/11_menu_timezone.png
   :align: center
   :scale: 65%

Sélectionnez votre fuseau horaire et cliquez sur le bouton de configuration :

.. image :: /images/guide_utilisateur/12_select_timzeone.png
   :align: center
   :scale: 65%

Activez ou ajouter des serveurs NTP, cliquez sur **OK** puis **Done** :

.. image :: /images/guide_utilisateur/13_enable_ntp.png
   :align: center
   :scale: 65%

Démarrage de l'installation
---------------------------

Une fois toutes les options configurées, cliquez sur **Begin Installation** :

.. image :: /images/guide_utilisateur/14_begin_install.png
   :align: center
   :scale: 65%

Cliquez sur **Root Password** :

.. image :: /images/guide_utilisateur/15_menu_root_password.png
   :align: center
   :scale: 65%

Saisissez et confirmez le mot de passe de l'utilisateur **root**. Cliquez sur **Done** :

.. image :: /images/guide_utilisateur/16_define_root_password.png
   :align: center
   :scale: 65%

Patientez pendant le processus d'installation :

.. image :: /images/guide_utilisateur/17_wait_install.png
   :align: center
   :scale: 65%

Lorsque l'installation est terminée, cliquez sur **Reboot**.

.. image :: /images/guide_utilisateur/18_reboot_server.png
   :align: center
   :scale: 65%


Mise à jour du système d'exploitation
-------------------------------------

Connectez-vous via un terminal et exécutez la commande :
  ::

  # yum update

.. image :: /images/guide_utilisateur/19_update_system.png
   :align: center
   :scale: 65%

Acceptez toutes les clés GPG proposées :

.. image :: /images/guide_utilisateur/20_accept_gpg_key.png
   :align: center
   :scale: 65%

Redémarrez votre système avec la commande :
  ::

  # reboot

*************
Configuration
*************

.. _installation_web_ces:

Via l'interface web
===================

Connectez-vous à l'interface web via http://[ADRESSE_IP_DE_VOTRE_SERVEUR]/centreon.
L'assistant de fin d'installation de Centreon s'affiche, cliquez sur **Next**.

.. image :: /images/guide_utilisateur/acentreonwelcome.png
   :align: center
   :scale: 65%

L'assistant de fin d'installation de Centreon contrôle la disponibilité des modules, cliquez sur **Next**.

.. image :: /images/guide_utilisateur/acentreoncheckmodules.png
   :align: center

Cliquez sur **Next**.

.. image :: /images/guide_utilisateur/amonitoringengine2.png
   :align: center
   :scale: 65%

Cliquez sur **Next**.

.. image :: /images/guide_utilisateur/abrokerinfo2.png
   :align: center
   :scale: 65%

Définissez les informations concernant l'utilisateur admin, cliquez sur **Next**.

.. image :: /images/guide_utilisateur/aadmininfo.png
   :align: center
   :scale: 65%

Par défaut, le serveur 'localhost' est défini et le mot de passe root est vide. Si vous utilisez un serveur de base de données déporté, il convient de modifier ces deux informations.
Dans notre cas, nous avons uniquement besoin de définir un mot de passe pour l'utilisateur accédant aux bases de données Centreon, à savoir 'centreon', cliquez sur **Next**.

.. image :: /images/guide_utilisateur/adbinfo.png
   :align: center
   :scale: 65%

Si le message d'erreur suivant apparaît : **Add innodb_file_per_table=1 in my.cnf file under the [mysqld] section and restart MySQL Server**.
Effectuez l'opération ci-dessous :

1. Connectez-vous avec l'utilisateur 'root' sur votre serveur
2. Editez le fichier suivant

::

   /etc/my.cnf

3. Ajoutez la ligne suivante au fichier

::

   [mysqld]
   innodb_file_per_table=1

4. Redémarrez le service mysql

::

   # service mysql restart

5. Cliquez sur **Refresh**

L'assistant de fin d'installation configure les bases de données, cliquez sur **Next**.

.. image :: /images/guide_utilisateur/adbconf.png
   :align: center
   :scale: 65%

L’installation est terminée, cliquez sur **Finish**.

À cette étape une publicité permet de connaitre les dernières nouveautés
de Centreon. Si votre plate-forme est connectée à Internet vous disposez
des dernières informations, sinon l’information présente dans cette version
sera proposée.

.. image :: /images/guide_utilisateur/aendinstall.png
   :align: center
   :scale: 65%

Vous pouvez maintenant vous connecter.

.. image :: /images/guide_utilisateur/aconnection.png
   :align: center
   :scale: 65%

Configuration de base
=====================

Dans un premier temps, il est nécessaire de passer l'interface en version française. Pour cela :

1. Connectez-vous avec l'utilisateur 'root' sur votre serveur
2. Installez le paquet de traduction en langue française avec la commande suivante

::

  # yum -y install centreon-lang-fr_FR

3. Rendez-vous dans le menu **Administration** ==> **Options**
4. Dans le menu de gauche cliquez sur **My Account**
5. Dans le champ **Language**, remplacez **en_US** par **fr_FR.UTF-8**
6. Cliquez sur **Save**

.. image :: /images/guide_utilisateur/alanguage.png
   :align: center

Démarrer la supervision
=======================

Pour démarrer l'ordonnanceur de supervision :

1. Sur l'interface web, rendez-vous dans le menu **Configuration** ==> **Moteur de supervision**
2. Laissez les options par défaut, et cliquez sur **Exporter**
3. Décochez **Générer les fichiers de configuration** et **Lancer le débogage du moteur de supervision (-v)**
4. Cochez **Déplacer les fichiers générés** ainsi que **Redémarrer l'ordonnanceur**
5. Cliquez à nouveau sur **Exporter**
6. Connectez-vous avec l'utilisateur 'root' sur votre serveur
7. Démarrez le composant Centreon Broker

::

   # service cbd start

8. Démarrez Centreon Engine

::

   # service centengine start

9. Démarrez centcore

::

    # service centcore start

La supervision est maintenant opérationnelle.

Découverte de l'interface web
=============================

L'interface web de Centreon est composée de plusieurs menus, chaque menu a une fonction bien précise :

.. image :: /images/guide_utilisateur/amenu.png
   :align: center

|

* Le menu **Accueil** permet d'accéder au premier écran d'accueil après s'être connecté. Il résume l'état général de la supervision.
* Le menu **Supervision** regroupe l'état de tous les éléments supervisés en temps réel et en différé au travers de la visualisation des logs
* Le menu **Vues** permet de visualiser et de configurer les graphiques de performances pour chaque élément du système d'informations
* Le menu **Rapports** permet de visualiser de manière intuitive (via des diagrammes) l'évolution de la supervision sur une période donnée
* Le menu **Configuration** permet de configurer l'ensemble des éléments supervisés ainsi que l'infrastructure de supervision
* Le menu **Administration** permet de configurer l'interface web Centreon ainsi que de visualiser l'état général des serveurs

***************************************
Configurez votre supervision facilement
***************************************

En lui-même Centreon est un excellent outil de supervision et peut être
configuré pour correspondre exactement à vos besoins. Cependant vous
trouverez peut-être utile d'utiliser Centreon IMP pour vous aider à
configurer rapidement votre supervision. Centreon IMP vous fournit des
Plugin Packs qui sont des paquets contenant des modèles de configuration
qui réduisent drastiquement le temps nécessaire pour superviser la
plupart des services de votre réseau.

Centreon IMP nécessite les composants techniques Centreon License
Manager et Centreon Plugin Pack Manager pour fonctionner.

Installation système
====================

En utilisant Centreon ISO, l'installation des paquets est très simple. Vous
noterez que Centreon Plugin Pack Manager installe également Centreon
License Manager en tant que dépendance.

::

   # yum install centreon-pp-manager


Installation web
================

Une fois les paquets installés, il est nécessaire d'activer les modules
dans Centreon. Rendez-vous à la page Administration -> Extensions -> Modules.

.. image:: /_static/images/installation/ppm_1.png
   :align: center

Installez tout d'abord Centreon License Manager.

.. image:: /_static/images/installation/ppm_2.png
   :align: center

Puis installez Centreon Plugin Pack Manager.

.. image:: /_static/images/installation/ppm_3.png
   :align: center

Vous pouvez maintenant vous rendre à la page Administration -> Extensions
-> Plugin packs -> Setup. Vous y trouverez vos six premiers Plugin Packs
gratuits pour vous aider à démarrer. Cinq Plugin Packs supplémentaires
sont débloqués après vous être inscrit et plus de 150 sont disponibles
si vous souscrivez à l'offre IMP (plus d'informations sur
`notre site web <https://www.centreon.com>`_).

.. image:: /_static/images/installation/ppm_4.png
   :align: center

Vous pouvez continuer à configurer votre supervision en utilisant
Centreon IMP en suivant :ref:`ce guide <impconfiguration>`.
