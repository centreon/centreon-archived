.. _install_from_packages:

====================
A partir des paquets
====================

Centreon fournit des RPM pour ses produits au travers de la solution Centreon Open Sources
(ex CES) disponible gratuitement sur notre dépôt.

Ces paquets ont été testés avec succès sur les environnements CentOS et
Red Hat en version 6.x et 7.x.

***********************
Étapes pré-installation
***********************

SELinux doit être désactivé. Pour cela vous devez modifier le fichier */etc/selinux/config* et remplacer "enforcing" par "disabled" comme dans l'exemple suivant :

::

    SELINUX=disabled

.. note::
    Après avoir sauvegardé le fichier, veuillez redémarrer votre système d'exploitation pour prendre en compte les changements.

***********************
Installation des dépôts
***********************

Afin d'installer les logiciels Centreon à partir des dépôts, vous devez au préalable installer
le fichier lié au dépôt. Exécutez la commande suivante à partir d'un utilisateur possédant les
droits suffisants.

Pour CentOS 6.

::

   $ wget http://yum.centreon.com/standard/3.4/el6/stable/noarch/RPMS/centreon-release-3.4-4.el6.noarch.rpm
   $ yum install --nogpgcheck centreon-release-3.4-4.el6.noarch.rpm


Pour CentOS 7.

::

   $ wget http://yum.centreon.com/standard/3.4/el7/stable/noarch/RPMS/centreon-release-3.4-4.el7.centos.noarch.rpm
   $ yum install --nogpgcheck centreon-release-3.4-4.el7.centos.noarch.rpm


Le dépôt est maintenant installé.


*******************************
Installation du serveur central
*******************************

Ce chapitre décrit l'installation d'un serveur central Centreon.

Exécutez la commande :

::

  $ yum install centreon-base-config-centreon-engine centreon

Installer MySQL sur le même serveur
-----------------------------------

Ce chapitre décrit l'installation de MySQL sur un serveur comprenant Centreon.

Exécutez la commande :

::

   $ yum install MariaDB-server
   $ service mysql restart

Fuseau horaire PHP
------------------

La timezone par défaut de PHP doit être configurée. Pour cela, aller dans le répertoire /etc/php.d et créer un fichier nommé php-timezone.ini contenant la ligne suivante :

::

    date.timezone = Europe/Paris

Après avoir sauvegardé le fichier, n'oubliez pas de redémarrer le service apache de votre serveur.

Pare-feu
--------

Paramétrer le pare-feu système ou désactiver ce dernier. Pour désactiver ce dernier exécuter les commandes suivantes :

* **iptables** (CentOS v6) ::

    # /etc/init.d/iptables save
    # /etc/init.d/iptables stop
    # chkconfig iptables off

* **firewalld** (CentOS v7) ::

    # systemctl stop firewalld
    # systemctl disable firewalld
    # systemctl status firewalld

Système de gestion de base de données
-------------------------------------

La base de données MySQL doit être disponible pour pouvoir continuer l'installation (localement ou non). Pour information nous recommandons MariaDB.

Pour les systèmes CentOS / RHEL en version 7, il est nécessaire de modifier la limitation **LimitNOFILE**.
Changer cette option dans /etc/my.cnf NE fonctionnera PAS.

::

   # mkdir -p  /etc/systemd/system/mariadb.service.d/
   # echo -ne "[Service]\nLimitNOFILE=32000\n" | tee /etc/systemd/system/mariadb.service.d/limits.conf
   # systemctl daemon-reload
   # service mysqld restart
   
Lancer les services au démarrage
--------------------------------

Activer le lancement automatique de services au démarrage.

Lancer les commandes suivantes sur le serveur Central.

* **CentOS v6** ::

    # chkconfig httpd on
    # chkconfig snmpd on
    # chkconfig mysql on

* **CentOS v7** ::

    # systemctl enable httpd.service
    # systemctl enable snmpd.service
    # systemctl enable mysql.service
    
.. note::
    Si la base de données MySQL est sur un serveur dédié, lancer la commande d'activation mysql sur ce dernier.

Terminer l'installation
-----------------------

:ref:`Clicquer ici pour finaliser le processus d'installation <installation_web_ces>`.

Installer un collecteur
-----------------------

Ce chapitre décrit l'installation d'un collecteur.

Exécutez la commande :

::

    $ yum install centreon-poller-centreon-engine

La communication entre le serveur central et un collecteur se fait via SSH.

Vous devez échanger les clés SSH entre les serveurs.

Si vous n'avez pas de clé SSH privées sur le serveur central pour l'utilisateur 'centreon' :

::

   $ su - centreon
   $ ssh-keygen -t rsa

Vous devez copier cette clé sur le collecteur :

::

    $ ssh-copy-id centreon@your_poller_ip
