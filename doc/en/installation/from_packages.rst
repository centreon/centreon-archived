.. _install_from_packages:

==============
Using packages
==============

Centreon supplies RPM for its products via the Centreon Enterprise Server (CES) solution Open Sources version available free of charge on our repository.

These packages have been successfully tested on CentOS and Red Hat environments in 5.x and 6.x versions

*************
Prerequisites
*************

To install Centreon software from the CES repository, you should first install the file linked to the repository.

Perform the following command from a user with sufficient rights:

 ::

 $ wget http://yum.centreon.com/standard/3.0/stable/ces-standard.repo -O /etc/yum.repos.d/ces-standard.repo

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

You should choose between one of the two configuration processes of your monitoring platform. Centreon recommends the first choice based on the “Centreon Engine” scheduler and the “Centreon Broker” stream multiplexer.

Install a central server
------------------------

The chapter describes the installation of a Centreon central server.

Perform the command:

 ::

  $ yum install centreon-base-config-centreon-engine centreon


After this step you should connect to Centreon to finalise the installation process.
This step is described :ref:`here <installation_web>`.

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
