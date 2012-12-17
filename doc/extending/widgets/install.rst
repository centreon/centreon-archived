============
Installation
============

*****
Setup
*****

Widget packages (.tgz) are to be unpackaged in the centreon/www/widgets directory::

  [root@localhost widgets]# ll
  total 32
  drwxr-xr-x 4 centreon centreon 4096 Dec 17 10:22 dummy
  drwxr-xr-x 4 centreon centreon 4096 Aug 24 15:47 graph-monitoring
  drwxr-xr-x 4 centreon centreon 4096 Aug 24 15:47 hostgroup-monitoring
  drwxr-xr-x 4 centreon centreon 4096 Aug 24 15:47 host-monitoring
  -rw-r--r-- 1 centreon centreon   57 Dec 14 10:19 require.php
  drwxr-xr-x 4 centreon centreon 4096 Aug 24 15:47 servicegroup-monitoring
  drwxr-xr-x 4 centreon centreon 4096 Aug 24 15:47 service-monitoring


Then, you need to log in the Centreon web UI and go to [Administration] > [Modules] > [Widget] > [Setup]

.. image:: /_static/images/extending/widgets/install.png
   :align: center

*******
Upgrade
*******

**************
Uninstallation
**************
