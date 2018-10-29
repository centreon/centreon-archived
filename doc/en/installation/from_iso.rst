.. _installisoel7:

======================
Using Centreon ISO el7
======================

************
Installation
************

Step 1: Starting up the server
==============================

To install Centreon, start up your server from the Centreon ISO image in version el7.
Start up with **Install CentOS 7**:

.. image :: /images/user/01_bootmenu.png
   :align: center
   :scale: 65%

Step 2: Choosing a language
============================

Choose the language for the installation process then click on **Done**:

.. image :: /images/user/02_select_install_lang.png
   :align: center
   :scale: 65%

Step 3: Selecting components
============================

Click on the **Installation Type** menu:

.. image :: /images/user/03_menu_type_install.png
   :align: center
   :scale: 65%

You can choose different options:

.. image :: /images/user/04_form_type_install.png
   :align: center
   :scale: 65%

|

 * **Central with database**: Install Centreon (web interface and database), monitoring engine and broker.
 * **Central without database**: Install Centreon (web interface only), monitoring engine and broker.
 * **Poller**: Install poller (monitoring engine and broker only).
 * **Database**: Install database server (use with **Central server without database** option).

Step 4: System configuration
============================

Partitioning the disk
---------------------

Click on the **Installation Destination** menu:

.. image :: /images/user/05_menu_filesystem.png
   :align: center
   :scale: 65%

Select the hard disk drive and the **I will configure partitioning** option, then click on **Done**:

.. image :: /images/user/06_select_disk.png
   :align: center
   :scale: 65%

Using the **+** button create, your own partitioning file system following the instructions in :ref:`documentation prerequisites<diskspace>`, then click on **Done**:

.. image :: /images/user/07_partitioning_filesystem.png
   :align: center
   :scale: 65%

A confirmation window appears. Click on **Accept Changes** to validate the partitioning:

.. image :: /images/user/08_apply_changes.png
   :align: center
   :scale: 65%

Configuring the network
------------------------

Click on the **Network & Hostname** menu:

.. image :: /images/user/09_menu_network.png
   :align: center
   :scale: 65%

Enable all network interfaces and define hostname, then click on **Done**:

.. image :: /images/user/10_network_hostname.png
   :align: center
   :scale: 65%

Configuring the timezone
------------------------

Click on the **Date & Time** menu:

.. image :: /images/user/11_menu_timezone.png
   :align: center
   :scale: 65%

Select timezone, then click on the configuration button:

.. image :: /images/user/12_select_timzeone.png
   :align: center
   :scale: 65%

To enable or add a NTP server, click on **OK**, then on **Done**:

.. image :: /images/user/13_enable_ntp.png
   :align: center
   :scale: 65%

Beginning the installation
---------------------------

Once configuration is complete, click on **Begin Installation**:

.. image :: /images/user/14_begin_install.png
   :align: center
   :scale: 65%

Click on **Root Password**:

.. image :: /images/user/15_menu_root_password.png
   :align: center
   :scale: 65%

Define and confirm **root** user password. Click on **Done**:

.. image :: /images/user/16_define_root_password.png
   :align: center
   :scale: 65%

Wait for installation process to finish:

.. image :: /images/user/17_wait_install.png
   :align: center
   :scale: 65%

When the installation is complete, click on **Reboot**:

.. image :: /images/user/18_reboot_server.png
   :align: center
   :scale: 65%


Updating the system packages
-----------------------------

Connect to your server using a terminal and execute the command:
  ::

  # yum update

.. image :: /images/user/19_update_system.png
   :align: center
   :scale: 65%

Accept all GPG keys:

.. image :: /images/user/20_accept_gpg_key.png
   :align: center
   :scale: 65%

Then restart your server with the following command:
  ::

  # reboot

*************
Configuration
*************

.. _installation_web_ces:

Using the web interface
=======================

Log in to Centreon web interface via the URL: http://[SERVER_IP]/centreon.
The Centreon setup wizard is displayed. Click on **Next**.

.. image :: /images/user/acentreonwelcome.png
   :align: center
   :scale: 85%

The Centreon setup wizard checks the availability of the modules. Click on **Next**.

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

Provide the information on the admin user, then click on **Next**.

.. image :: /images/user/aadmininfo.png
   :align: center
   :scale: 85%

By default, the ‘localhost’ server is defined and the root password is empty. If you use a remote database server, change these entries.
In this case, you only need to define a password for the user accessing the Centreon databases, i.e., ‘Centreon’. Click on **Next**.

.. image :: /images/user/adbinfo.png
   :align: center
   :scale: 85%

.. note::
    If the **Add innodb_file_per_table=1 in my.cnf file under the [mysqld] section and restart MySQL Server.**
    error message appears, perform the following operations:
    
    1. Log in to the ‘root’ user on your server.
    
    2. Modify this file::
    
        /etc/my.cnf
    
    3. Add these lines to the file::
    
        [mysqld]
        innodb_file_per_table=1
    
    4. Restart mysql service::

        # systemctl restart mysql
    
    5. Click on **Refresh**.

The Centreon setup wizard configures the databases. Click on **Next**.

.. image :: /images/user/adbconf.png
   :align: center
   :scale: 85%

At this point, you will be able to install the Centreon server modules.

Click on **Install**.

.. image :: /images/user/module_installationa.png
   :align: center
   :scale: 85%

Once installation is complete, click on **Next**.

.. image :: /images/user/module_installationb.png
   :align: center
   :scale: 85%

At this point, an advertisement informs you of the latest Centreon news and products. 
If your platform is connected to the internet, you will receive the up-to-date information.
If you are not online, only information on the current version will be displayed.

.. image :: /images/user/aendinstall.png
   :align: center
   :scale: 85%

The installation is complete. Click on **Finish**.

You can now log in.

.. image :: /images/user/aconnection.png
   :align: center
   :scale: 65%

Starting the monitoring engine
==============================

To start the monitoring engine :

 1. On your web interface, go to **Configuration** ==> **Pollers**.
 2. Keep the default options and click on **Export configuration**.
 3. Select **Central** poller from the box input **Pollers**.
 4. Uncheck **Generate Configuration Files** and **Run monitoring engine debug (-v)**.
 5. Check **Move Export Files** and **Restart Monitoring Engine** with option **Restart** selected.
 6. Click on **Export** again.
 7. Log in to the ‘root’ user on your server.

 8. Start Centreon Broker ::

     # systemctl start cbd

 9. Start Centreon Engine ::

     # systemctl start centengine

 10. Start centcore ::

     # systemctl start centcore

 11. Start centreontrapd ::

     # systemctl start centreontrapd

Monitoring is now working. You can begin monitoring your IT system!

Launching services during system bootup
=======================================

To make services automatically start during system bootup run these commands
on the central server: ::

    # systemctl enable centcore
    # systemctl enable centreontrapd
    # systemctl enable cbd
    # systemctl enable centengine

Introduction to the web interface
=================================


The Centreon web interface contains several menus, each with a specific function:

.. image :: /images/user/amenu.png
   :align: center

|

* **Home** lets you access the first home screen after logging in. It provides a summary of overall monitoring status.
* **Monitoring** provides a combined view of the status of all monitored items in real and delayed time using logs and performance graphics.
* **Reporting** provides an intuitive view (using diagrams) of the evolution of monitoring over a given period.
* **Configuration** allows you to configure all monitored items and the monitoring infrastructure.
* **Administration** allows you to configure the Centreon web interface and view the overall status of the servers.

.. _installation_ppm:

***************************************
Quick and easy monitoring configuration
***************************************

Centreon is a highly versatile monitoring solution that can be configured to
meet the specific needs of your IT infrastructure. To quickly configure Centreon and help you get started, you
may want to use Centreon IMP. This tool provides you with Plugin Packs, which are bundled configuration
templates that will dramatically reduce the time needed to implement the Centreon platform for monitoring
the services in your network.

Centreon IMP requires the Centreon License Manager and Centreon Plugin Pack Manager in order to function.

Installing from the internet
=============================

If you haven't installed any modules during the installation process, go to the
**Administration > Extensions > Modules** menu.

Click on **Install/Upgrade all** and validate.

.. image:: /_static/images/installation/install_imp_1.png
   :align: center

Once the installation is complete, click on **Back**.
The modules are now installed.

.. image:: /_static/images/installation/install_imp_2.png
   :align: center

Now proceed to Configuration -> Plugin packs -> Manager.
10 free Plugin Packs are provided to get you started. Five additional Packs are
available once you register and over 150 more if you subscribe to the IMP
offer (for more information: `our website <https://www.centreon.com>`_).

.. image:: /_static/images/installation/install_imp_3.png
   :align: center

You can continue to configure your monitoring system with Centreon IMP by
following the instructions in :ref:`this guide <impconfiguration>`.
