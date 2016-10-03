.. _Centreon-Partitioning:

#######################
Databases partitionning
#######################

========
Overview
========

Centreon Partioning module is integrated to Centreon Web, features and advantages are:

- It allows you to partition MySQL table according to data date. Giving optimization of request execution time.
- Data purge is improved, it's now just needed to delete old partitions.
- Extent of Mysql crash are limited. Only needed to rebuild concerned partitions.
- Existent partitions can be partitionned

.. note::

   There are some limitations:
   - Maximum number of partitions (for a MySQL table) is 1024
   - Foreign keys are not supported

Since Centreon Web 2.8.0 version, tables logs, data_bin, log_archive_host and log_archive_service are partioned during installation.

More details about MySQL partitioning `here
<http://dev.mysql.com/doc/refman/5.5/en/partitioning.html>`_.


=============
Prerequisites
=============

The following packages are required:

* php-mysql
* Pear-DB
* MySQL (>= 5.1.x)

MySQL open_files_limit parameter must be set to 32000 in [server] section :

::

  [server]
  open_files_limit = 32000


Contents:

.. toctree::
   :maxdepth: 2

   prerequisite/index
   user/index

