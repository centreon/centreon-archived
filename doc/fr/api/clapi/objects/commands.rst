========
Commands
========

Overview
--------

Object name: **CMD** 

Show
----

In order to list available commands, use **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CMD -a show 
  id;name;type;line
  1;check-ping;check;$USER1$/check_ping -H $HOSTADDRESS$ -w $ARG1$ -c $ARG2$
  2;check_dummy;check;$USER1$/check_dummy -o $ARG1$ -s $ARG2$
  [...]

Columns are the following:

============== =================================================
Column         Description
============== =================================================
Command ID

Command name

Command type   *check*, *notif*, *misc* or *discovery*

Command line   System command line that will be run on execution
============== =================================================

Add
---

In order to add a command use **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CMD -a ADD -v 'check-host-alive;check;$USER1$/check_ping -H $HOSTADDRESS$ -w 3000.0,80% -c 5000.0,100% -p 1'

Required columns are the following:

============== =================================================
Column         Description
============== =================================================
Command name

Command type   *check*, *notif*, *misc* or *discovery*

Command line   System command line that will be run on execution
============== =================================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Del
---

If you want to remove a command use **DEL** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CMD -a del -v 'check-host-alive'

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Setparam
--------

If you want to change a specific parameters for a command, use the **SETPARAM** command::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CMD -a setparam -v 'check-host-alive;type;notif'
  [root@centreon ~]# ./centreon -u admin -p centreon -o CMD -a setparam -v 'check-host-alive;name;check-host-alive2'

Parameters that you can change are the following:

=========== ===================================
Parameter   Description
=========== ===================================
name        Name of command

line        Command line

type        *check*, *notif*, *misc* or *discovery*

graph       Graph template applied on command

example     Example of arguments (i.e: !80!90)

comment     Comments regarding the command
=========== ===================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Getargumentdescr
----------------

To retrieve the argument descriptions for a command, use the **getargumentdescr** command:

  [root@centreon ~]# ./centreon -u admin -p centreon -o CMD -a getargumentdesc -v 'test-cmd'
  name;description
  ARG0;First Argument
  ARG1;Second Argument


Setargumentdescr
----------------

If you want to change all arguments descriptions for a command, use the **setargumentdescr** command::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CMD -a setargumentdescr -v 'check_centreon_ping;ARG1:count;ARG2:warning;ARG3:critical'

