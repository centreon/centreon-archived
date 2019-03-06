.. _install_from_vm:

============================
Using virtual machines (VMs)
============================

Two preconfigured virtual machines are available on the 
`Centreon download <https://download.centreon.com/>`_ web site.

These virtual machines are available in OVA (VMware) and OVF (VirtualBox) format.

***********************
Centreon Central server
***********************

Importing
=========

The first step is to import the OVF File. Go to **File > Deploy OVF Template** and select a file.
Because the menu selections are actually linked to your specific VMWare configuration, we are unable to provide more information.
Be advised that best practice is to use **Thin Provision** to save as much free space as possible on the disk.

Connecting
==========

The server has a default password.

To connect to the web UI use: **admin/centreon**.

You can also connect to the server via SSH using the account: **root/centreon**.
The **root** password of the DBMS is not initialized.

.. note::
    For security reasons, we highly recommend for you to change these passwords after installation.

On your first connecting, a message describes the operations to be performed.
Run these, **especially operations 4 and 5**.

.. note::
    To remove this message, delete the **/etc/profile.d/centreon.sh** file.

******
Poller
******

Deploying the Poller from a virtual machine is almost the same as from the central server. You have to exchange SSH
keys and configure the Poller through the web interface.

Exchanging SSH keys
===================

The communication between the central server and a poller server is done via SSH.

You must exchange the SSH keys between the servers.

If you don’t have any private SSH keys on the central server for the
**centreon** user: ::

    # su - centreon
    $ ssh-keygen -t rsa

Copy this key to the new server: ::

    # su - centreon
    $ ssh-copy-id -i .ssh/id_rsa.pub centreon@IP_POLLER

The password of the **centreon** user is *centreon*. It can be easily changed using the **passwd** command.

On the Web interface
====================

.. include:: ../administration_guide/poller/wizard_add_poller.rst
