=============
Prerequisites
=============

The Centreon web interface is compatible with the following list of web broswer:

* Chrome (latest version)
* Firefox (latest version)
* Internet Explorer IE 11 (latest version)
* Safari (latest version)

Your screen resolution must be at least 1280 x 768.

*********
Softwares
*********

Operating System
================

If you **use CES v3.x the operating system will be CentOS v6**. If you prefer to use
**Red Hat OS** you must install it in **v6 version**. Else you can use another GNU/Linux
operating system but installation will be more complex and realised using software
sources.

DBMS
====

**Centreon advises you to use MariaDB** instead of MySQL.

+----------+-----------+
| Software | Version   |
+==========+===========+
| MariaDB  | >= 5.5.48 |
+----------+-----------+
| MySQL    | >= 5.6.x  |
+----------+-----------+

Dependent software
==================

The following table describes the dependent software:

+----------+------------------+
| Logiciel | Version          |
+==========+==================+
| Apache   | 2.2 & 2.4        |
+----------+------------------+
| GnuTLS   | >= 2.0           |
+----------+------------------+
| Net-SNMP | 5.5              |
+----------+------------------+
| openssl  | >= 1.0.1e        |
+----------+------------------+
| PHP      | >= 5.3.0 & < 5.5 |
+----------+------------------+
| Qt       | >= 4.7.4         |
+----------+------------------+
| RRDtools | 1.4.7            |
+----------+------------------+
| zlib     | 1.2.3            |
+----------+------------------+
***************************
Select type of architecture
***************************

The table below gives the prerequisites for the installation of CES 3.x:

+----------------------+-----------------------------+--------------------------+----------------+---------------+
|  Number of Services  |  Estimated number of hosts  |  Number of pollers       |  Cenral        |  Poller       |
+======================+=============================+==========================+================+===============+
|           < 500      |             50              |        1 central         |  1 vCPU / 1 GB |               |
+----------------------+-----------------------------+--------------------------+----------------+---------------+
|       500 - 2000     |           50 - 200          |        1 central         |  2 vCPU / 2 GB |               |
+----------------------+-----------------------------+--------------------------+----------------+---------------+
|      2000 - 10000    |          200 - 1000         |  1 central + 1 poller    |  4 vCPU / 4 GB | 1 vCPU / 2 GB |
+----------------------+-----------------------------+--------------------------+----------------+---------------+
|     10000 - 20000    |         1000 - 2000         |  1 central + 1 poller    |  4 vCPU / 8 GB | 2 vCPU / 2 GB |
+----------------------+-----------------------------+--------------------------+----------------+---------------+
|     20000 - 50000    |         2000 - 5000         |  1 central + 2 pollers   |  4 vCPU / 8 GB | 4 vCPU / 2 GB |
+----------------------+-----------------------------+--------------------------+----------------+---------------+
|     50000 - 100000   |         5000 - 10000        |  1 central + 3 pollers   |  4 vCPU / 8 GB | 4 vCPU / 2 GB |
+----------------------+-----------------------------+--------------------------+----------------+---------------+

.. note::
    vCPU must have a frequency arround 3 GHz

*****************
Define space disk
*****************

The space used for store collected and performance data depends on several criteria:

* Frequency of controls
* Number of controls
* Retention time

The following table provides an idea of the disk space needed for your platform with:

* Data are collected every 5 minutes
* The retention period is 6 month
* Each performance graph have 2 curves

+------------------------+----------------+-------------------+
|  Number of Services    | /var/lib/mysql | /var/lib/centreon |
+========================+================+===================+
|        < 500           |     10 GB      |      2.5 GB       |
+------------------------+----------------+-------------------+
|       500 - 2000       |     42 GB      |       10 GB       |
+------------------------+----------------+-------------------+
|      2000 - 10000      |    210 GB      |       50 GB       |
+------------------------+----------------+-------------------+
|      10000 - 20000     |    420 GB      |      100 GB       |
+------------------------+----------------+-------------------+
|      20000 - 50000     |    1.1 TB      |      250 GB       |
+------------------------+----------------+-------------------+
|     50000 - 100000     |      2,3 TB    |        1 TB       |
+------------------------+----------------+-------------------+

*******************
Define files system
*******************

.. note::
    Your system must use LVM to manage files system.

Centreon server
===============

Files system description:

* / (at least 20 GB)
* swap (at least 1x RAM space)
* /var/log (at least 10 GB)
* /var/lib/centreon (define in previous chapter)
* /var/lib/centreon-broker (at least 5 GB)
* /var/cache/centreon/backup (use to backup you server)

MariaDB DBMS
============

Files system description:

* / (at least 10 GB)
* swap (at least 1x RAM space)
* /var/log (at least 10 GB)
* /var/lib/mysql (define in previous chapter)
* /var/cache/centreon/backup (use to backup you server)

Monitoring poller
=================

Files system description:

* / (at least 20 GB)
* swap (at least 1x RAM space)
* /var/log (at least 10 GB)
* /var/lib/centreon-broker (at least 5 GB)
* /var/cache/centreon/backup (use to backup you server)
