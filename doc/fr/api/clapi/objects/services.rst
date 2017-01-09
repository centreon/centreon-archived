.. _services:

========
Services
========

Overview
--------

Object name: **SERVICE**

Show
----

In order to list available service, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a show
  host id;host name;id;description;check command;check command arg;normal check interval;retry check interval;max check attempts;active checks enabled;passive checks enabled;activate
  14;Centreon-Server;19;Disk-/;;;;;;2;2;1
  14;Centreon-Server;20;Disk-/home;;;;;;2;2;1
  14;Centreon-Server;21;Disk-/opt;;;;;;2;2;1
  14;Centreon-Server;22;Disk-/usr;;;;;;2;2;1
  14;Centreon-Server;23;Disk-/var;;;;;;2;2;1
  14;Centreon-Server;151;Load;;;;;;2;2;1
  14;Centreon-Server;25;Memory;;;;;;2;2;1
  14;Centreon-Server;26;Ping;;;;;;2;2;0
  14;Centreon-Server;40;dummy;check_centreon_dummy;!2!critical;;;;2;2;1

Columns are the following:

============================ ===================================================
Column	                     Description
============================ ===================================================
Host ID	                     Host ID

Host name	             Host name

Service ID	             Service ID

Service description	     Service description

Check Command	             Check command

Command arguments	     Check command arguments

Normal check interval	     Normal check interval

Retry check interval	     Retry check interval

Max check attempts	     Maximum check attempts

Active check enable	     *1* when active checks are enabled, *0* otherwise

Passive check enable	     *1* when passive checks are enabled, *0* otherwise

Activate                     *1* when enabled, *0* when disabled
============================ ===================================================


Add
---

In order to add a service, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a add -v "Host-Test;ping;Ping-LAN" 

The required fields are:

======== ==================================================================
Order 	 Description
======== ==================================================================
1	 Host name

2	 Service description

3	 Service template - Only one service template can be defined
======== ==================================================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.



Del
---

In order to remove a service, use the **DEL** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a del -v "test;ping" 

The required fields are:

========= ============================================================
Order     Description
========= ============================================================
1         Host name

2         Service description
========= ============================================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Setparam
--------

In order to set a specific paremeter for a particular service, use the **SETPARAM** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a setparam -v "test;ping;max_check_attempts;10" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a setparam -v "test;ping;normal_check_interval;2" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a setparam -v "test;ping;normal_check_interval;10" 

The required fields are:

========= ============================================================
Order     Description
========= ============================================================
1         Host name

2         Service description

3         Paramater that you want to update

4         New parameter value
========= ============================================================

Parameters that may be modified:

================================ =============================================================
Parameter	                 Description
================================ =============================================================
activate	                 *1* when service is enabled, 0 otherwise

description	                 Description

template                         Name of the service template

is_volatile	                 *1* when service is volatile, *0* otherwise

check_period                     Name of the check period

check_command                    Name of the check command

check_command_arguments          Arguments that go along with the check command,
                                 prepend each argument with the '!' characteri

max_check_attempts		 Maximum number of attempt before a HARD state is declared

normal_check_interval		 value in minutes

retry_check_interval		 value in minutes

active_checks_enabled	         *1* when active checks are enabled, *0* otherwise

passive_checks_enabled	         *1* when passive checks are enabled, *0* otherwise

notifications_enabled            *1* when notification is enabled, *0* otherwise

contact_additive_inheritance     Enables contact additive inheritance

cg_additive_inheritance              Enables contactgroup additive inheritance

notification_interval            value in minutes

notification_period              Name of the notification period

notification_options             Status linked to notifications

first_notification_delay           First notification delay in seconds

parallelize_checks	         *1* when parallelize checks are enabled, *0* otherwise

obsess_over_service	         *1* when obsess over service is enabled, *0* otherwise

check_freshness	                 *1* when check freshness is enabled, *0* otherwise

freshness_threshold              Value in seconds

event_handler_enabled	         *1* when event handler is enabled, *0* otherwise

flap_detection_enabled	         *1* when flap detection is enabled, *0* otherwise

process_perf_data	         *1* when process performance data is enabled, *0* otherwise

retain_status_information	 *1* when status information is retained, *0* otherwise

retain_nonstatus_information	 *1* when non status information is retained, *0* otherwise

event_handler	                 Name of the event handler command

event_handler_arguments	         Arguments that go along with the event handler, 
                                 prepend each argument with the '!' character

flap_detection_options	         Flap detection options

notes	                         Notes

notes_url	                 Notes URL

action_url	                 Action URL

icon_image	                 Icon image

icon_image_alt	                 Icon image alt text

comment                          Comment

service_notification_options     Notification options (w,u,c,r,f,s)
================================ =============================================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Addhost and Sethost
-------------------

You may want to tie a service to an extra host. In order to do so, use the **ADDHOST** or **SETHOST** actions where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a sethost -v "host1;ping;host2" 

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a addhost -v "host1;ping;host2" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Delhost
-------

In order to remove the relation between a host and a service, use the **DELHOST** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a delhost -v "host1;ping;host2" 

The service ping which was originally linked to host1 and host2 is now only linked to host1.

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Getmacro
--------

In order to view the custom macro list of a service, use the **GETMACRO** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a getmacro -v "host1;ping" 
  macro name;macro value;is_password;description
  $_SERVICETIME$;80;0;description of macro
  $_SERVICEPL$;400;0;description of macro


Setmacro
--------

In order to set a macro for a specific service use the **SETMACRO** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a setmacro -v "test;ping;time;80;0;description of macro" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a setmacro -v "test;ping;pl;400;0;description of macro" 

.. note::
 You need to generate your configuration file and restart monitoring engine in order to apply changes.


Delmacro
--------

In order to remove a macro from a specific service use the **DELMACRO** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a delmacro -v "test;ping;time" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a delmacro -v "test;ping;pl" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Setseverity
-----------

In order to associate a severity to a service, use the **SETSEVERITY** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a setseverity -v "Centreon-Server;ping;Critical" 


Required parameters:

===== ==================================
Order Description
===== ==================================
1     Host name

2     Service description

3     Severity name
===== ==================================


Unsetseverity
-------------

In order to remove the severity from a service, use the **UNSETSEVERITY** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a unsetseverity -v "Centreon-Server;ping" 


Required parameters:

===== ==================================
Order Description
===== ==================================
1     Host name

2     Service description
===== ==================================


Getcontact
----------

In order to view the contact list of a service, use the **GETCONTACT** action::

  [root@localhost core]# ./centreon -u admin -p centreon -o "SERVICE" -a getcontact -v "Centreon-Server;Ping" 
  id;name
  28;Contact_1
  29;Contact_2


Addcontact and Setcontact
-------------------------

In order to add a new contact to notification contact list, use the **ADDCONTACT** or **SETCONTACT** actions where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a addcontact -v "test;ping;User1" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a setcontact -v "test;ping;User1|User2"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Delcontact
----------

In order to remove a contact from the notification contact list, use the **DELCONTACT** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a delcontact -v "test;ping;User1" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a delcontact -v "test;ping;User2" 

.. note::

  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Getcontactgroup
---------------

In order to view the contact group list of a service, use the **GETCONTACTGROUP** action::

  [root@localhost core]# ./centreon -u admin -p centreon -o "SERVICE" -a getcontactgroup -v "Centreon-Server;Ping" 
  id;name
  28;ContactGroup_1
  29;ContactGroup_2


Addcontactgroup and Setcontactgroup
-----------------------------------

In order to add a new contactgroup to notification contactgroup list, use the **ADDCONTACTGROUP** or **SETCONTACTGROUP** actions where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a addcontactgroup -v "test;ping;Group1" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a setcontactgroup -v "test;ping;Group1|Group2" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Delcontactgroup
---------------

In order to remove a contactgroup from the notification contactgroup list, use **DELCONTACTGROUP** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a delcontactgroup -v "test;ping;Group1" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a delcontactgroup -v "test;ping;Group2" 


.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Gettrap
-------

In order to view the trap list of a service, use the **GETTRAP** action::

  [root@localhost core]# ./centreon -u admin -p centreon -o "SERVICE" -a gettrap -v "Centreon-Server;Ping" 
  id;name
  48;ciscoConfigManEvent
  39;ospfVirtIfTxRetransmit


Addtrap and Settrap
-------------------

In order to add a new trap, use the **ADDTRAP** or **SETTRAP** actions where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a addtrap -v "test;ping;snOspfVirtIfConfigError" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a settrap -v "test;ping;snOspfVirtNbrStateChange|snTrapAccessListDeny" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Deltrap
-------

In order to remove a trap from a service, use the **DELTRAP** command::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SERVICE -a deltrap -v "test;ping;snOspfVirtIfConfigError" 
