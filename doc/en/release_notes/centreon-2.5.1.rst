==============
Centreon 2.5.1
==============

*******
WARNING
*******

If you are upgrading from Centreon 2.5.0 make sure to read the following. 

.. DANGER::
If you are upgrading from a version prior to 2.5.0, just skip this notice and follow this procedure instead:
`https://blog.centreon.com/centreon-2-5-0-release/ <https://blog.centreon.com/centreon-2-5-0-release/>`_.

As usual, database backups are to be made before going any further.

It does not matter whether you run the commands below before or after the web upgrade; do note that those scripts may take some execution time depending on
the size of your log tables.

**********************
You are using NDOUtils
**********************

If you are using NDOUtils, chances are that you have plenty of duplicate entries in your log table. Follow the procedure in order to re insert the logs::

Copy all the log files from the remote pollers to the local poller in /var/lib/centreon/log/POLLERID/. To know the POLLERID of each of your pollers, 
execute the following request against the MySQL server (centreon database)::
  
  mysql> SELECT id, name FROM nagios_server;

Then, execute the following script::

  /path/to/centreon/cron/logAnalyser -a


*************************************
You are upgrading from Centreon 2.5.0
*************************************

There was a bug in Centreon 2.5.0 that probably messed up your reporting data, you will have to recover by running these commands::

  /path/to/centreon/cron/eventReportBuilder -r

  /path/to/centreon/cron/dashboardBuilder -r -s <start_date> -e <end_date>

``start_date`` and ``end_date`` must be formatted like this ``yyyy-mm-dd``; they refer to the time period you wish to rebuild your dashboard on.
