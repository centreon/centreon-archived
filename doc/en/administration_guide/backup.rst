######
Backup
######

============
How it works
============

Daily execution
===============

The backup script is executed on a daily basis with a cron job located in **/etc/cron.d/centreon**::

    ##########################
    # Cron for Centreon-Backup
    30 3 * * * root /usr/share/centreon/cron/centreon-backup.pl >> /var/log/centreon/centreon-backup.log 2&>1

Each day at 3:30 AM, backup script checks if backup is planned on current day.

Backup types
============

There are two types of backup : database and configuration files.

Database backup
---------------

Database backup can be processed on two databases : **centreon** and **centreon_storage**

There are two kinds of database backup:

* MySQLdump : mysqldump command is used to backup databases. Be careful, mysqldump can take long time on large databases.
* LVM Snapshot : Binary copy of MySQL files is done. You need to have a specific LV for MySQL (i.e. /var/lib/mysql) and 1GB of space in its VG.

Backup format :

* yyyy-mm-dd-centreon.sql.gz
* yyyy-mm-dd-centreon_storage.sql.gz

Configuration files backup
--------------------------

All configuration files of central server can be saved : MySQL, Zend, Apache, PHP, SNMP, centreon, centreon-broker)

Backup format :

* yyyy-mm-dd-Monitoring-Engine.tar.gz (centreon-engine configuration files)
* yyyy-mm-dd-Central.tar.gz (other configuration files)


=============
Configuration
=============

This part covers the configuration of centreon-backup.

#. Go into the menu: **Administration** ==> **Parameters** ==> **Backup**

The following window is displayed:

.. image:: /images/guide_exploitation/backup.png
:align: center

* **Backup enabled** Enable/Disable backup
* **Backup directory** Directory where backup will be stored
* **Temporary directory** Directory used during backup process
* **Backup database centreon** Enable backup on centreon database
* **Backup database centreon_storage** Enable backup on centreon_storage database
* **Backup type** Type of backup (MySQLdump or LVM snapshot)
* **Full backup** Period for full backup
* **Partial backup** Period for partial backup (only available with LVM snapshot backup)
* **Backup retention** Retention for backups (in days)
* **Backup configuration files** Enable backup of configuration files
* **MySQL configuration file path** Path for MySQL configuration file
* **Zend configuration file path** Path for Zend configuration file
* **SCP export enabled** Enable SCP export of backups
* **Remote user** Remote user for SCP export
* **Remote host** Remote host for SCP export
* **Remote directory** Remote directory for SCP export