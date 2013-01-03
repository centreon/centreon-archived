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

In order to upgrade Centreon, you must choose between two base templates:
``Centreon Engine and Centreon Broker`` or ``Nagios and Ndo2db``.

This choice is based on your monitoring engine.


Upgrade a central server
------------------------

This part covers the upgrade procedure of a central server.

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

After this upgrade, you can connect to Centreon to finish the upgrade.
The steps of web upgrade are described :ref:`here <upgrade_web>`.

Upgrade a poller
----------------

This part covers the upgrade procedure of remote pollers.

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
   The user for communication between a central server and a poller 
   changed since version 2.4.0, it is now ``centreon`` instead of ``nagios``.

You must exchange ssh keys between the hosts.

If you do not have an SSH private key on the central for user centreon::

  $ su - centreon
  $ ssh-keygen -t rsa

Copy this key onto the poller::

  $ ssh-copy-id centreon@your_poller_ip


********************************************
Upgrade from version Centreon 2.4.0 or after
********************************************

Run the command::

  $ yum update
