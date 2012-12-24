.. _install_from_packages:

==============
Using packages
==============

Merethis provides RPM for its products through Centreon Entreprise
Server (CES). Open source products are freely available from our
repository.

These packages have been successfully tested with CentOS 5 and RedHat 5.

*************
Prerequisites
*************

In order to use RPM from the CES repository, you have to install the
appropriate repo file. Run the following command as privileged user::

  $ wget http://yum.centreon.com/standard/ces-standard-2.2.repo -O /etc/yum.repos.d/ces-standard-2.2.repo

The repo file is now installed.

*********************
Centreon installation
*********************

In CES 2.2, there are two choise of basic configuration.

+---------------------------------------+-------------------+-----------------+
| Configuration package name            | Monitoring Engine | Broker module   |
+=======================================+===================+=================+
| centreon-base-config-centreon-engine  | Centreon Engine   | Centreon Broker |
+---------------------------------------+-------------------+-----------------+
| centreon-base-config-nagios           | Nagios            | Ndoutils        |
+---------------------------------------+-------------------+-----------------+

You must choose between this two templates.

Install a central server
------------------------

This part is to install a central server.

Install packages with Centreon Engine
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Run the commands::

  $ yum install centreon-base-config-centreon-engine centreon

Install packages with Nagios
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Run the commands::

  $ yum install centreon-base-config-nagios centreon

After this installation, you can connect to Centreon for finish installation.
The steps of web installation is :ref:`here <installation_web>`.

Install a poller
----------------

This part is to install a poller server.

Install packages with Centreon Engine
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Run the commands::

  $ yum install centreon-poller-centreon-engine

Install packages with Nagios
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Run the commands::

  $ yum install centreon-poller-nagios

Base configuration of pollers
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The communication between a central server and a poller server is by SSH.

You must exchange ssh keys this hosts.

If you have not a ssh private on the central for user centreon::

  $ su - centreon
  $ ssh-keygen -t rsa

You copy this key into the poller::

  $ ssh-copy-id centreon@your_poller_ip
