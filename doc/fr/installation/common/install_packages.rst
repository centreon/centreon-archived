***********************
Étapes pré-installation
***********************

SELinux doit être désactivé. Pour cela vous devez modifier le fichier */etc/selinux/config*
et remplacer "enforcing" par "disabled" comme dans l'exemple suivant : ::

    SELINUX=disabled

.. note::
    Après avoir sauvegardé le fichier, veuillez redémarrer votre système
    d'exploitation pour prendre en compte les changements.

Une vérification rapide permet de confirmer le statut de SELinux ::

    $ getenforce
    Disabled

***********************
Installation des dépôts
***********************

Dépôt *Software collections* de Red Hat
---------------------------------------

Afin d'installer les logiciels Centreon, le dépôt *Software collections* de Red Hat doit être activé.

.. note::
    Le dépôt *Software collections* est nécessaire pour l'installation de PHP
    7 et les librairies associées.

Exécutez la commande suivante : ::

    # yum install centos-release-scl

Le dépôt est maintenant installé.

Dépôt Centreon
--------------

Afin d'installer les logiciels Centreon à partir des dépôts, vous devez au
préalable installer le fichier lié au dépôt. Exécutez la commande suivante. 

Installation : ::

    # wget http://yum.centreon.com/standard/18.10/el7/stable/noarch/RPMS/centreon-release-18.10-2.el7.centos.noarch.rpm -O /tmp/centreon-release-18.10-2.el7.centos.noarch.rpm
    # yum install --nogpgcheck /tmp/centreon-release-18.10-2.el7.centos.noarch.rpm

Le dépôt est maintenant installé.

*******************************
Installation du serveur central
*******************************

Ce chapitre décrit l'installation d'un serveur central Centreon.

Exécutez la commande : ::

    # yum install centreon-base-config-centreon-engine centreon

Installer MySQL sur le même serveur
-----------------------------------

Ce chapitre décrit l'installation de MySQL sur un serveur comprenant Centreon.

Exécutez la commande : ::

    # yum install MariaDB-server
    #  systemctl restart mysql

Système de gestion de base de données
-------------------------------------

La base de données MySQL doit être disponible pour pouvoir continuer l'installation
(localement ou non). Pour information nous recommandons MariaDB.

Pour les systèmes CentOS / RHEL en version 7, il est nécessaire de modifier
la limitation **LimitNOFILE**. Changer cette option dans /etc/my.cnf NE
fonctionnera PAS: ::

    # mkdir -p  /etc/systemd/system/mariadb.service.d/
    # echo -ne "[Service]\nLimitNOFILE=32000\n" | tee /etc/systemd/system/mariadb.service.d/limits.conf
    # systemctl daemon-reload
    # systemctl restart mysql

Fuseau horaire PHP
------------------

La timezone par défaut de PHP doit être configurée. Executer la commande suivante : ::

    # echo "date.timezone = Europe/Paris" > /etc/opt/rh/rh-php71/php.d/php-timezone.ini

.. note::
    Changez **Europe/Paris** par votre fuseau horaire.

Après avoir réalisé la modification, redémarrez le service Apache : ::

    # systemctl restart httpd

Pare-feu
--------

Paramétrer le pare-feu système ou désactiver ce dernier. Pour désactiver ce
dernier exécuter les commandes suivantes : ::

    # systemctl stop firewalld
    # systemctl disable firewalld
    # systemctl status firewalld

Lancer les services au démarrage
--------------------------------

Activer le lancement automatique de services au démarrage.

Lancer les commandes suivantes sur le serveur Central : ::

    # systemctl enable httpd
    # systemctl enable snmpd
    # systemctl enable snmptrapd
    # systemctl enable rh-php71-php-fpm
    # systemctl enable centcore
    # systemctl enable centreontrapd
    # systemctl enable cbd
    # systemctl enable centengine

.. note::
    Si la base de données MySQL est sur un serveur dédié, lancer la commande
    d'activation mysql sur ce dernier.

Terminer l'installation
-----------------------

Avant de démarrer la configuration via l'interface web la commande suivante
doit être exécutée : ::

    # systemctl start rh-php71-php-fpm
    # systemctl start httpd
    # systemctl start mysqld
    # systemctl start cbd
    # systemctl start snmpd
    # systemctl start snmptrapd
