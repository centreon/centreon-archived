===============
Getting Started
===============

This section will show you the basic steps to start your monitoring system.

************
Installation
************

.. todo::
   Blabla presentation

Monitoring Engine
=================

You can use a monitoring engine compatible to Nagios.

For install Centreon Engine as monitoring engine, see
:ref:`this documentation <centreon-engine:_user_installation_using_sources>`.

Broker module
=============

Broker module supported by Centreon is NDOUtils and Centreon Broker.

For install Centreon Broker as broker module, see
:ref:`this documentation <centreon-broker:_user_installation>`.

Web User Interface
==================

You can find the way to install the Centreon WebUI :ref:`here <install>`.

*******************
Starting monitoring
*******************

This is the most exciting part where you will start monitoring your
very first host and services! You will need to export your
configuration files and restart the scheduler:

Login to Centreon
=================

.. image:: /_static/images/user/centreon_login.png
   :align: center

Export and Restart
==================

.. image:: /_static/images/user/nagios_restart.png
   :align: center

This is what you should get in your Centreon Engine log file::

  $ tailf /var/log/centreon-engine/centengine.log
  [1355929880] Centreon Engine 1.3.0 starting ... (PID=17466)
  [1355929880] Local time is Wed Dec 19 16:11:20 CET 2012
  [1355929880] LOG VERSION: 2.0
  [1355929880] Event broker module '/usr/lib64/centreon-engine/externalcmd.so' initialized successfully.
  [1355929880] Centreon Broker: log applier: applying 1 logging objects
  
  [1355929880] Event broker module '/usr/lib/nagios/cbmod.so' initialized successfully.
  [1355929880] INITIAL HOST STATE: Centreon-Server;UP;HARD;1;
  [1355929880] INITIAL SERVICE STATE: Centreon-Server;Disk-/;OK;HARD;1;
  [1355929880] INITIAL SERVICE STATE: Centreon-Server;Load;OK;HARD;1;
  [1355929880] INITIAL SERVICE STATE: Centreon-Server;Memory;OK;HARD;1;
  [1355929880] INITIAL SERVICE STATE: Centreon-Server;Ping;OK;HARD;1;

.. warning::

   The external command module **must be** initialized successfully else
   Centreon can not execute commands.

   The broker module **must be** initialized successfully, else datas are not
   send to database.

Or this is what you should get in your nagios log file::

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

.. warning::

   The broker module **must be** initialized successfully, else there are not
   data send to database.

Also, at the top of your web page, you should see the following display:

.. image:: /_static/images/user/topcounter.png
   :align: center

If the top of your web page does not display this information, read the next
section.

******************
Things to validate
******************

Validate services running
=========================

Checking CentCore::

  $ /etc/init.d/centcore status
  centcore (pid  18113) is running...

For Centreon Engine / Centreon Broker
*************************************

Checking Centreon Engine::

  $ /etc/init.d/centengine status
  centengine status: running                                 [  OK  ]

Checking Centreon Broker::

  $ /etc/init.d/cbd status
  cbd (pid  17963) is running...
  cbd (pid  18013) is running...


For Nagios / NDOUtils
*********************

Checking Nagios::

  $ /etc/init.d/nagios status
  nagios (pid 2896) is running...

Checking ndo2db::

  $ /etc/init.d/ndo2db status
  ndo2db (pid 2894 2437) is running...

Checking CentStorage::

  $ /etc/init.d/centstorage status
  centstorage is running.

..
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
  
  Centreon Broker
  ===============
  
  .. note::
  
     Debian users, edit the */etc/defaults/cbd* file and set the
     ``RUN_AT_STARTUP`` variable to **YES**.
  
  Execute the init script as follow::
  
    $ /etc/init.d/cbd start
  
  NDOUtils
  ========
  
  .. note::
  
     Debian users, edit the */etc/defaults/ndoutils* file and set the
     ``ENABLE_NDOUTILS`` variable to **1**.
  
  Execute the init script as follow::
  
    $ /etc/init.d/ndo2db start
  
  Starting centstorage
  --------------------
  
  Centstorage is used for generating RRD graphics::
  
    /etc/init.d/centstorage start
