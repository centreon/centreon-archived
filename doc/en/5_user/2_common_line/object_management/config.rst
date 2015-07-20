Config
======

Overview
--------

Object name: **centreon-configuration:Config**

Available parameters are the following:

================== ======================
Parameter          Description
================== ======================
**id**             Poller id
================== ======================

Generate
--------

In order to generate configuration, use **generate** action::

  ./centreonConsole centreon-configuration:Config:generate id=1

Test
----

In order to test configuration, use **test** action::

  ./centreonConsole centreon-configuration:Config:test id=1
  Testing configuration files of poller 1

Move
----

In order to move configuration files, use **move** action::

  ./centreonConsole centreon-configuration:Config:move id=1
  Copying configuration files of poller 1

Apply
-----

In order to apply configuration, use **apply** action::

  ./centreonConsole centreon-configuration:Config:apply "id=1:action=reload"
  Processing poller 1
  Performing reload action on Engine...
  Performing reload action on Broker...
  
.. note::
     Action can be : reload, forcereload or restart.
