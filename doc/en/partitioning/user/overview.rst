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
 - Create partitioned table 
 - Migrate existing table to partitioned table

Basic usage
-----------

All actions are done by the command line. It uses a XML configuration file::

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

