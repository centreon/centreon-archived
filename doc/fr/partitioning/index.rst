.. _Centreon-Partitioning:

Centreon-Partitioning
=====================

===========
Présentaion
===========

Le module Centreon Partioning est maintenant intégré de base avec Centreon Web, il offre différentes fonctionnalités et avantages.

- Il permet de partitionner les tables MySQL en fontion de la date des lignes. Ce qui offre une optimisation du temps d'execution de nombreuses requêtes.
- La purge des données est améliorée, il est maintenant justé nécessaire de supprimer les partitions trop anciennes.
- L'étendue d'un crash MySQL est limité par la reconstruction des partitions en erreur.
- Les tables existantes peuvent être partitionnées

.. note::

  Ce partionement comporte des limitations :
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


Afin d'exploiter le module, vous pouvez suivre la documentation suivante :

.. toctree::

  user/index
