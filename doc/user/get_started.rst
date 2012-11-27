===============
Getting Started
===============

This section will show you the basic configuration you need for your monitoring system.

*********
Scheduler
*********

Paths
=====

Some default paths need to be changed.

.. image:: /_static/images/user/nagiospaths.png
   :align: center

With NDOUtils
=============

Ignore this part if you are not using NDOUtils.

The basic broker module configuration should be this:

.. image:: /_static/images/user/nagiosbroker_ndocfg.png
   :align: center

With Centreon Broker
====================

Ignore this part if you are not using Centreon Broker.

*************
Broker Module
*************

Module Selection
================

You need to specify the broker module in *Administration > Options > Monitoring*.

Select the appropriate module:

.. image:: /_static/images/user/brokermoduleselection.png
   :align: center

NDOUtils Configuration
======================

Ignore this part if you are not using NDOUtils.

With NDOUtils, you will need to configure ndomod and ndo2db (*Configuration > Centreon > NDOUtils*). 

Basic configuration looks like this for ndomod:

.. image:: /_static/images/user/ndomodconf.png
   :align: center

For ndo2db:

.. image:: /_static/images/user/ndo2dbconf_1.png
   :align: center



.. image:: /_static/images/user/ndo2dbconf_2.png
   :align: center

.. image:: /_static/images/user/ndo2dbconf_3.png
   :align: center

Centreon Broker
===============

Ignore this part if you are not using Centreon Broker.

****
SNMP
****

By default, Centreon will monitor itself, so you will need to activate
the SNMP service first::

  /etc/init.d/snmpd start

*******
Plugins
*******

Make sure your plugins have the correct permissions to be executed by
the scheduler. Or, you could just set the permissions like this::

  cd /usr/lib/nagios/plugins/
  chmod +x check_*

*******************
Starting the broker
*******************

Debian
======

Edit the file::

  vim /etc/default/ndoutils

Change the line::

  ENABLE_NDOUTILS=0

To::

  ENABLE_NDOUTILS=1

Start the broker
================

::

  /etc/init.d/ndo2db start

********************
Starting centstorage
********************

Centstorage is used for generating RRD graphs::

  /etc/init.d/centstorage start

******************
Export and Restart
******************

This is the most exciting part where you will start monitoring your
very first host and services! You will need to export your
configuration files and restart the scheduler:

.. image:: /_static/images/user/nagios_restart.png
   :align: center

This is what you should get in your nagios log file::

  $ tailf /var/log/nagios/nagios.log
  [1322143480] Nagios 3.3.1 starting... (PID=18772)
  [1322143480] Local time is Thu Nov 24 15:04:40 CET 2011
  [1322143480] LOG VERSION: 2.0
  [1322143480] ndomod: NDOMOD 1.4b9 (10-27-2009) Copyright (c) 2009 Nagios Core Development Team and Community Contributors
  [1322143480] ndomod: Successfully connected to data sink.  0 queued items to flush.
  [1322143480] Event broker module '/usr/lib/nagios/brokers/ndomod.o' initialized successfully.
  [1322143480] Finished daemonizing... (New PID=18776)
  [1322143481] INITIAL HOST STATE: Centreon-Server;UP;HARD;1;
  [1322143481] INITIAL SERVICE STATE: Centreon-Server;Disk-/;OK;HARD;1;(null)
  [1322143481] INITIAL SERVICE STATE: Centreon-Server;Load;OK;HARD;1;(null)
  [1322143481] INITIAL SERVICE STATE: Centreon-Server;Memory;OK;HARD;1;(null)
  [1322143481] INITIAL SERVICE STATE: Centreon-Server;Ping;OK;HARD;1;(null)

Also, at the top of your web page, you should see the following display:

.. image:: /_static/images/user/topcounter.png
   :align: center

