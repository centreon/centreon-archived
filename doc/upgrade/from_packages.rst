.. _upgrade_from_packages:

=============
From packages
=============

.. warning::

   Before upgrading Centreon, please make a database backup.

********************************************
Upgrade from Centreon version prior to 2.4.0
********************************************

The RPM structure has changed between Centreon 2.3.x and Centreon 2.4.0.

In order to upgrade Centreon, you must choose between two base templates :
``Centreon Engine and Centreon Broker`` or ``Nagios and Ndo2db``.

This choice is based on your monitoring engine.


Upgrade a central server
------------------------

This part is to upgrade a central server.

Upgrade with Centreon Engine
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Run the commands::

  $ yum install centreon-base-config-centreon-engine centreon
  $ yum update

Upgrade with Nagios
^^^^^^^^^^^^^^^^^^^

Run the commands::

  $ yum install centreon-base-config-nagios centreon
  $ yum update

After this upgrade, you can connect to Centreon for finish upgrade.
The steps of web upgrade is :ref:`here <upgrade_web>`.

Upgrade a poller
----------------

This part is to upgrade pollers.

Upgrade with Centreon Engine
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Run the commands::

  $ yum install centreon-poller-centreon-engine
  $ yum update

Upgrade with Nagios
^^^^^^^^^^^^^^^^^^^

Run the commands::

  $ yum install centreon-poller-nagios
  $ yum update

.. warning::
   If the snmptt package is installed, you must remove it and install the
   package centreon-snmptt.

Base configuration of pollers
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. warning::
   The user for communication between a central server and a poller change in
   version 2.4.0. Change from nagios user to centreon user.

You must exchange ssh keys this hosts.

If you have not a ssh private on the central for user centreon::

  $ su - centreon
  $ ssh-keygen -t rsa

You copy this key into the poller::

  $ ssh-copy-id centreon@your_poller_ip


********************************************
Upgrade from version Centreon 2.4.0 or after
********************************************

Run the command::

  $ yum update
