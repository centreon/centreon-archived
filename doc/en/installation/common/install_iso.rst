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
