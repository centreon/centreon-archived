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

 - Partitionnement par durée
 - Partitionnement des tables déjà existantes
 - Mise à jour des tables partitionnées

Usage
-----

La migration est réalisée en ligne de commande. Un fichier de configuration XML est utilisé::

  # php /usr/share/centreon/bin/centreon-partitioning.php -m <table>

