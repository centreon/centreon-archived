.. _install_from_vm:

========
Using VM
========

You can download in Centreon web site, VM already installed.

These VMs are in OVF format and have been tested for VMWare infrastructure. The following procedure have been done for VSphere Client in 5.1 version

Import
------

The first step is to import the OVF FIle. To do that go in **File > Deploy OVF Template** and choose your file.
You can then follow diffrents menus. Choices you made are linked to your VMWare configuration so it's difficult to be more specific.
Just be noticed that best practice are to used **Thin Provision** to keep some spaces in disk.

Connection
----------

The server has default password.

To connect to the web UI use : **admin/centreon**. You can also connect to the server using SSH with the account : **root/centreon**

.. note::

    For security reasons, we highly recommand you to change this passwords after installation.

Poller
------

Using Poller VM is nearly the same as central. You just have to exchange SSH keys and configure it on web interface.

Exchange SSH keys
=================

On your central server:

   ::

    su - centreon
    ssh-copy-id -i .ssh/id_rsa.pub centreon@IP_POLLER

The password of the centreon user is **centreon**. It can be easyly changed using **passwd** command.

On Web interface
================

#. In **Configuration > Poller > Pollers**, Activate Poller Template and replace IP_POLLER by the poller IP address.
#. In **Configuration > Poller > Engine configuration**, Activate Poller-template
#. In **Configuration > Poller > Broker configuration**, Activate Poller-template-module and in **Output** tab, replace IP_CENTRAL by the central IP address.

Then you can configure and add ressources on your poller. The poller is operational !

.. warning::

    When you export your configuration for the first time, you have to choose "restart".