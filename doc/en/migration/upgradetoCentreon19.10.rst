.. _migrate_to_1810:

======================================
Migrating from a Centreon 3.4 platform
======================================

*************
Prerequisites
*************

The following procedure only applies to migration from a Centreon 3.4 platform
installed on a 64-bit GNU/Linux distribution other than CentOS or Red Hat 7.
Here are the system requirements:

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
    If your Centreon platform includes a Centreon redundancy system, please
    contact `Centreon support <https://centreon.force.com>`_.

.. warning::
    If you try to migrate a platform using the **Centreon Poller Display 1.6.x**,
    please refer to the following :ref:`migration procedure <migratefrompollerdisplay>`.

Installing the new server
=========================

Perform the following actions:

#. You will need to install a new Centreon central server from the :ref:`ISO<installisoel7>` or from :ref:`packages <install_from_packages>`, until to complete the installation process by connecting to the Centreon Web interface.
#. Perform software and system updates: ::

    # yum update

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

.. warning::
    In case of migration from CES 3.4.x, Centreon-web 2.8.x under CentOS 6 with MariaDB 5.X, do not sync folders
    /var/lib/mysql with RSYNC toward Centreon 19.10 / MariaDB 10.2.
    
    #. Dump source databases: ::
    
        # mysqldump -u root -p centreon > /tmp/export/centreon.sql
        # mysqldump -u root -p centreon_storage > /tmp/export/centreon_storage.sql
      
    #. Stop source MariaDB servers: ::
    
        # service mysqld stop
    
    #. Export the dumps to the new Centreon 19.10 database server (make sure you have enough space for large databases dumps): ::
    
        # rsync -avz /tmp/centreon.sql root@IP_New_Centreon:/tmp/
        # rsync -avz /tmp/centreon_storage.sql root@IP_New_Centreon:/tmp/
        
    #. On the Centreon 19.10 database server, drop the original databases and create them again: ::
    
        # mysql -u root -p
        # drop database centreon;
        # drop database centreon_storage;
        # create database centreon;
        # create database centreon_storage;
        
    #. Import the previously transfered dumps: ::
    
        # mysql -u root centreon -p </tmp/centreon.sql
        # mysql -u root centreon_storage -p </tmp/centreon_storage.sql
        
    #. Upgrade the tables: ::
    
        # mysql_upgrade
        
    #. Keep going with the migration
    
    If your DBMS is installed on the same server as the Centreon central server, execute the following commands:

#. Stop **mysqld** on both Centreon servers: ::

    # service mysqld stop

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

.. note::
    If you still have distant centreon-engine 1.8.1 pollers that you want to
    postpone the upgrade to v19.10, be aware that centreon-web 19.10 resource
    $USER1$ actually points to /usr/lib64/nagios/plugins
    
    On the 1.8.1 pollers to mitigate the issue: ::
    
        # mv /usr/lib64/nagios/plugins/* /usr/lib/nagios/plugins/
        # rmdir /usr/lib64/nagios/plugins/
        # ln -s -t /usr/lib64/nagios/ /usr/lib/nagios/plugins/
    
    You now have a symbolic link as: ::
    
        # ls -alt /usr/lib64/nagios/
        lrwxrwxrwx   1 root root      24  1 nov.  17:59 plugins -> /usr/lib/nagios/plugins/
        -rwxr-xr-x   1 root root 1711288  6 avril  2018 cbmod.so
    
    You can now push poller configuration from Centreon 19.10 whether the distant poller is centreon-engine 19.10 or 1.8.1
    
Upgrading Centreon
==================

On the new server, force the update by moving the contents of the
**/usr/share/centreon/installDir/install-19.10.0-YYYYMMDD_HHMMSS** directory to
the **/usr/share/centreon/www/install** directory: ::

    # cd /usr/share/centreon/installDir/
    # mv install-19.10.0-YYYYMMDD_HHMMSS/ ../www/install/

.. note::
    If you use the same IP address or same DNS name between old Centreon webserver and the new one, do a full cache cleanup of your browser to avoid JS issues
 
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
with Centreon 19.10 and perform the upgrade.
