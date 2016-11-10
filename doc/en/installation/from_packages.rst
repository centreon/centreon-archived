.. _install_from_packages:

==============
Using packages
==============

Centreon supplies RPM for its products via the Centreon Enterprise Server (CES) solution Open Sources version available free of charge on our repository.

These packages have been successfully tested on CentOS and Red Hat environments in 6.x and 7.x versions.

*************
Prerequisites
*************

To install Centreon software from the CES repository, you should first install the file linked to the repository.

Perform the following command from a user with sufficient rights:

 ::

  $ wget http://yum.centreon.com/standard/3.4/stable/ces-standard.repo -O /etc/yum.repos.d/ces-standard.repo

The repository is now installed.

Any operating system
--------------------

SELinux should be disabled; for this, you have to modify the file "/etc/sysconfig/selinux" and replace "enforcing" by "disabled":

 ::

  SELINUX=disabled

PHP timezone should be set; go to /etc/php.d directory and create a file named php-timezone.ini who contain the following line :

 ::

  date.timezone = Europe/Paris

After saving the file, please don't forget to restart apache server.

The Mysql database server should be available to complete installation (locally or not). MariaDB is recommended.

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

Web Installation
================

.. note::

Make sure that your Apache and MySQL servers are up and running before continuing.

Open your favorite web browser and go to the address:

::

 http://SERVER_ADDRESS/centreon

You should see the following page:

.. image:: /_static/images/installation/setup_1.png
    :align: center

Click on the **Next** button:

.. image:: /_static/images/installation/setup_2.png
    :align: center

If a package is missing install it and click on the **Refresh** button. Click on the **Next** button as soon as everything is **OK**:

.. image:: /_static/images/installation/setup_3_1.png
    :align: center

Select your monitoring engine. Depending on the selection, the settings are different.

For Centreon Engine:

.. image:: /_static/images/installation/setup_3_2.png
    :align: center

Click on the **Next** button as soon as all the fields are filled.

.. image:: /_static/images/installation/setup_4.png
    :align: center

Select your Stream Multiplexer. Depending on the selection, the settings are different.

For Centreon Broker:

.. image:: /_static/images/installation/setup_4_2.png
    :align: center

Click on the **Next** button when all parameters are filled.

.. image:: /_static/images/installation/setup_5.png
    :align: center

Fill the form with your data. Be sure to remember your password. Click on the **next** button.

.. image:: /_static/images/installation/setup_6.png
    :align: center

Fill the form with information about your database. Click on the **Next** button.

.. image:: /_static/images/installation/setup_7.png
    :align: center

The database structure will be installed during this process. All must be validated by **OK**.

.. note::
    The installation process may ask you to change the settings of the MySQL server to **add innodb_file_per_table=1** in the configuration file.

Click on the **Next** button.

.. image:: /_static/images/installation/setup_8.png
    :align: center

The installation is now finished, click on the ``Finish`` button, you will be redirected to the login screen:

.. image:: /images/user/aconnection.png
    :align: center

Enter your credentials to log in.


.. _installation_ppm:

*****************************
Easy monitoring configuration
*****************************

Centreon is great in itself, highly versatile  and can be configured to
fit the very specifics of your monitored infrastructure. However you
might find useful to use Centreon Plugin Pack Manager to get you started
in minutes. Centreon Plugin Packs are bundled configuration templates
that highly reduce the time needed to properly monitor the most common
services of your network.

Install packages
----------------

When using CES, installation of Centreon Plugin Pack Manager is very
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
