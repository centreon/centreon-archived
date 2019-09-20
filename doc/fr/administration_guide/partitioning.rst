.. _Centreon-Partitioning:

####################################
Partitionnement des bases de données
####################################

============
Présentation
============

Certaines tables de la base de données 'centreon_storage' sont partitionnées
afin :

* D'optimiser le temps d'exécution de nombreuses requêtes.
* D'optimiser la purge des données.
* De minimiser la reconstruction des tables en erreur lors d'un crash du SGBD.

Une partition par jour est créée pour les tables suivantes :

* **data_bin** : données de performance.
* **logs** : journaux d'évènements de la collecte des moteurs de supervision.
* **log_archive_host** : données de disponibilité des hôtes.
* **log_archive_service** : données de disponibilité des services.

.. note::
    Ce partitionnement comporte des limitations :
    
    * Le nombre maximal de partitions (pour une table) est 1024
    * Les clés étrangères ne sont pas supportées

Plus de détails sur le partitionnement MySQL `à cette adresse
<http://dev.mysql.com/doc/refman/5.5/en/partitioning.html>`_.

=========
Prérequis
=========

Les prérequis nécessaires pour l'utilisation de ce module sont les suivants :

* php-mysql
* Pear-DB
* MySQL (>= 5.1.x)

Le paramètre MySQL **open_files_limit** doit être fixé à 32000 dans la section
[server] : ::

    [server]
    open_files_limit = 32000

.. note::
    En installant Centreon via l'ISO, ce paramètre est déjà configuré. Si vous
    installez Centreon via les RPM sur votre propre server RedHat ou CentOS, vous
    serez obligé de réaliser cette configuration manuellement. N'oubliez pas de
    redémarrer le service mysql / mariadb si vous avez besoin de configurer ce
    paramètre dans le fichier my.cnf.

Si vous utilisez systemd, il est nécessaire de créer le fichier
**/etc/systemd/system/mariadb.service.d/mariadb.conf** : ::

    [Service]
    LimitNOFILE=32000

Puis recharger systemd et MySQL : ::

    # systemctl daemon-reload
    # systemctl restart mysql

=============
Configuration
=============

La durée de rétention des données est programmée dans le menu
**Administration > Paramètres > Options** :

.. image:: /images/guide_exploitation/partitioning_conf.png
    :align: center

Le paramétrage est le suivant :

* **Retention duration for partitioning** : durée de rétention pour les tables partitionnées, par défaut **365 jours**.
* **Forward provisioning** : nombre de partitions créées en avance, par défaut **10 jours**.
* **Backup directory for partitioning** : répertoire de sauvegarde des partitions, par défaut **/var/cache/centreon/backup**.

==============
Fonctionnement
==============

Le partitionnement utilise des fichiers XML, présents dans le répertoire 
**/usr/share/centreon/config/partition.d/** pour créer les partitions
nécessaires.

Chaque jour, un script lancé par un cron réalise la création des tables
manquantes ou celles en avance : ::

    0 4 * * * centreon /opt/rh/rh-php72/root/bin/php /usr/share/centreon/cron/centreon-partitioning.php >> /var/log/centreon/centreon-partitioning.log 2>&1

Exemple de fichier de partitionnement **partitioning-data_bin.xml** : ::

    <?xml version="1.0" encoding="UTF-8"?>
    <centreon-partitioning>
        <table name="data_bin" schema="centreon_storage">
            <activate>1</activate>
            <column>ctime</column>
            <type>date</type>
            <createstmt>
    CREATE TABLE IF NOT EXISTS `data_bin` (
      `id_metric` int(11) DEFAULT NULL,
      `ctime` int(11) DEFAULT NULL,
      `value` float DEFAULT NULL,
      `status` enum('0','1','2','3','4') DEFAULT NULL,
      KEY `index_metric` (`id_metric`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            </createstmt>
        </table>
    </centreon-partitioning>

======================================
Migration des tables non partitionnées
======================================

La ligne de commande exécute la procédure suivante :

* Renomme la table existante (‘xxx’ devient ‘xxx_old’)
* Crée une table partitionnée vide
* Migre les données dans la table partitionnée (instructions ‘SELECT INSERT’)

Des vérifications doivent être faites avant :

* L’espace disponible sur le volume sur lequel se trouvent les bases MySQL doit être suffisant pour contenir deux fois la taille des tables traitées (Index + données).
* Les tables ne doivent pas contenir de données dans le futur (le temps est un facteur clé pour la mise en place du partitionnement).
* La mémoire sur le serveur MySQL doit être suffisante.

.. warning::
    Les requêtes/instructions ‘SELECT INSERT’ vont verrouiller la table et probablement certains traitements.

La migration de la table est effectuée en utilisant l’option **-m** et en
précisant le nom de la table à migrer : ::

    # /opt/rh/rh-php72/root/bin/php /usr/share/centreon/bin/centreon-partitioning.php -m data_bin

Si la migration de la table est ok l’ancienne table peut être supprimée avec
la commande suivante : ::

    # mysql -u root centreon_storage
    MariaDB [centreon_storage]> DROP TABLE data_bin_old;

================================================
Supervision du fonctionnement du partitionnement
================================================

Le Plugin Pack **Centreon Database** permet de contrôler que le nombre de
partitions créées en avances est suffisant. Il est recommandé d'installer et
de déployer ce dernier.

Il est également possible de visualiser les tables partitionnées et la
consommation associée à chaque partition via le menu **Administration >
Statut de la plateforme > Bases de données** :

.. image:: /images/guide_exploitation/partitioning-state.png
    :align: center

Des informations plus globales sur l’état de santé des bases de données sont
également présentes :

.. image:: /images/guide_exploitation/Global-DB-Information.png
    :align: center
