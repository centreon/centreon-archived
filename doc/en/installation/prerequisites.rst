=============
Prerequisites
=============

***************************
Select type of architecture
***************************

The table below gives the prerequisites for the installation of CES 3.2:

+------------------------+--------------------------+----------------+---------------+
|  Number of Services    |  Number of pollers       | Central        | Poller        |
+========================+==========================+================+===============+
|        < 500           |        1 central         |  1 vCPU / 1 GB |               |
+------------------------+--------------------------+----------------+---------------+
|       500 - 2000       |        1 central         |  2 vCPU / 2 GB |               |
+------------------------+--------------------------+----------------+---------------+
|      2000 - 10000      | 1 central + 1 poller     |  4 vCPU / 4 GB | 1 vCPU / 2 GB |
+------------------------+--------------------------+----------------+---------------+
|      10000 - 20000     |  1 central + 1 poller    |  4 vCPU / 8 GB | 2 vCPU / 2 GB |
+------------------------+--------------------------+----------------+---------------+
|      20000 - 50000     | 1 central + 2 pollers    |  4 vCPU / 8 GB | 4 vCPU / 2 GB |
+------------------------+--------------------------+----------------+---------------+
|     50000 - 100000     | 1 central + 3 pollers    |  4 vCPU / 8 GB | 4 vCPU / 2 GB |
+------------------------+--------------------------+----------------+---------------+

*****************
Define space disk
*****************

Th space used for store collected and performance data depends on several criteria:

* Frequency of controls
* Number of controls
* Retention time

.. warning::
    The following table provides an idea of the disk space need by monitoring servers based on that the data is collected every 5 minutes for a 6 month retention period.

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
Define fyles system
*******************

.. note::
    Your system must use LVM to manage files system.

Centreon server
===============

Files system descrption:

* / (at least 20 GB)
* swap (at least 1x RAM space)
* /var/log (at least 10 GB)
* /var/lib/centreon (define in previous chapter)
* /var/lib/centreon-broker (at least 5 GB)
* /var/backup (use to backup you server)

MariaDB DBMS
============

Files system descrption:

* / (at least 10 GB)
* swap (at least 1x RAM space)
* /var/log (at least 10 GB)
* /var/lib/mysql (define in previous chapter)
* /var/backup (use to backup you server)

Monitoring poller
=================

Files system descrption:

* / (at least 20 GB)
* swap (at least 1x RAM space)
* /var/log ( at least 10 GB)
* /var/lib/centreon-broker (at least 5 GB)
* /var/backup (use to backup you server)

