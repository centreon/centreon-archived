.. _Centreon-Partitioning:

######################
Databases partitioning
######################

========
Overview
========

Centreon Partitioning module is integrated to Centreon Web, features and advantages are:

- It allows you to partition MariaDB table according to data date. Giving optimization of request execution time.
- Data purge is improved, it's now just needed to delete old partitions.
- Extent of MariaDB crash are limited. Only needed to rebuild concerned partitions.
- Existent partitions can be partitioned

.. note::

   There are some limitations:
   - Maximum number of partitions (for a MariaDB table) is 1024
   - Foreign keys are not supported

Since Centreon Web 2.8.0 version, tables logs, data_bin, log_archive_host and log_archive_service are partitioned during installation.

More details about MariaDB partitioning `here
<https://mariadb.com/kb/en/library/partitioning-overview/>`_.


=============
Prerequisites
=============

The following packages are required:

* php-mysql
* Pear-DB
* MariaDB (>= 10.1)

MariaDB open_files_limit parameter must be set to 32000 in [server] section :

::

  [server]
  open_files_limit = 32000

.. note::
    If you install Centreon via the dedicated ISO, this parameter is already configured. If you do it on your RedHat or CentOS Linux version, you will be able to do it manually.
    Don't forget to restart mariadb processes if you change this value in my.cnf.


If you use systemd, you need to create file "/etc/systemd/system/mariadb.service.d/mariadb.conf" :

::

  [Service]
  LimitNOFILE=32000

Then reload systemd and MariaDB :

::

  $ systemctl daemon-reload
  $ systemctl restart mariadb

Contents:

.. toctree::
   :maxdepth: 2

   user/index
