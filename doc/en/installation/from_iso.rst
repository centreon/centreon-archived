.. _installisoel7:

======================
Using Centreon ISO el7
======================

.. note::
   Installation from Centreon el6 ISO is available :ref:`here<installisoel6>`

************
Installation
************

Step 1: Start
=============

To install Centreon, start your server on the Centreon ISO image in version el7.
Start with **Install CentOS 7**:

.. image :: /images/user/01_bootmenu.png
   :align: center
   :scale: 65%

Step 2: Choice of language
==========================

Choose the language of the installation process then click **Done**:

.. image :: /images/user/02_select_install_lang.png
   :align: center
   :scale: 65%

Step 3: Component selection
===========================

Click on the **Installation Type** menu:

.. image :: /images/user/03_menu_type_install.png
   :align: center
   :scale: 65%

It is possible to choose different options:

.. image :: /images/user/04_form_type_install.png
   :align: center
   :scale: 65%

|

 * **Central with database**: Install Centreon (web interface and database), monitoring engine and broker
 * **Central without database**: Install Centreon (web interface only), monitoring engine and broker
 * **Poller**: Install poller (monitoring engine and broker only)
 * **Database**: Install database server (use with **Central server without database** option)

Step 4: System configuration
============================

Partitioning of disk
--------------------

Click on **Installation Destination** menu:

.. image :: /images/user/05_menu_filesystem.png
   :align: center
   :scale: 65%

Select the hard disk drive and the **I will configure partitioning** option, then click **Done**:

.. image :: /images/user/06_select_disk.png
   :align: center
   :scale: 65%

Using **+** button create your own partitioning file system following :ref:`documentation prerequisites<diskspace>` then click **Done**: 

.. image :: /images/user/07_partitioning_filesystem.png
   :align: center
   :scale: 65%

A confirmation window appears. Click **Accept Changes** to validate the partitioning:

.. image :: /images/user/08_apply_changes.png
   :align: center
   :scale: 65%

Network configuration
---------------------

Click **Network & Hostname** menu:

.. image :: /images/user/09_menu_network.png
   :align: center
   :scale: 65%

Enable all network interfaces and define hostname then click **Done**:

.. image :: /images/user/10_network_hostname.png
   :align: center
   :scale: 65%

Timezone configuration
----------------------

Click **Date & Time** menu:

.. image :: /images/user/11_menu_timezone.png
   :align: center
   :scale: 65%

Select timezone then click on configuration button:

.. image :: /images/user/12_select_timzeone.png
   :align: center
   :scale: 65%

Enable or add a NTP server, click **OK** then **Done**:

.. image :: /images/user/13_enable_ntp.png
   :align: center
   :scale: 65%

Start installation
------------------

Once configuration is over click **Begin Installation**:

.. image :: /images/user/14_begin_install.png
   :align: center
   :scale: 65%

Click **Root Password** :

.. image :: /images/user/15_menu_root_password.png
   :align: center
   :scale: 65%

Define and confirm **root** user password. Click **Done**:

.. image :: /images/user/16_define_root_password.png
   :align: center
   :scale: 65%

Wait during installation process:

.. image :: /images/user/17_wait_install.png
   :align: center
   :scale: 65%

When the installation is finished, click **Reboot**:

.. image :: /images/user/18_reboot_server.png
   :align: center
   :scale: 65%


Update system packages
----------------------

Connect to your server using a terminal and execute:
  ::

  # yum update

.. image :: /images/user/19_update_system.png
   :align: center
   :scale: 65%

Accept all GPG keys:

.. image :: /images/user/20_accept_gpg_key.png
   :align: center
   :scale: 65%

Then restart your server with following command:
  ::

  # reboot

*************
Configuration
*************

.. _installation_web_ces:

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

.. _installation_ppm:

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
