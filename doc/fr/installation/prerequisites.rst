=========
Prérequis
=========

L'interface Centreon web est compatible avec les navigateurs web suivants :

* Chrome (latest version)
* Firefox (latest version)
* Internet Explorer IE 11 (latest version)
* Safari (latest version)

Votre résolution doit être au minimum à 1280 x 768.

*********
Logiciels
*********

Système d'exploitation
======================

Centreon propose un fichier ISO incluant la distribution CentOS 7 et tous les
paquets nécessaires à l'installation.

Si vous préférez utiliser **Red Hat OS** vous devez installer une **version 7**
du système puis y installer les rpms disponible dans nos dépôts de téléchargement.

Les utilisateurs Open Source, sans contrat de support, peuvent utiliser une autre distribution GNU/Linux.
L'installation de la plate-forme sera plus complexe à partir des fichiers sources de chaque composant.
sera plus complexe à partir des fichiers sources de chaque composant.

.. note::
    Seuls les systèmes d'exploitation 64bits (x86_64) sont supportés.

SGBD
====

**Centreon vous recommande d'utiliser MariaDB** plutôt que le moteur MySQL.

+----------+------------+
| Logiciel | Version    |
+==========+============+
| MariaDB  | = 10.2.x   |
+----------+------------+
| MySQL    | = 5.6.x    |
+----------+------------+

Dépendances logicielles
=======================

Le tableau suivant décrit les dépendances logicielles :

+----------+------------------+
| Logiciel | Version          |
+==========+==================+
| Apache   | 2.4              |
+----------+------------------+
| GnuTLS   | >= 2.0           |
+----------+------------------+
| Net-SNMP | 5.7              |
+----------+------------------+
| openssl  | >= 1.0.1k        |
+----------+------------------+
| PHP      | 7.2              |
+----------+------------------+
| Qt       | >= 4.7.4         |
+----------+------------------+
| RRDtools | 1.4.7            |
+----------+------------------+
| zlib     | 1.2.3            |
+----------+------------------+

*******************************
Sélectionner votre architecture
*******************************

.. note::
    Centreon propose un :download:`classeur </files/Centreon_platform_sizing.xlsx>`
    permettant de calculer le dimensionnement de votre plate-forme.

Le tableau suivant présente les prérequis pour une installation de Centreon :

+----------------------+-------------------------+----------------------------+----------------+---------------+
|  Nombre de services  |  Nombre d'hôtes estimé  |  Nombre de collecteurs     |  Central       |  Collecteur   |
+======================+=========================+============================+================+===============+
|           < 500      |           50            |        1 central           |  1 vCPU / 1 GB |               |
+----------------------+-------------------------+----------------------------+----------------+---------------+
|       500 - 2000     |         50 - 200        |        1 central           |  2 vCPU / 2 GB |               |
+----------------------+-------------------------+----------------------------+----------------+---------------+
|      2000 - 7000     |        200 - 700        | 1 central + 1 collecteur   |  4 vCPU / 4 GB | 2 vCPU / 4 GB |
+----------------------+-------------------------+----------------------------+----------------+---------------+
|      7000 - 14000    |        700 - 1400       | 1 central + 2 collecteurs  |  4 vCPU / 8 GB | 2 vCPU / 4 GB |
+----------------------+-------------------------+----------------------------+----------------+---------------+
|     14000 - 21000    |       1400 - 2100       | 1 central + 3 collecteurs  |  4 vCPU / 8 GB | 4 vCPU / 4 GB |
+----------------------+-------------------------+----------------------------+----------------+---------------+
|     21000 - 28000    |       2100 - 2800       | 1 central + 4 collecteurs  |  4 vCPU / 8 GB | 4 vCPU / 4 GB |
+----------------------+-------------------------+----------------------------+----------------+---------------+
|          ...         |           ...           |             ...            |      ...       |      ...      |
+----------------------+-------------------------+----------------------------+----------------+---------------+

.. note::
    Un collecteur peut superviser en moyenne 7000 services actifs.
    Les vCPU doivent avoir une fréquence avoisinant les 3 GHz. Le nombre de
    vCPU par collecteur dépend principalement de la complexité des contrôles.
    Si vous utilisez des connecteurs ou réalisez de nombreux appels vers des
    applications tierces, ajoutez des vCPU supplémentaires.

Ces informations sont à mettre en corrélation avec vos besoins techniques liés au découpage géographique ou topologiques
de votre système. Pour voir ce qu'il est possible de faire avec centreon à ce sujet, reportez vous au chapitre :ref:`Architectures possibles <architectures>`.

.. _diskspace:

*****************************
Définition de l'espace disque
*****************************

.. note::
    Centreon propose un :download:`classeur </files/Centreon_platform_sizing.xlsx>`
    permettant de calculer le dimensionnement de votre plate-forme.

L'espace disque utilisé pour supporter les données issues de la collecte dépend
de plusieurs critères :

* Fréquence des contrôles
* Nombre de contrôles
* Durée de rétention programmée

Le tableau suivant propose une idée de la volumétrie de votre plate-forme :

* Les données sont collectées toutes les 5 minutes
* La période de rétention programmée est de 6 mois
* Deux courbes sont présentes par graphique de performance

+--------------------+------------------------+---------------------------+
| Nombre de services | /var/lib/mysql (in GB) | /var/lib/centreon (in GB) |
+====================+========================+===========================+
| 500                | 10                     | 2.5                       |
+--------------------+------------------------+---------------------------+
| 2000               | 42                     | 10                        |
+--------------------+------------------------+---------------------------+
| 10 000             | 93                     | 27                        |
+--------------------+------------------------+---------------------------+
| 20 000             | 186                    | 54                        |
+--------------------+------------------------+---------------------------+
| 50 000             | 465                    | 135                       |
+--------------------+------------------------+---------------------------+
| 100 000            | 930                    | 270                       |
+--------------------+------------------------+---------------------------+
| ...                | ...                    | ...                       |
+--------------------+------------------------+---------------------------+

*************************
Définition des partitions
*************************

.. note::
    Votre système doit utiliser LVM pour gérer vos partitions.

Serveur Centreon
================

Description des partitions :

+----------------------------+-------------------------------------------------------------------------------------------------------------+
| Partition                  | Taille                                                                                                      |
+============================+=============================================================================================================+
| swap                       | 1 à 1.5 la taille totale de la mémoire vive                                                                 |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /                          | au moins 20 Go                                                                                              |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /var/log                   | au moins 10 Go                                                                                              |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /var/lib/centreon          | :ref:`défini dans le chapitre précédant <diskspace>`                                                        |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /var/lib/centreon-broker   | au moins 5 Go                                                                                               |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /var/cache/centreon/backup | au moins 10 Go (penser à exporter les sauvegarde de manière régulière puis supprimer les données exportées) |
+----------------------------+-------------------------------------------------------------------------------------------------------------+

SGBD MariaDB
============

.. note::
    1 Go d'espace libre non alloué doit être disponible sur le **volum group**
    hébergeant la partition **/var/lib/mysql** lorsque vous souhaitez utiliser
    le mode de sauvegarde **snapshot LVM**.

Description des partitions :

+----------------------------+-------------------------------------------------------------------------------------------------------------+
| Partition                  | Taille                                                                                                      |
+============================+=============================================================================================================+
| swap                       | 1 à 1.5 la taille totale de la mémoire vive                                                                 |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /                          | au moins 20 Go                                                                                              |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /var/log                   | au moins 10 Go                                                                                              |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /var/lib/mysql             | :ref:`défini dans le chapitre précédant <diskspace>`                                                        |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /var/cache/centreon/backup | au moins 10 Go (penser à exporter les sauvegarde de manière régulière puis supprimer les données exportées) |
+----------------------------+-------------------------------------------------------------------------------------------------------------+

Collecteur de supervision
=========================

Description des partitions :

+----------------------------+-------------------------------------------------------------------------------------------------------------+
| Partition                  | Taille                                                                                                      |
+============================+=============================================================================================================+
| swap                       | 1 à 1.5 la taille totale de la mémoire vive                                                                 |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /                          | au moins 20 Go                                                                                              |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /var/log                   | au moins 10 Go                                                                                              |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /var/lib/centreon-broker   | au moins 5 Go                                                                                               |
+----------------------------+-------------------------------------------------------------------------------------------------------------+
| /var/cache/centreon/backup | au moins 5 Go (penser à exporter les sauvegarde de manière régulière puis supprimer les données exportées)  |
+----------------------------+-------------------------------------------------------------------------------------------------------------+

***********************
Utilisateurs et groupes
***********************

.. note::
    Ces données sont présentées pour les systèmes Red Hat / CentOS.
    Les noms des groupes, utilisateurs et services peuvent changer suivant la distribution GNU/Linux.

Description des logiciels et utilisateurs liés :

+-----------------+------------------+-----------------+-----------------------+
| Logiciel        | Service          | Utilisateur     | Commentaire           |
+=================+==================+=================+=======================+
| Apache          | httpd24-httpd    | apache          | démarrage automatique |
+-----------------+------------------+-----------------+-----------------------+
| PHP-FPM         | rh-php72-php-fpm | apache          | démarrage automatique |
+-----------------+------------------+-----------------+-----------------------+
| MySQL (MariaDB) | mysqld (mysql)   | mysql           | démarrage automatique |
+-----------------+------------------+-----------------+-----------------------+
| Centreon        | centcore         | centreon        | démarrage automatique |
+-----------------+------------------+-----------------+-----------------------+
| Centreon        | centreontrapd    | centreon        | démarrage automatique |
+-----------------+------------------+-----------------+-----------------------+
| Centreon Broker | cbwd             | centreon-broker | démarrage automatique |
+-----------------+------------------+-----------------+-----------------------+
| Centreon Broker | cbd              | centreon-broker | démarrage automatique |
+-----------------+------------------+-----------------+-----------------------+
| Centreon Engine | centengine       | centreon-engine | démarrage automatique |
+-----------------+------------------+-----------------+-----------------------+

Description des logiciels optionnels et utilisateurs liés :

+-----------------+-----------------+-----------------+------------------------------------------------------+
| Logiciel        | Service         | Utilisateur     | Commentaire                                          |
+=================+=================+=================+======================================================+
| Centreon VMware | centreon_vmware | centreon        | non installé par défaut                              |
+-----------------+-----------------+-----------------+------------------------------------------------------+
| RRDtool         | rrdcached       | rrdcached       | non activé et non parémétré dans Centreon par défaut |
+-----------------+-----------------+-----------------+------------------------------------------------------+

Description des groupes et utilisateurs liés :

+-----------------+----------------------------------------+
| Groupe          | Utilisateurs                           |
+=================+========================================+
| apache          | nagios,centreon                        |
+-----------------+----------------------------------------+
| centreon        | centreon-engine,centreon-broker,apache |
+-----------------+----------------------------------------+
| centreon-broker | centreon,nagios,centreon-engine,apache |
+-----------------+----------------------------------------+
| centreon-engine | centreon-broker,apache,nagios,centreon |
+-----------------+----------------------------------------+

Description des utilisateurs, umask et répertoire utilisateur :

+-----------------+-------+--------------------------+
| Utilisateur     | umask | home                     |
+=================+=======+==========================+
| root            | 0022  | /root                    |
+-----------------+-------+--------------------------+
| apache          | 0022  | /var/www                 |
+-----------------+-------+--------------------------+
| centreon        | 0002  | /var/spool/centreon      |
+-----------------+-------+--------------------------+
| centreon-broker | 0002  | /var/lib/centreon-broker |
+-----------------+-------+--------------------------+
| centreon-engine | 0002  | /var/lib/centreon-engine |
+-----------------+-------+--------------------------+
| mysql           | 0002  | /var/lib/mysql           |
+-----------------+-------+--------------------------+
