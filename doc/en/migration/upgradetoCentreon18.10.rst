======================================
Migration from a Centreon 3.4 platform
======================================

*************
Prerequisites
*************

This procedure only applies to a Centreon 3.4 platform that is installed on a
64-bit GNU/Linux distribution and has the following prerequisites:

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
    on CentOS or Red Hat in version 7, refer to the :ref:`update<upgrade>`
    documentation.

*********
Migration
*********

.. warning::
    If your Centreon platform has a Centreon redundancy system, please contact
    your `Centreon support <https://support.centreon.com>`_.

Installing the new server
=========================

Install your new Centreon central server from the :ref:`ISO<installisoel7>` or from
:ref:`packages <install_from_packages>` and finish the install process to connect
to Centreon web interface.

.. note::
    It is better to set the same password for the 'centreon' user during the web
    installation process
 
Data synchronization
====================

Connect to your old Centreon server and synchronize following directories::

    # rsync -avz /etc/centreon root@IP_New_Centreon:/etc
    # rsync -avz /etc/centreon-broker root@IP_New_Centreon:/etc
    # rsync -avz /var/log/centreon-engine/archives/ root@IP_New_Centreon:/var/log/centreon-engine
    # rsync -avz --exclude centcore/ logs/ /var/lib/centreon root@IP_New_Centreon:/var/lib
    # rsync -avz /var/spool/centreon/.ssh root@IP_New_Centreon:/var/spool/centreon

.. note::
    Replace **IP_New_Centreon** by the IP or the new Centreon server.

If your DBMS is installed on the same server than Centreon central, execute the
following commands:

#. Stop **mysqld** on both Centreon server: ::

    # service mysql stop

#. Synchronize data: ::

    # rsync -avz /var/lib/mysql/ root@IP_New_Centreon:/var/lib/mysql/

#. Start mysqld process on new server: ::

    # systemctl start mysqld

Plugins synchronization
=======================

The synchronization of monitoring plugins is more complex and depends on your
installation. The main directories to synchronize are:

#. /usr/lib/nagios/plugins/
#. /usr/lib/centreon/plugins/

.. note::
    It is mandatory to install needed dependencies to run the plugins.

Upgrade Centreon
================

Go to http://[ADRESSE_IP_DE_VOTRE_SERVEUR]/centreon url and perform the
process of upgrade.

.. note::
    If you changed the 'centreon' password during the installation process
    you must execute the following steps:
    
    #. Edit /etc/centreon/centreon.conf.php file
    #. Edit /etc/centreon/conf.pm file
    #. Edit the Centreon Broker central configuration, using Centreon web
	   interface and change the password for **Perfdata generator** and
	   **Broker SQL database** output.

If the IP of your Centreon server changed, edit all the Centreon Broker module
of all your pollers and change the IP to connect to the Centreon central
(output IPv4).

Then :ref:`generate <deployconfiguration>` the configuration of all your pollers
and export it.

Modules upgrade
===============

Please refer to the documentation of each modules to verify the compatibility
with Centreon 18.10 and to perform upgrade.
