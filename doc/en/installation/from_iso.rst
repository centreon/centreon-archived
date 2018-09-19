.. _installisoel7:

======================
Using Centreon ISO el7
======================

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

Log into Centreon web interface via the url : http://[SERVER_IP]/centreon.
The Centreon setup wizard is displayed, click on **Next**.

.. image :: /images/user/acentreonwelcome.png
   :align: center
   :scale: 85%

The Centreon setup wizard checks the availability of the modules, click on **Next**.

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

Provide information related to the admin user, click on **Next**.

.. image :: /images/user/aadmininfo.png
   :align: center
   :scale: 85%

By default, the ‘localhost’ server is defined and the root password is empty. If you use a remote database server, these two data entries must be changed. In our box, we only need to define a password for the user accessing the Centreon databases, i.e. ‘Centreon’, click on **Next**.

.. image :: /images/user/adbinfo.png
   :align: center
   :scale: 85%

.. note::
    If the **Add innodb_file_per_table=1 in my.cnf file under the [mysqld] section and restart MySQL Server.**
    error message appears, please perform the following operations:
    
    1. Log-on to the ‘root’ user on your server
    
    2. Modify this file::
    
        /etc/my.cnf
    
    3. Add these lines to the file::
    
        [mysqld]
        innodb_file_per_table=1
    
    4. Restart mysql service::

        # systemctl restart mysql
    
    5. Click on **Refresh**

The Centreon setup wizard configures the databases, click on **Next**.

.. image :: /images/user/adbconf.png
   :align: center
   :scale: 85%

At this point, you will be able to install the modules provided with Centreon.

Click on **Install**

.. image :: /images/user/module_installationa.png
   :align: center
   :scale: 85%

Once installation is performed, click on **Next**

.. image :: /images/user/module_installationb.png
   :align: center
   :scale: 85%

At this point, an ad informs you of the latest Centreon news/products . If your platform is connected to the Internet, you will receive the latest information. If not, the information of the current version will be displayed.

.. image :: /images/user/aendinstall.png
   :align: center
   :scale: 85%

The installation is finished, click on **Finish**.

You can now log in.

.. image :: /images/user/aconnection.png
   :align: center
   :scale: 65%

Start monitoring
================

To start the monitoring engine :

 1. On the web interface, go to **Configuration** ==> **Pollers**
 2. Leave the default options and click on **Export configuration**
 3. Select **Central** poller from the box input **Pollers**
 4. Uncheck **Generate Configuration Files** and **Run monitoring engine debug (-v)**
 5. Check **Move Export Files** and **Restart Monitoring Engine** with option method **Restart** selected
 6. Click on **Export** again
 7. Log into the ‘root’ user on your server
 8. Verify if services **cbd**, **centengine** and **centcore** is running

  ::

    service cbd status
    service centengine status
    service centcore status

 If they are not running, start them

 * Start Centreon Broker

  ::

    service cbd start


 * Start Centreon Engine

  ::

    service centengine start

 * Start centcore

  ::

    service centcore start

Monitoring is now working. You can start to monitor your IT !

Introduction to the web interface
=================================


Centreon web interface is made up of several menus, each menu has a specific function:

.. image :: /images/user/amenu.png
   :align: center

|

* The **Home** menu enables access to the first home screen after logging in. It summarizes the general status of the supervision.
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

Web install
===========

If you didn't installed those module during the installation process, go to the
**Administration > Extensions > Modules** menu.

Click on **Install/Upgrade all** and validate the action.

.. image:: /_static/images/installation/install_imp_1.png
   :align: center

Once the instalaltion is finish, click on **Back**.
The modules are now installed.

.. image:: /_static/images/installation/install_imp_2.png
   :align: center

You're now ready to go to Configuration -> Plugin packs -> Manager.
You'll find there 10 free Plugin Packs to get you started. 5 more are
available after free registration and 150+ if you subscribe to the IMP
offer (more information on `our website <https://www.centreon.com>`_).

.. image:: /_static/images/installation/install_imp_3.png
   :align: center

You can continue to configure your monitoring with Centreon IMP by
following :ref:`this guide <impconfiguration>`.
