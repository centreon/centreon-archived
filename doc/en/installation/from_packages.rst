.. _install_from_packages:

==============
Using packages
==============

Centreon provides RPM for its products through the Centreon Open Sources version available free of charge on our repository (ex CES).

These packages have been successfully tested on CentOS and Red Hat environments 7.x version.

*****************
Pre-install steps
*****************

SELinux should be disabled. In order to do this, you have to edit the file */etc/selinux/config* and replace "enforcing" by "disabled":

::

    SELINUX=disabled

.. note::
    After saving the file, please reboot your operating system to apply the changes.

A quick check of SELinux status:

::

    $ getenforce
    Disabled


***********************
Repository installation
***********************

Redhat Software collections repository
--------------------------------------

To install Centreon you will need to set up the official software collections repository supported by Redhat.

.. note::
    Software collections are required in order to install php 7 and associated libs (Centreon requirement)

To do so, perform the following command with an user granted with sufficient rights.

Software collections repository installation.

::

   $ yum install centos-release-scl


The repository is now installed.

Centreon repository
-------------------

To install Centreon software from the repository, you should first install centreon-release package
which will provide the repository file.

Perform the following command with an user granted with sufficient rights.

Centreon repository installation.

::

   $ wget http://yum.centreon.com/standard/18.9/el7/stable/noarch/RPMS/centreon-release-18.9.el7.centos.noarch.rpm
   $ yum install --nogpgcheck centreon-release-18.9.el7.centos.noarch.rpm


The repository is now installed.


************************************
Installing a Centreon central server
************************************

This chapter describes the installation of a Centreon central server.

Perform the command:

::

  $ yum install centreon-base-config-centreon-engine centreon

Installing MySQL on the Centreon central server
-----------------------------------------------

This chapter describes the installation of MySQL on a server including Centreon.

Perform the command:

::

   $ yum install MariaDB-server
   $ systemctl restart mysql

Database management system
--------------------------

The MySQL database server should be available to complete installation (locally or not). MariaDB is recommended.

It is necessary to modify **LimitNOFILE** limitation.
Setting this option into /etc/my.cnf will NOT work.

Perform instead:

::

   # mkdir -p  /etc/systemd/system/mariadb.service.d/
   # echo -ne "[Service]\nLimitNOFILE=32000\n" | tee /etc/systemd/system/mariadb.service.d/limits.conf
   # systemctl daemon-reload
   # systemctl restart mysql

PHP timezone
------------

PHP timezone needs to be set; to do so go to /etc/opt/rh/rh-php71/php.d directory and create a file named php-timezone.ini which contains the timezone of the Centreon central server. For example :
::

    date.timezone = Europe/Paris

After saving the file, please do not forget to restart apache server.

::

    systemctl restart httpd

Firewall
--------

Add firewall rules or disable it. To disable it execute following commands:

**firewalld** ::

    # systemctl stop firewalld
    # systemctl disable firewalld

Launch services during the system bootup
----------------------------------------

To make services automatically start during system bootup perform these commands on the central server ::

    # systemctl enable httpd.service
    # systemctl enable snmpd.service
    # systemctl enable mysql.service
    # systemctl enable rh-php71-php-fpm

.. note::
    If MySQL database is on a dedicated server, execute the enable command of mysql on the database server.

Conclude installation
---------------------

Before starting the web installation process you will need to execute ::

    # systemctl start rh-php71-php-fpm

Click :ref:`here <installation_web_ces>` to finalise the installation process.


*******************
Installing a poller
*******************

This chapter describes the installation of a collector.

Perform the command:

::

  $ yum install centreon-poller-centreon-engine

The communication between a central server and a poller server is by SSH.

You should exchange the SSH keys between the servers.

If you don’t have any private SSH keys on the central server for the Centreon user:

::

    $ su - centreon
    $ ssh-keygen -t rsa

Copy this key on the collector:

::

    $ ssh-copy-id centreon@your_poller_ip
