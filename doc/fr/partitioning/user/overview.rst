============
Présentation
============

Centreon Partitioning n'offre pas toutes les fonctionnalités de partitionnement disponibles dans MySQL.
Quelques avantages du partitionnement:
 - Le système Centreon Purge est amélioré (le script cron 'centreonPurge.sh' peut être désactivé)
 - Des requêtes peuvent être grandement optimisées (plus de détails `http://dev.mysql.com/doc/refman/5.5/en/partitioning-pruning.html`_)
 - Limite l'étendue d'un crash (reconstruction par partition) 

Le partitionnement peut être utilisé sur des tables MyISAM ou InnoDB. Il y a cependant des limitations:
 - Le nombre maximal de partitions (pour une table) est 1024
 - Les clés étrangères ne sont pas supportées
 
Fonctionnalités
---------------

 - Durée de partitionnement
 - Création de table partitionnées 
 - Partitionne des tables déjà existantes

Usage
-----

Toutes les actions sont faites en ligne de commande. Un fichier de configuration XML est utilisé::

  # php /usr/share/centreon-partitioning/bin/centreon-partitioning.php
  Program options:
    -h  print program usage
  Execution mode:
    -c <configuration file>       create tables and create partitions
    -m <configuration file>       migrate existing table to partitioned table
    -u <configuration file>       update partitionned tables with new partitions
    -o <configuration file>       optimize tables
    -p <configuration file>       purge tables
    -b <configuration file>       backup last part for each table
    -l <table> -s <database name> List all partitions for a table.

