.. _Centreon-Partitioning:

####################################
Partitionnement des bases de données
####################################

============
Présentation
============

Le module Centreon Partitioning est maintenant intégré de base avec Centreon Web, il offre différentes fonctionnalités et avantages.

- Il permet de partitionner les tables MySQL en fonction de la date des lignes. Ce qui offre une optimisation du temps d'exécution de nombreuses requêtes.
- La purge des données est améliorée, il est maintenant juste nécessaire de supprimer les partitions trop anciennes.
- L'étendue d'un crash MySQL est limitée par la reconstruction des partitions en erreur.
- Les tables existantes peuvent être partitionnées

.. note::

  Ce partionnement comporte des limitations :
  - Le nombre maximal de partitions (pour une table) est 1024
  - Les clés étrangères ne sont pas supportées

Depuis la version 2.8.0 de Centreon Web, les tables logs, data_bin, log_archive_host et log_archive_service sont partionnées automatiquement lors de l'installation.

Plus de détails sur le partitionnement MySQL `ici
<http://dev.mysql.com/doc/refman/5.5/en/partitioning.html>`_.

==========
Pré-requis
==========

Les pré-requis nécessaires pour l'utilisation de ce module sont les suivants :

* php-mysql
* Pear-DB
* MySQL (>= 5.1.x)


Le paramètre MySQL **open_files_limit** doit être fixé à 32000 dans la section [server] :

::

  [server]
  open_files_limit = 32000

.. note::
    En installant via l'ISO de Centreon, ce paramètre est déjà convenablement configuré. Si vous installez les rpm sur votre propre install RedHat ou CentOS, vous serez obligé de le faire vous même. 
    N'oubliez pas de redémarrer le service mysql / mariadb si vous avez besoin de configurer ce paramètre dans le fichier my.cnf. 

Si vous utilisez systemd, il est nécessaire de créer le fichier "/etc/systemd/system/mariadb.service.d/mariadb.conf" :

::

  [Service]
  LimitNOFILE=32000

Puis recharger systemd et MySQL :

::

  $ systemctl daemon-reload
  $ systemctl restart mysql

Afin d'exploiter le module, vous pouvez suivre la documentation suivante :

.. toctree::

  user/index
