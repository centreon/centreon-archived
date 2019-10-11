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

    # yum install -y http://yum.centreon.com/standard/19.10/el7/stable/noarch/RPMS/centreon-release-19.10-1.el7.centos.noarch.rpm

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

.. include:: common/sql_strict_mode.rst

Installer un serveur Centreon central sans base de données
----------------------------------------------------------

Exécutez la commande : ::

    # yum install centreon-base-config-centreon-engine

.. _dedicateddbms:

Installer le SGBD sur un serveur dédié
--------------------------------------

Exécutez les commandes : ::

    # yum install centreon-database
    # systemctl daemon-reload
    # systemctl restart mysql

.. note::
    le paquet **centreon-database** installe un serveur de base de données optimisé pour l'utilisation avec Centreon.

Puis créer un utilisateur **root** distant : ::

    mysql> CREATE USER 'root'@'IP' IDENTIFIED BY 'PASSWORD';
    mysql> GRANT ALL PRIVILEGES ON *.* TO 'root'@'IP' WITH GRANT OPTION;
    mysql> FLUSH PRIVILEGES;

.. note::
    Remplacez **IP** par l'adresse IP publique du serveur Centreon et **PASSWORD**
    par le mot de passe de l'utilisateur **root**.

.. warning::
    MySQL >= 8 requiert un mot de passe fort. Utilisez des lettres minuscules et majuscules ainsi que des caractères
    numériques et spéciaux; ou désinstallez le plugin **validate_password** de MySQL en utilisant la commande
    suivantes : ::
        
        mysql> uninstall plugin validate_password;

.. warning::
    Si PHP est utilisé dans une version 7.1 antérieure à la version 7.1.16, ou PHP 7.2 antérieure à 7.2.4, le
    plugin de mot de passe doit être défini à mysql_native_password pour MySQL 8 Server, car sinon des erreurs
    similaires à *The server requested authentication method unknown to the client [caching_sha2_password]* peuvent
    apparaitre, même si caching_sha2_password n'est pas utilisé.
    
    Ceci est dû au fait que MySQL 8 utilise par défaut caching_sha2_password, un plugin qui n'est pas reconnu par les
    anciennes versions de PHP. À la place il faut modifier le paramètre *default_authentication_plugin=
    mysql_native_password* dans le fichier **my.cnf**.
    
    Changez la méthode de stockage du mot de passe, utilisez la commande suivante : ::
    
        mysql> ALTER USER 'root'@'IP' IDENTIFIED WITH mysql_native_password BY 'PASSWORD';
        mysql> FLUSH PRIVILEGES;

.. include:: common/sql_strict_mode.rst

Une fois l'installation terminée vous pouvez supprimer ce compte via la commande : ::
        
    mysql> DROP USER 'root'@'IP';

Système de gestion de base de données
-------------------------------------

La base de données MySQL doit être disponible pour pouvoir continuer l'installation
(localement ou non). Pour information nous recommandons MariaDB.

Pour les systèmes CentOS / RHEL en version 7, il est nécessaire de modifier la limitation **LimitNOFILE**. Changer
cette option dans /etc/my.cnf *ne fonctionnera pas*.

**Pour MariaDB** : ::

    # mkdir -p  /etc/systemd/system/mariadb.service.d/
    # echo -ne "[Service]\nLimitNOFILE=32000\n" | tee /etc/systemd/system/mariadb.service.d/limits.conf
    # systemctl daemon-reload
    # systemctl restart mysql

**Pour MySQL** : ::

    # mkdir -p  /etc/systemd/system/mysqld.service.d
    # echo -ne "[Service]\nLimitNOFILE=32000\n" | tee /etc/systemd/system/mysqld.service.d/limits.conf
    # systemctl daemon-reload
    # systemctl restart mysqld

Fuseau horaire PHP
------------------

La timezone par défaut de PHP doit être configurée. Exécuter la commande suivante : ::

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
    Si la base de données MariaDB est sur un serveur dédié, lancer la commande
    d'activation mysql sur ce dernier : ::
        
        # systemctl enable mysql
    
    ou pour Mysql : ::
        
        # systemctl enable mysqld

Terminer l'installation
-----------------------

Avant de démarrer la configuration via l'interface web les commandes suivantes
doivent être exécutées : ::

    # systemctl start rh-php72-php-fpm
    # systemctl start httpd24-httpd
    # systemctl start mysqld
    # systemctl start centreon
    # systemctl start snmpd
    # systemctl start snmptrapd
