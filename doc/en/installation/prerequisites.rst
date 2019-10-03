=============
Prerequisites
=============

The Centreon web interface is compatible with the following web browsers:

* Chrome (latest version)
* Firefox (latest version)
* Internet Explorer IE 11 (latest version)
* Safari (latest version)

Your screen resolution must be at least 1280 x 768.

********
Software
********

Operating Systems
=================

Centreon offers an ISO file including CentOS v7 and all the required packages.

If you prefer to use **Red Hat OS** you must install **version v7**
and use the RPMs available from our repositories.

Open Source users, without Support contract, can use another GNU/Linux operating system.
This will require installing the platform from source files and therefore be more complex.

.. note::
    Only 64-bit operating systems (x86_64) are supported.

DBMS
====

*Centreon advises you to use MariaDB instead of MySQL.*

+----------+------------+
| Software | Version    |
+==========+============+
| MariaDB  | = 10.2.x   |
+----------+------------+
| MySQL    | = 5.6.x    |
+----------+------------+

Software dependencies
=====================

The following table describes the software dependencies:

+----------+------------------+
| Software | Version          |
+==========+==================+
| Apache   | 2.4              |
+----------+------------------+
| GnuTLS   | >= 2.0           |
+----------+------------------+
| Net-SNMP | 5.7              |
+----------+------------------+
| openssl  | >= 1.0.1k        |
+----------+------------------+
| PHP      | 7.2              |
+----------+------------------+
| Qt       | >= 4.7.4         |
+----------+------------------+
| RRDtools | 1.4.7            |
+----------+------------------+
| zlib     | 1.2.3            |
+----------+------------------+

****************************
Centreon Server Requirements
****************************

.. note::
    Centreon offers a :download:`workbook</files/Centreon_platform_sizing.xlsx>`
    to calculate the size of your platform.

The table below describes requirements for installing Centreon:

+----------------------+-----------------------------+--------------------------+----------------+---------------+
|  Number of Services  |  Estimated number of hosts  |  Number of pollers       |  Central       |  Poller       |
+======================+=============================+==========================+================+===============+
|           < 500      |             50              |        1 central         |  1 vCPU / 1 GB |               |
+----------------------+-----------------------------+--------------------------+----------------+---------------+
|       500 - 2000     |           50 - 200          |        1 central         |  2 vCPU / 2 GB |               |
+----------------------+-----------------------------+--------------------------+----------------+---------------+
|      2000 - 7000     |          200 - 700          |  1 central + 1 poller    |  4 vCPU / 4 GB | 1 vCPU / 4 GB |
+----------------------+-----------------------------+--------------------------+----------------+---------------+
|      7000 - 14000    |          700 - 1400         |  1 central + 1 poller    |  4 vCPU / 8 GB | 2 vCPU / 4 GB |
+----------------------+-----------------------------+--------------------------+----------------+---------------+
|     14000 - 21000    |         1400 - 2100         |  1 central + 2 pollers   |  4 vCPU / 8 GB | 2 vCPU / 4 GB |
+----------------------+-----------------------------+--------------------------+----------------+---------------+
|     21000 - 28000    |         2100 - 2800         |  1 central + 3 pollers   |  4 vCPU / 8 GB | 2 vCPU / 4 GB |
+----------------------+-----------------------------+--------------------------+----------------+---------------+
|         ...          |             ...             |            ...           |       ...      |       ...     |
+----------------------+-----------------------------+--------------------------+----------------+---------------+

.. note::
    A poller can monitor around 7000 active services.
    vCPU must have a frequency of approximately 3 GHz. The number of vCPU depends of the
    complexity of checks. If you use connectors or perform a large number of
    system/third-party binary calls, you should add more vCPU.

.. _diskspace:

*****************
Define disk space
*****************

.. note::
    Centreon offers a :download:`workbook</files/Centreon_platform_sizing.xlsx>`
    to calculate the size of your platform.

The space used to store collected performance data depends on several criteria:

* Frequency of controls
* Number of controls
* Retention time

The following table provides an estimate of disk space required for your platform assuming:

* Data is collected every 5 minutes.
* The retention period is 6 months.
* Each performance graph has 2 curves.

+--------------------+------------------------+---------------------------+
| Number of Services | /var/lib/mysql (in GB) | /var/lib/centreon (in GB) |
+====================+========================+===========================+
| 500                | 10                     | 2.5                       |
+--------------------+------------------------+---------------------------+
| 2000               | 42                     | 10                        |
+--------------------+------------------------+---------------------------+
| 10 000             | 93                     | 27                        |
+--------------------+------------------------+---------------------------+
| 20 000             | 186                    | 54                        |
+--------------------+------------------------+---------------------------+
| 50 000             | 465                    | 135                       |
+--------------------+------------------------+---------------------------+
| 100 000            | 930                    | 270                       |
+--------------------+------------------------+---------------------------+
| ...                | ...                    | ...                       |
+--------------------+------------------------+---------------------------+

***********************
Define the file system
***********************

.. note::
    Your system must use LVM to manage the file system.

Centreon server
===============

Files system description:

+----------------------------+------------------------------------------------------------------------------------------------------------+
| File system                | Size                                                                                                       |
+============================+============================================================================================================+
| swap                       | 1 to 1.5 total size of RAM space                                                                           |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /                          | at least 20 GB                                                                                             |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /var/log                   | at least 10 GB                                                                                             |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /var/lib/centreon          | :ref:`defined in a previous chapter <diskspace>`                                                           |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /var/lib/centreon-broker   | at least 5 GB                                                                                              |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /var/cache/centreon/backup | at least 10 GB (export the backups and delete the exported data daily)                                     |
+----------------------------+------------------------------------------------------------------------------------------------------------+

MariaDB DBMS
============

.. note::
    At least 1 GB of non-allocated free space must be available for the **volume group**
    containing **/var/lib/mysql**, if you wish to use **snapshot LVM** as a
    backup method.

Files system description:

+----------------------------+------------------------------------------------------------------------------------------------------------+
| File system                | Size                                                                                                       |
+============================+============================================================================================================+
| swap                       | 1 to 1.5 total size of RAM space                                                                           |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /                          | at least 20 GB                                                                                             |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /var/log                   | at least 10 GB                                                                                             |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /var/lib/mysql             | :ref:`defined in a previous chapter <diskspace>`                                                           |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /var/cache/centreon/backup | at least 10 Go (export the backups and delete the exported data daily)                                     |
+----------------------------+------------------------------------------------------------------------------------------------------------+

Monitoring poller
=================

Files system description:

+----------------------------+------------------------------------------------------------------------------------------------------------+
| File system                | Size                                                                                                       |
+============================+============================================================================================================+
| swap                       | 1 to 1.5 total size of RAM space                                                                           |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /                          | at least 20 GB                                                                                             |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /var/log                   | at least 10 GB                                                                                             |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /var/lib/centreon-broker   | at least 5 GB                                                                                              |
+----------------------------+------------------------------------------------------------------------------------------------------------+
| /var/cache/centreon/backup | at least 5 Go (export the backups and delete the exported data daily)                                      |
+----------------------------+------------------------------------------------------------------------------------------------------------+

****************
Users and groups
****************

.. note::
    This information pertains to the Red Hat / CentOS system.
    Names of users, groups and services can change according to the GNU/Linux distribution.

Description of software and linked users:

+-----------------+------------------+-----------------+-----------------------+
| Software        | Service          | User            | Comment               |
+=================+==================+=================+=======================+
| Apache          | httpd24-httpd    | apache          | automatic start       |
+-----------------+------------------+-----------------+-----------------------+
| PHP-FPM         | rh-php72-php-fpm | apache          | automatic start       |
+-----------------+------------------+-----------------+-----------------------+
| MySQL (MariaDB) | mysqld (mysql)   | mysql           | automatic start       |
+-----------------+------------------+-----------------+-----------------------+
| Centreon        | centcore         | centreon        | automatic start       |
+-----------------+------------------+-----------------+-----------------------+
| Centreon        | centreontrapd    | centreon        | automatic start       |
+-----------------+------------------+-----------------+-----------------------+
| Centreon Broker | cbwd             | centreon-broker | automatic start       |
+-----------------+------------------+-----------------+-----------------------+
| Centreon Broker | cbd              | centreon-broker | automatic start       |
+-----------------+------------------+-----------------+-----------------------+
| Centreon Engine | centengine       | centreon-engine | automatic start       |
+-----------------+------------------+-----------------+-----------------------+

Description of optional software and linked users:

+-----------------+-----------------+-----------------+------------------------------------------------------+
| Software        | Service         | User            | Comment                                              |
+=================+=================+=================+======================================================+
| Centreon VMware | centreon_vmware | centreon        | not installed by default                             |
+-----------------+-----------------+-----------------+------------------------------------------------------+
| RRDtool         | rrdcached       | rrdcached       | not enabled and not defined in Centreon by default   |
+-----------------+-----------------+-----------------+------------------------------------------------------+

Description of groups and linked users:

+-----------------+----------------------------------------+
| Group           | Users                                  |
+=================+========================================+
| apache          | nagios,centreon                        |
+-----------------+----------------------------------------+
| centreon        | centreon-engine,centreon-broker,apache |
+-----------------+----------------------------------------+
| centreon-broker | centreon,nagios,centreon-engine,apache |
+-----------------+----------------------------------------+
| centreon-engine | centreon-broker,apache,nagios,centreon |
+-----------------+----------------------------------------+

Description of users, umask and home directory:

+-----------------+-------+--------------------------+
| User            | umask | home                     |
+=================+=======+==========================+
| root            | 0022  | /root                    |
+-----------------+-------+--------------------------+
| apache          | 0022  | /var/www                 |
+-----------------+-------+--------------------------+
| centreon        | 0002  | /var/spool/centreon      |
+-----------------+-------+--------------------------+
| centreon-broker | 0002  | /var/lib/centreon-broker |
+-----------------+-------+--------------------------+
| centreon-engine | 0002  | /var/lib/centreon-engine |
+-----------------+-------+--------------------------+
| mysql           | 0002  | /var/lib/mysql           |
+-----------------+-------+--------------------------+
