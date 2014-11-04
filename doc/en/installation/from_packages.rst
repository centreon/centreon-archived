.. _install_from_packages:

==============
Using packages
==============

Merethis supplies RPM for its products via the Centreon Enterprise Server (CES) solution Open Sources version available free of charge on our repository.

These packages have been successfully tested on CentOS and Red Hat environments in 5.x and 6.x versions

*************
Prerequisites
*************

To install Centreon software from the CES repository, you should first install the file linked to the repository.

CES 3.0 (CentOS 6.x)
--------------------

Perform the following command from a user with sufficient rights:

 ::

 $ wget http://yum.centreon.com/standard/3.0/stable/ces-standard.repo -O /etc/yum.repos.d/ces-standard.repo

The repository is now installed.

CES 2.2 (CentOS 5.x)
--------------------

Perform the following command from a user with sufficient rights:

 ::

 $ wget http://yum.centreon.com/standard/2.2/ces-standard.repo -O /etc/yum.repos.d/ces-standard.repo

The repository is now installed.

*********************
Centreon installation
*********************

From CES 2.2, two installation choices are available

+---------------------------------------+-------------------+-----------------+
| Configuration package name            | Monitoring Engine | Broker module   |
+=======================================+===================+=================+
| centreon-base-config-centreon-engine  | Centreon Engine   | Centreon Broker |
+---------------------------------------+-------------------+-----------------+
| centreon-base-config-nagios           | Nagios            | NDOutils        |
+---------------------------------------+-------------------+-----------------+

You should choose between one of the two configuration processes of your monitoring platform. Merethis recommends the first choice based on the “Centreon Engine” scheduler and the “Centreon Broker” stream multiplexer.

Install a central server
------------------------

The chapter describes the installation of a Centreon central server.

Installation of the server with the Centreon Engine
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Perform the command:

 ::

  $ yum install centreon-base-config-centreon-engine centreon


Installation of the server with the Nagios engine
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Perform the command:

 ::

  $ yum install centreon-base-config-nagios centreon

After this step you should connect to Centreon to finalise the installation process.
This step is described :ref:`here <installation_web>`.

Installing a poller
-------------------

This chapter describes the installation of a collector.

Installation of the server with Centreon Engine
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Perform the command:

 ::

  $ yum install centreon-poller-centreon-engine

Installation of the server with the Nagios engine
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Perform the command:

 ::

  $ yum install centreon-poller-nagios

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
