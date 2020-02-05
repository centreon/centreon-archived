================================
Centreon administration platform
================================

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

My dashboard on several days is undetermined, what should I look into?
======================================================================

This is a bug from some mysql versions with partitioned tables
(https://bugs.mysql.com/bug.php?id=70588).
Replacing '=' by LIKE in queries fixes the problem but reduces performance.

We therefore recommend to update your SGBD :
MySQL 5.5.36, 5.6.16, 5.7.4,
MariaDB  10.0.33, 10.1.29, 10.2.10.

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
Process that usually writes in this directory is either ``centstorage`` or ``cbd``.


Plugins
-------

Does your plugin return the correct output? Refer to the 
:ref:`Plugin API documentation<centreon-engine:centengine_plugin_api>` 
for more information


Centreon Broker
---------------

Centreon Broker must be configured properly, refer to this 
:ref:`documentation <advance_conf_broker>` for more information.

The cbd rrd daemon must be running::

    # systemctl status cbd
    ● cbd.service - Centreon Broker watchdog
       Loaded: loaded (/etc/systemd/system/cbd.service; enabled; vendor preset: disabled)
       Active: active (running) since mer. 2018-07-18 17:46:03 CEST; 2 months 9 days ago
      Process: 21410 ExecReload=/bin/kill -HUP $MAINPID (code=exited, status=0/SUCCESS)
     Main PID: 9537 (cbwd)
       CGroup: /system.slice/cbd.service
               ├─9537 /usr/sbin/cbwd /etc/centreon-broker/watchdog.json
               ├─9539 /usr/sbin/cbd /etc/centreon-broker/central-rrd.json
               └─9540 /usr/sbin/cbd /etc/centreon-broker/central-broker.json
