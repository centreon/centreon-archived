.. _basic_plugins:

=============================
Basic principle of monitoring
=============================

Before starting to monitor, let's take a look at some basic concepts:

* A **host** is any device that has an IP address and that one wishes to monitor. For example, a physical server, a
  virtual machine, a temperature probe, an IP camera, a printer or a storage space.
* A **service** is a check point, or indicator, to be monitored on a host. This can be the CPU usage rate, temperature,
  motion detection, bandwidth usage rate, disk I/O, and so on.
* In order to collect each indicator value, monitoring **plugins** are used which are periodically executed by a
  collection engine called Centreon Engine.
* To be executed, a plugin needs a set of arguments that define, for example, which host to connect to or through which protocol.
  The plugin and its associated arguments form a **command**.

For example, to monitor a host with Centreon is to configure all the commands needed to measure the desired indicators,
and then deploy that configuration to the collection engine so that these commands are run periodically.

Nevertheless, to drastically simplify the configuration, we will rely on monitoring templates:

* A **host template** defines the configuration of the indicators for a given type of equipment.
* It relies on **service templates** that define the configuration of the commands needed to collect these indicators.
* Centreon provides downloadable **Plugins Packs** to install on its monitoring platform: each Plugin Pack includes host
  and services templates to configure the monitoring of a particular device in a few clicks.

This quick start guide proposes to install the monitoring templates supplied free of charge with the Centreon solution
and then to implement them to monitor your first equipment.

.. image:: /images/quick_start/host_service_command.png
    :align: center

.. note::
    To go further with templates, please read the :ref:`Templates chapter<hosttemplates>`.

******************************************
Installation of basic monitoring templates
******************************************

Go to the **Configuration > Plugin Packs** menu.

.. note::
    Configure :ref:`the proxy<impproxy>` to allow the Centreon server to access the Internet.

Install the **base-generic** Plugin Pack by moving your cursor on it and by clicking on **+** icon (it is a prerequisite
to the installation of any other Plugin Pack):

.. image:: /_static/images/quick_start/pp_base_generic.png
    :align: center

Install other Plugin Packs you probably need for your environment, for **Linux SNMP** and **Windows SNMP** available
for free:

.. image:: /_static/images/quick_start/pp_install_basic.gif
    :align: center

Now you have the basic templates and plugins to initial monitoring!

Five additional Packs are available once you register on `our web site <https://store.centreon.com>`_, and over 300
more if you subscribe to the `IMP offer <https://store.centreon.com>`_.

.. note::
    If you already have a Centreon account, `you can now authenticate your Centreon platform 
    <https://documentation.centreon.com/docs/plugins-packs/en/latest/installation.html>`_
    to receive additional Plugin Packs or any services associated with your account.

**********************
Deploy your monitoring
**********************

Start now to supervise your first equipment:

* :ref:`Monitor a Linux server with SNMP<monitor_linux>`
* :ref:`Monitor a Windows server with SNMP<monitor_windows>`
* :ref:`Monitor a Cisco Router with SNMP<monitor_cisco>`
* :ref:`Monitor a MySQL or MariaDB database<monitor_mysql>`
* :ref:`Monitor Printer equipment with SNMP<monitor_printer>`
* :ref:`Monitor UPS equipment with SNMP<monitor_ups>`
