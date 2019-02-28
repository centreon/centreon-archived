.. _migrate_to_1810:

======================================
Migrating from a Centreon 3.4 platform
======================================

*************
Prerequisites
*************

The following precedure only applies to migration from a Centreon 3.4 platform installed on a
64-bit GNU/Linux distribution. Here are the system requirements:

+-----------------+---------+
| Components      | Version |
+=================+=========+
| Centreon Web    | 2.8.x   |
+-----------------+---------+
| Centreon Broker | 3.0.x   |
+-----------------+---------+
| Centreon Engine | 1.8.x   |
+-----------------+---------+

.. note::
    If your platform was installed from Centreon ISO or Centreon 3.4 repositories
    running on CentOS or Red Hat version 7, refer to the :ref:`update<upgrade>`
    documentation.

*********
Migrating
*********

.. warning::
    If your Centreon platform includes a Centreon redundancy system, please contact `Centreon support <https://support.centreon.com>`_.

.. warning::
    If you try to migrate a platform using the **Centreon Poller Display 1.6.x**,
    please refer to the following :ref:`migration procedure <migratefrompollerdisplay>`.

Installing the new server
=========================

Install your new Centreon central server from the :ref:`ISO<installisoel7>` or from
:ref:`packages <install_from_packages>`, and complete the installation process by connecting
to the Centreon Web interface.

.. note::
    It is advisable to set the same password for the *centreon* user during the web
    installation process.
 
Synchronizing the data
======================

Connect to your old Centreon server and synchronize following directories::

    # rsync -avz /etc/centreon root@IP_New_Centreon:/etc
    # rsync -avz /etc/centreon-broker root@IP_New_Centreon:/etc
    # rsync -avz /var/log/centreon-engine/archives/ root@IP_New_Centreon:/var/log/centreon-engine
    # rsync -avz --exclude centcore/ --exclude log/ /var/lib/centreon root@IP_New_Centreon:/var/lib
    # rsync -avz /var/spool/centreon/.ssh root@IP_New_Centreon:/var/spool/centreon

.. note::
    Replace **IP_New_Centreon** by the IP or the new Centreon server.

If your DBMS is installed on the same server as the Centreon central server, run the
following commands:

#. Stop **mysqld** on both Centreon servers: ::

    # systemctl stop mysqld

#. On the new server, remove data in /var/lib/mysql/: ::

    # rm -Rf /var/lib/mysql/*

#. On the old server, synchronize data: ::

    # rsync -avz /var/lib/mysql/ root@IP_New_Centreon:/var/lib/mysql/

#. If you migrate your DMBS from 5.x to 10.x, you must run this command on the new server: ::

    # mysql_upgrade

#. Start the mysqld process on the new server: ::

    # systemctl start mysqld

Synchronizing the plugins
=========================

Synchronizing the monitoring plugins is more complex and depends on your
installation. The main directories to synchronize are:

#. /usr/lib/nagios/plugins/
#. /usr/lib/centreon/plugins/

.. note::
    To run the plugins, you must first install the required dependencies.

Upgrading Centreon
==================

On the new server, force the update by moving the contents of the
**/usr/share/centreon/installDir/install-18.10.0-YYYYMMDD_HHMMSS** directory to
the **/usr/share/centreon/www/install** directory: ::

    # cd /usr/share/centreon/installDir/
    # mv install-18.10.0-YYYYMMDD_HHMMSS/ ../www/install/

Go to http://[New_Centreon_IP]/centreon URL and perform the
upgrade.

.. note::
    If you changed the *centreon* password during the installation process
    you must follow these steps:
    
    #. Edit the /etc/centreon/centreon.conf.php file.
    #. Edit the /etc/centreon/conf.pm file.
    #. Edit the Centreon Broker central configuration using Centreon web
       interface and change the password for the **Perfdata generator** and
       **Broker SQL database** output.

If the IP of your Centreon server has changed, edit the configuration for all the Centreon Broker modules
on your pollers and change the IP to connect to the Centreon central server
(output IPv4).

Then :ref:`generate <deployconfiguration>` the configuration of all your pollers
and export it.

Upgrading the modules
=====================

Please refer to the documentation of each module to verify compatibility
with Centreon 18.10 and perform the upgrade.
