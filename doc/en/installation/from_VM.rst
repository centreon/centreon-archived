.. _install_from_vm:

============================
Using virtual machines (VMs)
============================

Two preconfigured virtual machines are available on the 
`Centreon download <https://download.centreon.com/>`_ web site.

These virtual machines are available in OVA (VMware) and OVF (VirtualBox) format.

.. note::
    The OVA/OVF may not have a network adapter configured. If so, you will have
    to configure a network adapter in your virtual machine before you proceed.

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

Log into the CLI of your Centreon VM. The server has a default password.

To connect to the web UI use: **admin/centreon**.

You can also connect to the server via SSH using the account: **root/centreon**.
The **root** password of the DBMS is not initialized.

.. note::
    For security reasons, we highly recommend for you to change these passwords after installation.

On your first login to Centreon CLI, you will see a banner that describes
additional oerations to be performed. **It is imperative that you complete the instructions, especially operations 4 and 5.**

.. note::
    To remove this message, delete the **/etc/profile.d/centreon.sh** file.

Quick start
===========

Go to the :ref:`quick start<quickstart>` chapter to configure your first
monitoring.
