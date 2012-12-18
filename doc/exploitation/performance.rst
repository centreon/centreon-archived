.. _performance:

===========
Performance
===========

This is a guide on improving Centreon's performance

*********
Databases
*********

The database server is one of the central components of Centreon. Its
performance has a direct impact on the end user application's speed. Centreon
uses two or three databases depending on your monitoring broker:

 * ``centreon`` -- Storing metadata
 * ``centreon_storage`` -- Real-time monitoring and history
 * ``centreon_status`` -- Real-time monitoring for ``ndo2db``

The database ``centreon_status`` is installed even if you don't use ``ndo2db``.

Indexes
=======

Databases use indexes to speed up queries. In case indexes are missing queries
are executed slower.

.. _synchronizing-indexes:

Synchronizing indexes
*********************

Starting with Centreon ``2.4.0`` for each release, index information files are
generated. They are found in ``data`` folder usually located next to the
``bin`` or ``www`` folders. They are JSON files and there is one for each database:

 * ``centreonIndexes.json`` -- Indexes for ``centreon`` database
 * ``centreonStorageIndexes.json`` -- Indexes for ``centreon_storage`` database
 * ``centreonStatusIndexes.json`` -- Indexes for ``centreon_status`` database

Check if your database is desynchronized::

  $ cd CENTREONBINDIR
  $ ./import-mysql-indexes -d centreon -i ../data/centreonIndexes.json

If any differences are detected you can synchronize your database. The process
usually takes several minutes BUT **if your database contains a lot of data and no
index exists the process may take up to 2 hours**. Make sure you have enough free
space on the disk because indexes may require a lot of space::

  $ ./import-mysql-indexes -d centreon -i ../data/centreonIndexes.json -s

.. note::

   **Indexes used by foreign keys cannot be synchronized.**

   ``-s`` or ``--sync`` options should be used in order to alter the
   database. If you need to specify the username and/or password you can use ``-u`` and
   ``-p`` options respectively.

InnoDB optimizations
====================

This section is not documented yet.