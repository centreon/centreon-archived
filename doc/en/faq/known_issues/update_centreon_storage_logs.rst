.. _update_centreon_storage_logs:

==================================
Update centreon_storage.logs table
==================================

Issues
======

Some customers have reached the maximum number of records in the centreon_storage.logs table which currently can only
contain 2 147 483 647 records (signed integer, count from 0).

Broker can therefore no longer add any elements to this table.

Objectif
========

The purpose of this procedure is to allow the modification of the log_id column of the centreon_storage.logs table.

The easiest way to make this change would be to execute the following command directly on the table to change the type
of the log_id column: ::

    ALTER TABLE centreon_storage.logs MODIFY log_id BIGINT(20) NOT NULL AUTO_INCREMENT

However, this operation would block the table during the modification and could take several hours before the end of
the operation. Broker would be forced to retain and block the logs upload on the Centreon interface throughout the
process.

Nevertheless, this option could be considered if the table contains only a few records (< 10 million).

For large volumes we have created a script allowing the migration of data by partition from the old table to the new
one without service interruption.

Requirements
============

Password
--------

Before starting to run the script, it is necessary to get root password of Centreon database.

Enable PHP on Centreon 19.10 (**Centos7**)
------------------------------------------

On the new version 19.10 of Centreon (Centos7), it is mandatory to enable PHP before running PHP scripts in command line.

Then, run the following command: ::

    # scl enable rh-php72 bash

Explanations
============

Functional diagram:

.. image:: /images/faq/workflow_centreon_storage_logs.png
    :align: center

The update will proceed as follows:

1. renaming the table **centreon_storage.logs** in **centreon_storage.logs_old**
2. creating the new table **centreon_storage.logs**
3. migration of data by partition

Update
======

On regular installation of Centreon, the script is located here: ::

# usr/share/centreon/tools/update_centreon_storage_logs.php

Run in interactive mode (<10 million rows)
------------------------------------------

1. go into the following folder : /usr/share/centreon/tools
2. then, run the following script: ::

    # php update_centreon_storage_logs.php

Run in non-interactive mode (>10 million rows)
----------------------------------------------

1. go into the following folder : /usr/share/centreon/tools
2. then, run the following script: ::

    # nohup php update_centreon_storage_logs.php --password=root_password [--keep |--no-keep] > update_logs.logs &

.. note:: Run options:
    
    --password:
        root password of Centreon database (eg. --password=my_root_password).
    --keep:
        keep data of old table to centreon_storage.logs_old.
    --no-keep:
        remove progressively data from centreon_storage.logs_old during the migration to the new table centreon_storage.logs.
    --temporary-path:
        directory in which temporary files will be stored

.. warning::
    If you decide to keep the data from the old centreon_storage.logs table, do not forget to check the available disk space.

Resuming migration
------------------

If, for any reason, you want to stop the migration script, know that it is possible to restart it so that it can resume
where it was.

.. note:: Recovery option:
    
    --continue:
        This option is used to specify the resumption of migrations after an execution interruption.
        
        If this option is specified, the structures of the *centreon_storage.logs* and *centreon_storage.logs_old*
        tables will not be affected.

For this there are two possibilities:

1. y specifying the name of the last partition processed.
2. Without specifying the name of the last partition processed, the script will use the first non-empty partition of
  the centreon_storage.logs_old table.

.. warning::
    Using the *--continue* option without specifying the name of the last partition being processed should only be used
    if you specified the *--no-keep* option the previous time the script was run.

Examples: ::

    # nohup php update_centreon_storage_logs.php --continue [--password=root_password]

or ::

    # nohup php update_centreon_storage_logs.php --continue=last_partition_name [--password=root_password]

.. note::
    To find the name of the last partition processed, just look in the script processing logs for the name of the last
    partition being processed before the script was stopped.