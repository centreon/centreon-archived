.. _widget_installation:

============
Installation
============

Most Open Source modules can be downloaded from the `forge <http://forge.centreon.com/projects/modules>`_.


*****
Setup
*****

Modules packages (.tgz) are to be unpackaged in the centreon/www/modules directory::

  [root@localhost modules]# ll
  total 36
  drwxr-xr-x  7 apache apache 4096 Nov 16 14:34 centreon-clapi
  drwxr-xr-x 12 apache apache 4096 Apr 17  2012 Syslog


Then, you need to log in the Centreon web UI and go to [Administration] > [Modules] > [Modules] > [Setup]

.. image:: /_static/images/extending/modules/install.png
   :align: center
   :width: 750px


At last, click on the Install button:

.. image:: /_static/images/extending/modules/install_btn.png
   :align: center


After confirming the installation, your module is ready to be used!


*******
Upgrade
*******

The upgrade process is similar to the installation process, but you will see the *upgrade* button instead:

.. image:: /_static/images/extending/modules/upgrade_btn.png
   :align: center


**************
Uninstallation
**************

To uninstall a module, click on this button:

.. image:: /_static/images/extending/widgets/uninstall_btn.png
   :align: center

.. warning::
   Centreon does not have control over the uninstallation process of a module. 
   However, data will probably get erased during this process, we suggest you make a backup before proceeding.
