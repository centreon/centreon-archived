.. _install_from_vm:

========
Using VM
========

Two pre-configured virtual machines are available on 
`Centreon download <https://download.centreon.com/>`_ web site.

These virtual machines are available in OVA (VMware) and OVF (VirtualBox) format.

***********************
Centreon Central server
***********************

Import
======

The first step is to import the OVF File. To do that go in **File > Deploy OVF Template** and choose your file.
You can then follow different menus. Choices you made are linked to your VMWare configuration so it's difficult to be more specific.
Just be noticed that best practice are to used **Thin Provision** to keep some spaces in disk.

Connection
==========

The server has default password.

To connect to the web UI use : **admin/centreon**.

You can also connect to the server using SSH with the account : **root/centreon**
The **root** password of the DBMS is not initialized.

.. note::
    For security reasons, we highly recommend you to change those passwords after installation.

On the first connection, a message describes the operations to be performed.
Run these, **especially operations 4 and 5**.

.. note::
    To remove this message, remove the **/etc/profile.d/centreon.sh** file.

******
Poller
******

Using Poller VM is nearly the same as central. You just have to exchange SSH
keys and configure it on web interface.

Exchange SSH keys
=================

The communication between a central server and a poller server is done by SSH.

You should exchange the SSH keys between the servers.

If you don’t have any private SSH keys on the central server for the
**centreon** user: ::

    # su - centreon
    $ ssh-keygen -t rsa

Copy this key on the new server: ::

    # su - centreon
    $ ssh-copy-id -i .ssh/id_rsa.pub centreon@IP_POLLER

The password of the centreon user is **centreon**. It can be easily changed using **passwd** command.

On Web interface
================

.. include:: ../administration_guide/poller/wizard_add_poller.rst
