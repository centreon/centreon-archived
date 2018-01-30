.. _installisoel6:

======================
Using Centreon el6 ISO
======================

************
Installation
************

Step 1 : Start
==============

To install Centreon, start the server/virtual machine using the ISO file cited in the downloads section of the installation guide.
Start with the **Install or upgrade an existing system** option

.. image :: /images/user/abootmenu.png
   :align: center
   :scale: 65%

Click on **Next**

.. image :: /images/user/adisplayicon.png
   :align: center
   :scale: 65%


Step 2 : Choice of language
===========================

Choose your language and click on **Next**.

.. image :: /images/user/ainstalllanguage.png
   :align: center
   :scale: 65%

Select the keyboard used by your system and click on **Next**.

.. image :: /images/user/akeyboard.png
   :align: center
   :scale: 65%


Step 3 : General configuration
==============================

Depending on the type of storage required, choose the options necessary to obtain the partitioning that suits you best.

.. image :: /images/user/adatastore1.png
   :align: center
   :scale: 65%

A warning message may appear

.. image :: /images/user/adatastore2.png
   :align: center
   :scale: 65%

Choose your hostname and click on **Configure network** in order to modify your network card configuration.

Select the network card that you want to use and go to the “IPv4 Settings” tab or the “IPv6 Settings” tab (depending on the requirements) to configure the IP addresses of the different interfaces. Click on **Apply** to save the changes.

.. image :: /images/user/anetworkconfig.png
   :align: center
   :scale: 65%

Click on **Close** and  **Next** to continue.

Select your time zone and click on **Next**.

.. image :: /images/user/afuseauhoraire.png
   :align: center
   :scale: 65%

Enter the desired root password, and click on **Next**.

Select the partitioning options that suit you best. Then validate.

.. image :: /images/user/apartitionning.png
   :align: center
   :scale: 65%


Step 4 : Component selection
============================

Choose the server type
----------------------

It is possible to choose from different options to answer the question: **Which server type would you like to install?**:


.. image :: /images/user/aservertoinstall.png
   :align: center
   :scale: 65%

|

* Central server with database : Install Centreon (web interface and database), monitoring engine and broker
* Central server without database : Install Centreon (web interface only), monitoring engine and broker
* Poller server : Install poller (monitoring engine and broker only)
* Database server : Install database server (use with **Central server without database** option)

In our box, we shall choose the **Centreon Server with database** option.

Once all these options have been selected, the installation starts.

.. image :: /images/user/arpminstall.png
   :align: center
   :scale: 65%

When the installation is finished, click on **Restart**.

.. image :: /images/user/arestartserver.png
   :align: center
   :scale: 65%


*************
Configuration
*************

Via the web interface
=====================

Log into web interface via : http://[SERVER_IP]/centreon.
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

1. Log-on to the ‘root’ user on your server

2. Modify this file

::

  /etc/my.cnf

3. Add these lines to the file

.. raw:: latex

::

  [mysqld]
  innodb_file_per_table=1

4. Restart mysql service

::

  service mysql restart

5. Click on **Refresh**

The End of installation wizard configures the databases, click on **Next**.

.. image :: /images/user/adbconf.png
   :align: center
   :scale: 85%

The installation is finished, click on Finish.

At this stage, an ad informs you of the latest Centreon news/products . If your platform is connected to the Internet, you will receive the latest information. If not, the information of the current version will be displayed.

.. image :: /images/user/aendinstall.png
   :align: center
   :scale: 85%

You can now log in.

.. image :: /images/user/aconnection.png
   :align: center
   :scale: 65%

Start monitoring
================

To start the monitoring engine :

 1. On the web interface, go to **Configuration** ==> **Monitoring engines**
 2. Leave the default options and click on **Export**
 3. Uncheck **Generate Configuration Files** and **Run monitoring engine debug (-v)**
 4. Check **Move Export Files** and **Restart Monitoring Engine**
 5. Click on **Export** again
 6. Log into the ‘root’ user on your server
 7. Start Centreon Broker

  ::

    service cbd start

 8. Start Centreon Engine

  ::

    service centengine start

 9. Start centcore

  ::

    service centcore start

Monitoring is now working. You can start to monitor your IT !

Introduction to the web interface
=================================


Centreon web interface is made up of several menus, each menu has a specific function:

.. image :: /images/user/amenu.png
   :align: center

|

* The **Home** menu enables access to the first home screen after logging in. It summarises the general status of the supervision.
* The **Monitoring** menu contains the status of all the supervised elements in real and delayed time via the viewing of logs and performance graphics.
* The **Reporting** menu serves to view, intuitively (via diagrams), the evolution of the supervision on a given period.
* The **Configuration** menu serves to configure all monitored objects and the supervision infrastructure.
* The **Administration** menu serves to configure the Centreon web interface and to view the general status of the servers.

Before going further
====================

it is necessary update the server. To do this:

 #.	Log in as a ‘root’ on the central server
 #.	Enter this command

::

    yum -y update

Allow the update to run fully and then restart the server in case of a kernel update.

*****************************
Easy monitoring configuration
*****************************

Centreon is great in itself, highly versatile  and can be configured to
fit the very specifics of your monitored infrastructure. However you
might find it useful to use Centreon IMP to get you started in minutes.
Centreon IMP provides you Plugin Packs which are bundled configuration
templates that highly reduce the time needed to properly monitor the
most common services of your network.

Centreon IMP needs the technical components: Centreon License Manager
and Centreon Plugin Pack Manager to work.

Install packages
================

When using Centreon ISO, the installation of Centreon Plugin Pack Manager is very
easy. You'll see that Centreon License Manager will be installed too
as a dependency.

::

   $ yum install centreon-pp-manager

Web install
===========

Once the packages are installed, you need to enable the module in Centreon.
So go to the Administration -> Extensions -> Modules page.

.. image:: /_static/images/installation/ppm_1.png
   :align: center

Install Centreon License Manager (dependency of Centreon Plugin Pack Manager) first.

.. image:: /_static/images/installation/ppm_2.png
   :align: center

Then install Centreon Plugin Pack Manager itself.

.. image:: /_static/images/installation/ppm_3.png
   :align: center

You're now ready to go to Administration -> Extensions -> Plugin packs -> Setup.
You'll find there 6 free Plugin Packs to get you started. 5 more are
available after free registration and 150+ if you subscribe to the IMP
offer (more information on `our website <https://www.centreon.com>`_).

.. image:: /_static/images/installation/ppm_4.png
   :align: center

You can continue to configure your monitoring with Centreon IMP by
following :ref:`this guide <impconfiguration>`.
