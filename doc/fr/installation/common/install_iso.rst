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
