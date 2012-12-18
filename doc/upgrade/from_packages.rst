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

Upgrade with Centreon Engine
----------------------------

Run the commands::

  $ yum install centreon-base-config-centreon-engine
  $ yum update

Upgrade with Nagios
-------------------

Run the commands::

  $ yum install centreon-base-config-nagios
  $ yum update


********************************************
Upgrade from version Centreon 2.4.0 or after
********************************************

Run the command::

  $ yum update
