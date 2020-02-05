.. _migratefrompollerdisplay:

===============================================
Migration d'une plate-forme avec Poller Display
===============================================

***************************************
Mise à jour du serveur Centreon Central
***************************************

Si le module centreon-poller-display-central-1.6.x est installé :

1. Rendez-vous dans le menu **Administration > Extensions > Modules** et désinstallez le module **centreon-poller-display-central**
2. Supprimez le paquet associé : ::

    # yum remove centreon-poller-display-central

Puis suivez la :ref:`procédure de mise à jour <upgrade_from_packages>` si vous
avez une plate-forme **CentOS v7** avec paquets Centreon, sinon la 
:ref:`procédure de migration <migrate_to_1810>` d'un serveur Centreon central
vers 18.10.

.. note::
    Si vous possédez des modules Centreon EMS, il est nécessaire de mettre à
    jour ces dépôts. Contactez votre support Centreon pour obtenir ces derniers.
    Demandez également les nouvelles licences associées.

******************************************************
Migration d'un server Poller Display vers Remote Serve
******************************************************

1. Rendez-vous dans le menu **Administration > Extensions > Modules** et supprimez
   le module **Centreon Poller Display**
2. Si vous avez installé le module à partir du paquet RPM, supprimez le paquet
   en exécutant la commande suivante : ::

    # yum remove centreon-poller-display

.. note::
    Si vous possédez des modules Centreon EMS, il est nécessaire de mettre à
    jour ces dépôts. Contactez votre support Centreon pour obtenir ces derniers.

3. Si votre serveur est basé sur la distribution CentOS ou Red Hat en version 7,
   réalisez la mise à jour en suivant :ref:`la procédure de mise à jour <upgrade_from_packages>`
   ; sinon exécutez :ref:`la procédure de migration <migrate_to_1810>`.

4. Rendez-vous dans le menu **Administration > Extensions > Modules** et installez le module **centreon-license-manager**.

.. note::
    Si vous possédez des modules Centreon EMS, demandez les nouvelles licences de vos modules.

5. Exécutez la commande suivante : ::

    # /usr/share/centreon/bin/centreon -u admin -p centreon -a enableRemote -o CentreonRemoteServer -v @IP_CENTREON_CENTRAL

.. note::
    Remplacez **@IP_CENTREON_CENTRAL** par l'IP du serveur Centreon vu par le collecteur.

Cette commande va activer le mode **Remote Server** ::

    Starting Centreon Remote enable process:

      Limiting Menu Access...Success
      Limiting Actions...Done

      Notifying Master...Success

      Set 'remote' instance type...Done

      Centreon Remote enabling finished.

6. Ajout des droits pour l'utilsateur de base de données centreon d'utiliser la commande **LOAD DATA INFILE**::

    # mysql -h <database_server_address> -u root -p
    MariaDB [(none)]> GRANT FILE on *.* to 'centreon'@'<remote_server_ip>';

7. Echange de clé SSH :

Si vous n’avez pas de clé SSH privée sur le serveur central pour
l’utilisateur ‘centreon’ ::

    # su - centreon
    $ ssh-keygen -t rsa

Vous devez copier cette clé sur le Remote Server : ::

    # su - centreon
    $ ssh-copy-id -i .ssh/id_rsa.pub centreon@IP_POLLER

8. Sur le serveur **Centreon Central**, dans l'interface web Centreon, éditez
   tous les collecteurs rattachés au **Remote Server** et liez ceux-ci au
   nouveau Remote Server via la liste de sélection.

.. note::
    N'oubliez pas de :ref:`générer la configuration <deployconfiguration>` de
    votre **Remote Server**.

.. note::
    Un serveur Centreon Remote Server est un serveur dont l’administration est 
    autonome. Ainsi, la configuration de l’annuaire LDAP, celle des utilisateurs
    ainsi que celle des ACL sont propres à ce serveur et doivent être configurées
    via les menus **Configuration** (pour les utilisateurs) et **Administration**
    (pour l'annuaire LDAP et les ACL).
