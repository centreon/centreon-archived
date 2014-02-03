==========================
Frequently Asked Questions
==========================

**************
Administration
**************

How does the *Empty all services data* action work?
===================================================

In order to preserve global performance, this action won't remove all
data from the database right after you launched it. Entries will be
removed from ``index_data`` and ``metrics`` tables but not from
``data_bin``.

The main reason for that is ``data_bin`` quickly stores a huge amount
of data and uses the ``MyISAM`` engine which doesn't support per-row
locking. If you try to remove too many entries simultaneously, you
could block all your database for several hours.

Anyway, it doesn't mean the data will stay into your database
indefinitely. It will be removed in the future, depending on you data
retention policy.


No graph seems to be generated, what should I look into?
========================================================

There are various things to check when RRDs don't seem to be generated.


Disk space
----------

By default, the graph files (.rrd) are stored in ``/var/lib/centreon/metrics``, 
it is obviously necessary to have enough space in your filesystem.


Permissions
-----------

Can the .rrd files be written in the ``/var/lib/centreon/metrics`` directory?
Process that usally writes in this directory is either ``centstorage`` or ``cbd``.


Plugins
-------

Does your plugin return the correct output? Refer to the 
:ref:`Plugin API documentation<centreon-engine:centengine_plugin_api>` 
for more information

If you are using NDOUtils
-------------------------

Make sure that ``centstorage`` is running::

  $ /etc/init.d/centstorage status
  centstorage (pid  30276) is running...


The ``service-perfdata`` path of your poller must be correctly set in 
``Configuration`` > ``Centreon``

Also this path must match with the one in the *process-service-perfdata* plugin::

  $ head -43 plugin_path/process-service-perfdata | tail -1
  PERFFILE="/var/log/centreon-engine/service-perfdata"


If you are using Centreon Broker
--------------------------------

Centreon Broker must be configured properly, refer to this 
:ref:`documentation <centreon_broker_wizards>` for more information.

The cbd rrd daemon must be running::

  $ /etc/init.d/cbd status
   * cbd_central-rrd is running

Make sure to have the *Start script for broker daemon* parameter filled in 
``Administration`` > ``Options`` > ``Monitoring``.
