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

    # yum install -y http://yum.centreon.com/standard/19.04/el7/stable/noarch/RPMS/centreon-release-19.04-1.el7.centos.noarch.rpm

Le dépôt est maintenant installé.

.. note::
    Si le paquet n'est pas installé, exécutez la commande : ::

        # yum install wget

*******************************
Installation du serveur central
*******************************

Ce chapitre décrit l'installation d'un serveur central Centreon.

Installer un serveur Centreon central avec base de données
----------------------------------------------------------

Exécutez la commande : ::

    # yum install centreon
    # systemctl restart mysql

Installer un serveur Centreon central sans base de données
----------------------------------------------------------

Exécutez la commande : ::

    # yum install centreon-base-config-centreon-engine

Installer MySQL sur un serveur dédié
------------------------------------

Exécutez les commandes : ::

    # yum install centreon-database
    # systemctl daemon-reload
    # systemctl restart mysql

.. note::
    le paquet **centreon-database** installe un serveur de base de données optimisé pour l'utilisation avec Centreon.

.. note::
    Centreon n'est pas encore **compatible** avec le mode STRICT de SQL. Veuillez
    vous assurer que le mode soit bien désactivé. Pour plus d'information sur la
    désactivation du mode vous pouvez consulter la `documentation officielle
    <https://mariadb.com/kb/en/library/sql-mode/#strict-mode>`_ de MariaDB pour
    le désactiver

Puis créer un utisateur **root** distant : ::

    MariaDB [(none)]> GRANT ALL PRIVILEGES ON *.* TO 'root'@'IP' IDENTIFIED BY 'PASSWORD' WITH GRANT OPTION;

.. note::
    Remplacez **IP** par l'adresse IP publique du serveur Centreon et **PASSWORD**
    par le mot de passe de l'utilisateur **root**. Une fois l'installation terminée
    vous pouvez supprimer ce compte via la commande : ::
        
        MariaDB [(none)]> DROP USER 'root'@'IP';

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

    # echo "date.timezone = Europe/Paris" > /etc/opt/rh/rh-php72/php.d/php-timezone.ini

.. note::
    Changez **Europe/Paris** par votre fuseau horaire. La liste des fuseaux horaires
    est disponible `ici <http://php.net/manual/en/timezones.php>`_.

Après avoir réalisé la modification, redémarrez le service PHP-FPM : ::

    # systemctl restart rh-php72-php-fpm

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

    # systemctl enable httpd24-httpd
    # systemctl enable snmpd
    # systemctl enable snmptrapd
    # systemctl enable rh-php72-php-fpm
    # systemctl enable centcore
    # systemctl enable centreontrapd
    # systemctl enable cbd
    # systemctl enable centengine
    # systemctl enable centreon

.. note::
    Si la base de données MySQL est sur un serveur dédié, lancer la commande
    d'activation mysql sur ce dernier : ::
    
        # systemctl enable mysql

Terminer l'installation
-----------------------

Avant de démarrer la configuration via l'interface web les commandes suivantes
doivent être exécutées : ::

    # systemctl start rh-php72-php-fpm
    # systemctl start httpd24-httpd
    # systemctl start mysqld
    # systemctl start cbd
    # systemctl start snmpd
    # systemctl start snmptrapd
