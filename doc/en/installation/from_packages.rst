.. _install_from_packages:

==============
Using packages
==============

Centreon supplies RPM for its products via the Centreon Open Sources version available free of charge on our repository (ex CES).

These packages have been successfully tested on CentOS and Red Hat environments in 6.x and 7.x versions.

*****************
Pre-install steps
*****************

SELinux should be disabled; for this, you have to modify the file */etc/selinux/config* and replace "enforcing" by "disabled":

::

    SELINUX=disabled

.. note::
    After saving the file, please reboot your operating system to apply the changes.

******************
Repository install
******************

To install Centreon software from the repository, you should first install the file linked to the repository.

Perform the following command from a user with sufficient rights.

For CentOS 6.

::

   $ wget http://yum.centreon.com/standard/3.4/el6/stable/noarch/RPMS/centreon-release-3.4-4.el6.noarch.rpm
   $ yum install --nogpgcheck centreon-release-3.4-4.el6.noarch.rpm


For CentOS 7.

::

   $ wget http://yum.centreon.com/standard/3.4/el7/stable/noarch/RPMS/centreon-release-3.4-4.el7.centos.noarch.rpm
   $ yum install --nogpgcheck centreon-release-3.4-4.el7.centos.noarch.rpm


The repository is now installed.


************************
Install a central server
************************

The chapter describes the installation of a Centreon central server.

Perform the command:

::

  $ yum install centreon-base-config-centreon-engine centreon


Installing MySQL on the same server
-----------------------------------

This chapter describes the installation of MySQL on a server including Centreon.

Perform the command:

::

   $ yum install MariaDB-server
   $ service mysql restart


PHP timezone
------------

PHP timezone should be set; go to /etc/php.d directory and create a file named php-timezone.ini which contains the following line :

::

    date.timezone = Europe/Paris

After saving the file, please don't forget to restart apache server.

Firewall
--------

Add firewall rules or disable it. To disable it execute following commands:

* **iptables** (CentOS v6) ::

    # /etc/init.d/iptables save
    # /etc/init.d/iptables stop
    # chkconfig iptables off

* **firewalld** (CentOS v7) ::

    # systemctl stop firewalld
    # systemctl disable firewalld
    # systemctl status firewalld

DataBase Management System
--------------------------

The MySQL database server should be available to complete installation (locally or not). MariaDB is recommended.

For CentOS / RHEL in version 7, it is necessary to modify **LimitNOFILE** limitation.
Setting this option into /etc/my.cnf will NOT work.

::

   # mkdir -p  /etc/systemd/system/mariadb.service.d/
   # echo -ne "[Service]\nLimitNOFILE=32000\n" | tee /etc/systemd/system/mariadb.service.d/limits.conf
   # systemctl daemon-reload
   # service mysql restart
 
Launch services during the system startup
-----------------------------------------

Enable the automatically start of services during the system startup.

Execute these commands on central server.

* **CentOS v6** ::

    # chkconfig httpd on
    # chkconfig snmpd on
    # chkconfig mysql on

* **CentOS v7** ::

    # systemctl enable httpd.service
    # systemctl enable snmpd.service
    # systemctl enable mysql.service
    
.. note::
    If MySQL database is on a dedicated server, execute the enable command of mysql on DB server.

Conclude installation
---------------------

:ref:`click here to finalise the installation process <installation_web_ces>`.

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
