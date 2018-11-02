.. _migrate_to_1810:

======================================
Migrating from a Centreon 3.4 platform
======================================

*************
Prerequisites
*************

This procedure, which only applies to a Centreon 3.4 platform installed on a
64-bit GNU/Linux distribution, has the following prerequisites:

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
    If your platform has been installed from Centreon ISO or Centreon 3.4 repositories
    on CentOS or Red Hat version 7, refer to the :ref:`update<upgrade>`
    documentation.

.. warning::
    If you try to migrate a platform using **Centreon Poller Display 1.6.x**,
    please refer to the following :ref:`migration procedure <migratefrompollerdisplay>`.

*********
Migrating
*********

.. warning::
    If your Centreon platform has a Centreon redundancy system, please contact
    your `Centreon support <https://support.centreon.com>`_.

Installing the new server
=========================

Install your new Centreon central server from the :ref:`ISO<installisoel7>` or from
:ref:`packages <install_from_packages>` and finish the installation process by connecting
to the Centreon web interface.

.. note::
    It is preferable to set the same password for the 'centreon' user during the web
    installation process.
 
Synchronizing the data
======================

Connect to your old Centreon server and synchronize following directories::

    # rsync -avz /etc/centreon root@IP_New_Centreon:/etc
    # rsync -avz /etc/centreon-broker root@IP_New_Centreon:/etc
    # rsync -avz /var/log/centreon-engine/archives/ root@IP_New_Centreon:/var/log/centreon-engine
    # rsync -avz --exclude centcore/ logs/ /var/lib/centreon root@IP_New_Centreon:/var/lib
    # rsync -avz /var/spool/centreon/.ssh root@IP_New_Centreon:/var/spool/centreon

.. note::
    Replace **IP_New_Centreon** by the IP or the new Centreon server.

If your DBMS is installed on the same server as the Centreon central server, execute the
following commands:

#. Stop **mysqld** on both Centreon servers: ::

    # service mysql stop

#. Synchronize data: ::

    # rsync -avz /var/lib/mysql/ root@IP_New_Centreon:/var/lib/mysql/

#. If you migrate your DMBS from 5.x to 10.x, it's necessary to execute this command on the new server : ::

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
    It is mandatory to install the required dependencies to run the plugins.

Upgrading Centreon
==================

On the new server, force the update by moving the content of the
**/usr/share/centreon/installDir/install-18.10.0-YYYYMMDD_HHMMSS** directory to
the **/usr/share/centreon/www/install** directory: ::

    # cd /usr/share/centreon/installDir/
    # mv install-18.10.0-YYYYMMDD_HHMMSS/ ../www/install/

Go to http://[New_Centreon_IP]/centreon URL and perform the
upgrade.

.. note::
    If you changed the 'centreon' password during the installation process
    you must follow these steps:
    
    #. Edit /etc/centreon/centreon.conf.php file
    #. Edit /etc/centreon/conf.pm file
    #. Edit the Centreon Broker central configuration, using Centreon web interface and change the password for **Perfdata generator** and **Broker SQL database** output.

If the IP of your Centreon server has changed, edit configuration for all the Centreon Broker modules
on your pollers and change the IP to connect to the Centreon central server
(output IPv4).

Then :ref:`generate <deployconfiguration>` the configuration of all your pollers
and export it.

Upgrading the modules
=====================

Please refer to the documentation of each module both to verify compatibility
with Centreon 18.10 and perform the upgrade.
