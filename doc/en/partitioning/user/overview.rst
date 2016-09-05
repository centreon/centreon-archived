========
Overview
========

Centreon Partitioning does not offer all partitioning features that are available with MySQL.
Some advantages of partitioning:
 - Centreon Purge system improved (cron script 'centreonPurge.sh' can be disabled)
 - Some queries can be greatly optimized (more details `http://dev.mysql.com/doc/refman/5.5/en/partitioning-pruning.html`_)
 - Limit crash spread (can repair by partition) 

Partitioning can be used for MyISAM or InnoDB tables. Nevertheless, there are some limitations (main above):
 - The maximum number of partitions (for one table) is 1024
 - Foreign keys are not supported
 
Features
--------

 - Range Partitioning
 - Migrate existing table to partitioned table
 - Update partitioned table

Basic usage
-----------

The migration is done by the command line. It uses a XML configuration file::

  # php /usr/share/centreon/bin/centreon-partitioning.php -m <table>

