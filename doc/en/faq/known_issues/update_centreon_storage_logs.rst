.. _update_centreon_storage_logs:

==================================
Update centreon_storage.logs table
==================================

The purpose of this procedure is to allow modification of log_id column of centreon_storage.logs table without service interruption.
Requirements
============

Password
--------

Before starting to run the script, it is necessary to get root password of Centreon database.

Enable PHP on Centreon 19.10 (**Centos7**)
------------------------------------------

On the new version 19.10 of Centreon (Centos7), it is mandatory to enable PHP before running PHP scripts in command line.

Then, run the following command:
::

# scl enable rh-php72 bash

Explanations
============

The update will proceed as follows:

1. renaming the table **centreon_storage.logs** in **centreon_storage.logs_old**
2. creating the new table **centreon_storage.logs**
3. migration of data by partition

Update
======

On regular installation of Centreon, the script is located here:
::

# usr/share/centreon/tools/update_centreon_storage_logs.php

Run in interactive mode (<10 million rows)
------------------------------------------
    1. go into the following folder : /usr/share/centreon/tools
    2. then, run the following script:

::

# php update_centreon_storage_logs.php

Run in non-interactive mode (>10 million rows)
-----------------------------------------------------------------
    1. go into the following folder : /usr/share/centreon/tools
    2. then, run the following script:

::

# nohup php update_centreon_storage_logs.php --password=root_password [--keep |--no-keep] > update_logs.logs &

.. note:: Run options :

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
