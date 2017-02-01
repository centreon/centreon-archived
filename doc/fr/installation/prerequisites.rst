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

Si vous souhaitez **utiliser Centreon ISO v3.x, le système d'exploitation sera CentOS en version v6**.

Si vous préférez utiliser **Red Hat OS** vous devez installer une **version v6** du système puis y 
installer les rpms disponible dans nos repository de téléchargement.

Enfin, vous pouvez utiliser une autre distribution GNU/Linux mais l'installation de la plate-forme
sera plus complexe à partir des fichiers sources de chaque composants.

SGBD
====

**Centreon vous recommande d'utiliser MariaDB** plutôt que le moteur MySQL.

+----------+-----------+
| Logiciel | Version   |
+==========+===========+
| MariaDB  | >= 10.1.x |
+----------+-----------+
| MySQL    | >= 5.6.x  |
+----------+-----------+

Dépendances logicielles
=======================

Le tableau suivant dérit les dépendances logicielles :

+----------+------------------+
| Logiciel | Version          |
+==========+==================+
| Apache   | 2.2 & 2.4        |
+----------+------------------+
| GnuTLS   | >= 2.0           |
+----------+------------------+
| Net-SNMP | 5.5              |
+----------+------------------+
| openssl  | >= 1.0.1e        |
+----------+------------------+
| PHP      | >= 5.3.0 & < 5.5 |
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

Le tableau suivant présente les prérequis pour une installation de CES v3.x :

+----------------------+-----------------------------+---------------------------+----------------+---------------+
|  Nombre de services  |  Nombre d'hôtes estimé      |  Nombre de collecteurs    |  Central       |  Collecteur   |
+======================+=============================+===========================+================+===============+
|           < 500      |             50              |        1 central          |  1 vCPU / 1 GB |               |
+----------------------+-----------------------------+---------------------------+----------------+---------------+
|       500 - 2000     |           50 - 200          |        1 central          |  2 vCPU / 2 GB |               |
+----------------------+-----------------------------+---------------------------+----------------+---------------+
|      2000 - 10000    |          200 - 1000         | 1 central + 1 collecteur  |  4 vCPU / 4 GB | 1 vCPU / 2 GB |
+----------------------+-----------------------------+---------------------------+----------------+---------------+
|     10000 - 20000    |         1000 - 2000         | 1 central + 1 collecteur  |  4 vCPU / 8 GB | 2 vCPU / 2 GB |
+----------------------+-----------------------------+---------------------------+----------------+---------------+
|     20000 - 50000    |         2000 - 5000         | 1 central + 2 collecteurs |  4 vCPU / 8 GB | 4 vCPU / 2 GB |
+----------------------+-----------------------------+---------------------------+----------------+---------------+
|     50000 - 100000   |         5000 - 10000        | 1 central + 3 collecteurs |  4 vCPU / 8 GB | 4 vCPU / 2 GB |
+----------------------+-----------------------------+---------------------------+----------------+---------------+

.. note::
    Les vCPU doivent avoir une fréquence avoisinant les 3 GHz

Ces informations sont à mettre en corrélation avec vos besoins techniques liés au découpage géographique ou topologiques 
de votre système. Pour voir ce qu'il est possible de faire avec centreon à ce sujet, reportez vous au chapitre :ref:`Architectures possibles <architectures>`.


*****************************
Définition de l'espace disque
*****************************

L'espace disque utilisé pour supporter les données issues de la collecte dépend
de plusieurs critères :

* Fréquence des contrôles
* Nombre de contrôles
* Durée de rétention programmée

Le tableau suivant propose une idée de la volumétrie de votre plate-forme :

* Les données sont collectées toutes les 5 minutes
* La période de rétention programmée est de 6 mois
* Deux courbes sont présentes par graphique de performance

+------------------------+----------------+-------------------+
|  Nombre de services    | /var/lib/mysql | /var/lib/centreon |
+========================+================+===================+
|        < 500           |     10 GB      |      2.5 GB       |
+------------------------+----------------+-------------------+
|       500 - 2000       |     42 GB      |       10 GB       |
+------------------------+----------------+-------------------+
|      2000 - 10000      |    210 GB      |       50 GB       |
+------------------------+----------------+-------------------+
|      10000 - 20000     |    420 GB      |      100 GB       |
+------------------------+----------------+-------------------+
|      20000 - 50000     |    1.1 TB      |      250 GB       |
+------------------------+----------------+-------------------+
|     50000 - 100000     |      2,3 TB    |        1 TB       |
+------------------------+----------------+-------------------+

*************************
Définition des partitions
*************************

.. note::
    Votre système doit utiliser LVM pour gérer vos partitions.

Serveur Centreon
================

Description des partitions :

* / (au moins 20 GB)
* swap (au moins 1x la taille de la mémoire vive)
* /var/log (au moins 10 GB)
* /var/lib/centreon (défini dans le chapitre précédant)
* /var/lib/centreon-broker (au moins 5 GB)
* /var/cache/centreon/backup (utilisé pour la sauvegarde)

SGBD MariaDB
============

Description des partitions :

* / (au moins 10 GB)
* swap (au moins 1x la taille de la mémoire vive)
* /var/log (au moins 10 GB)
* /var/lib/mysql (défini dans le chapitre précédant)
* /var/cache/centreon/backup (utilisé pour la sauvegarde)

Collecteur de supervision
=========================

Description des partitions :

* / (au moins 20 GB)
* swap (au moins 1x la taille de la mémoire vive)
* /var/log (au moins 10 GB)
* /var/lib/centreon-broker (au moins 5 GB)
* /var/cache/centreon/backup (utilisé pour la sauvegarde)
