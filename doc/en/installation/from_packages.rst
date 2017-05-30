.. _install_from_packages:

==============
Using packages
==============

Centreon supplies RPM for its products via the Centreon Open Sources version available free of charge on our repository (ex CES).

These packages have been successfully tested on CentOS and Red Hat environments in 6.x and 7.x versions.

*************
Prerequisites
*************

To install Centreon software from the repository, you should first install the file linked to the repository.

Perform the following command from a user with sufficient rights.

Centreon Repository
--------------

For CentOS 6.

::

   $ wget http://yum.centreon.com/standard/3.4/el6/stable/noarch/RPMS/centreon-release-3.4-4.el6.noarch.rpm
   $ yum install --nogpgcheck centreon-release-3.4-4.el6.noarch.rpm


For CentOS 7.

::

   $ wget http://yum.centreon.com/standard/3.4/el7/stable/noarch/RPMS/centreon-release-3.4-4.el7.centos.noarch.rpm
   $ yum install --nogpgcheck centreon-release-3.4-4.el7.centos.noarch.rpm


The repository is now installed.

*********************
Centreon installation
*********************

Install a central server
------------------------

The chapter describes the installation of a Centreon central server.

Perform the command:

 ::

  $ yum install centreon-base-config-centreon-engine centreon


After this step you should connect to Centreon to finalise the installation process.
This step is described :ref:`here <installation_web_ces>`.

Installing a poller
-------------------

This chapter describes the installation of a collector.

Perform the command:

 ::

 $ yum install centreon-poller-centreon-engine

Install mysql on the same server
--------------------------------

Ce chapitre décrit l'installation de mysql sur un serveur comprenant Centreon.
This chapter describes the installation of mysql on a server including Centreon.

Perform the command:

  ::

   $ yum install mariadb-server
   $ service mysql restart

Base configuration of a poller
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The communication between a central server and a poller server is by SSH.

You should exchange the SSH keys between the servers.

If you don’t have any private SSH keys on the central server for the Centreon user:

 ::

 $ su - centreon
 $ ssh-keygen -t rsa

Copy this key on the collector:

 ::

 $ ssh-copy-id centreon@your_poller_ip


.. _installation_web:

Any operating system
--------------------

Disable SELinux
^^^^^^^^^^^^^^^

SELinux should be disabled; for this, you have to modify the file "/etc/sysconfig/selinux" and replace "enforcing" by "disabled":

::

    SELINUX=disabled

After saving the file, please reboot your operating system to apply the changes.

PHP timezone
^^^^^^^^^^^^

PHP timezone should be set; go to /etc/php.d directory and create a file named php-timezone.ini who contain the following line :

::

    date.timezone = Europe/Paris

After saving the file, please don't forget to restart apache server.

Firewall
^^^^^^^^

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
^^^^^^^^^^^^^^^^^^^^^^^^^^

The Mysql database server should be available to complete installation (locally or not). MariaDB is recommended.

For CentOS / RHEL in version 7, it is necessary to modify **LimitNOFILE** limitation.
Edit **/etc/systemd/system/mysqld.service** file and change ::

    LimitNOFILE=32000

Save the file and execute the folowwing commands::

    # systemctl daemon-reload
    # service mysqld restart

Web Installation
================

The End of installation wizard of Centreon is displayed, click on **Next**.

.. image :: /images/user/acentreonwelcome.png
   :align: center
   :scale: 85%

The End of installation wizard of Centreon checks the availability of the modules, click on **Next**.

.. image :: /images/user/acentreoncheckmodules.png
   :align: center
   :scale: 85%

Click on **Next**.

.. image :: /images/user/amonitoringengine2.png
   :align: center
   :scale: 85%

Click on **Next**.

.. image :: /images/user/abrokerinfo2.png
   :align: center
   :scale: 85%

Define the data concerning the admin user, click on **Next**.

.. image :: /images/user/aadmininfo.png
   :align: center
   :scale: 85%

By default, the ‘localhost’ server is defined and the root password is empty. If you use a remote database server, these two data entries must be changed. In our box, we only need to define a password for the user accessing the Centreon databases, i.e. ‘Centreon’, click on **Next**.

.. image :: /images/user/adbinfo.png
   :align: center
   :scale: 85%

If the following error message appears: **Add innodb_file_per_table=1 in my.cnf file under the [mysqld] section and restart MySQL Server.** Perform the following operation:

1.  Log-on to the ‘root’ user on your server

2.  Modify this file 

::

  /etc/my.cnf

3.  Add these lines to the file

.. raw:: latex 

::

  [mysqld] 
  innodb_file_per_table=1

4.  Restart mysql service

::

  service mysql restart

5.  Click on **Refresh**

The End of installation wizard configures the databases, click on **Next**.

.. image :: /images/user/adbconf.png
   :align: center
   :scale: 85%

The installation is finished, click on Finish.

At this stage a publicity allows to know the latest Centreon . If your platform is connected to the Internet you have the latest information , if the information present in this version will be offered.

.. image :: /images/user/aendinstall.png
   :align: center
   :scale: 85%

You can now log in.

.. image :: /images/user/aconnection.png
   :align: center
   :scale: 85%

Start monitoring
================

To start monitoring engine :
 
 1.   On web interface, go to **Configuration** ==> **Monitoring engines**
 2.   Leave the default options and click on **Export**
 3.   Uncheck **Generate Configuration Files** and **Run monitoring engine debug (-v)**
 4.   Check **Move Export Files** and **Restart Monitoring Engine**
 5.   Click on **Export** again
 6.   Log into the ‘root’ user on your server
 7.   Start Centreon Broker

::
 
  service cbd start
8.   Start Centreon Engine

::
 
   service centengine start

 8.   Start centcore

::
 
   service centcore start

Monitoring is now working. You can start to monitor your IT !

Introduction to the web interface
=================================


Centreon web interface is made up of several menus, each menu has a specific function:

.. image :: /images/user/amenu.png
   :align: center

|

*       The **Home** menu enables access to the first home screen after logging in. It summarises the general status of the supervision.
*       The **Monitoring** menu contains the status of all the supervised elements in real and delayed time via the viewing of logs and performance graphics.
*       The **Reporting** menu serves to view, intuitively (via diagrams), the evolution of the supervision on a given period.
*       The **Configuration** menu serves to configure all monitored objects and the supervision infrastructure.
*       The **Administration** menu serves to configure the Centreon web interface and to view the general status of the servers.


.. _installation_ppm:

*****************************
Easy monitoring configuration
*****************************

Centreon is great in itself, highly versatile  and can be configured to
fit the very specifics of your monitored infrastructure. However you
might find useful to use Centreon IMP to get you started in minutes.
Centreon IMP provides you Plugin Packs which are bundled configuration
templates that highly reduce the time needed to properly monitor the
most common services of your network.

Centreon IMP needs the technical components Centreon License Manager
and Centreon Plugin Pack Manager to work.

Install packages
----------------

When using Centreon ISO, installation of Centreon Plugin Pack Manager is very
easy. You'll see that Centreon License Manager will be installed too
as a dependency.

::

   $ yum install centreon-pp-manager

Web install
-----------

Once the packages installed, you need to enable the module in Centreon.
So get to the Administration -> Extensions -> Modules page.

.. image:: /_static/images/installation/ppm_1.png
   :align: center

Install Centreon License Manager (dependency of Centreon Plugin Pack Manager) first.

.. image:: /_static/images/installation/ppm_2.png
   :align: center

Then install Centreon Plugin Pack Manager itself.

.. image:: /_static/images/installation/ppm_3.png
   :align: center

You're now ready to got to Administration -> Extensions -> Plugin packs -> Setup.
You'll find there 6 free Plugin Packs to get you started. 5 more are
available after free registration and 150+ if you subscribe to the IMP
offer (more information on `our website <https://www.centreon.com>`_).

.. image:: /_static/images/installation/ppm_4.png
   :align: center

You can continue to configure your monitoring with Centreon IMP by
following :ref:`this guide <impconfiguration>`.
