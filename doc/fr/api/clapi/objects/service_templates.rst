=================
Service templates
=================

Overview
--------

Object name: **STPL**

Show
----

In order to list available service, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a show
  id;description;check command;check command arg;normal check interval;retry check interval;max check attempts;active checks enabled;passive checks enabled
  1;generic-service;generic-service;;;5;1;3;1;0
  3;Ping-LAN;Ping;check_centreon_ping;!3!200,20%!400,50%;;;;2;2
  4;Ping-WAN;Ping;check_centreon_ping;!3!400,20%!600,50%;;;;2;2
  5;SNMP-DISK-/;Disk-/;check_centreon_remote_storage;!/!80!90;;;;2;2
  6;SNMP-DISK-/var;Disk-/var;check_centreon_remote_storage;!/var!80!90;;;;2;2
  7;SNMP-DISK-/usr;Disk-/usr;check_centreon_remote_storage;!/usr!80!90;;;;2;2
  8;SNMP-DISK-/home;Disk-/home;check_centreon_remote_storage;!/home!80!90;;;;2;2
  9;SNMP-DISK-/opt;Disk-/opt;check_centreon_remote_storage;!/opt!80!90;;;;2;2

Columns are the following :

====================== =====================================================
Order	               Description
====================== =====================================================
1	               Service ID

2	               Service Description

3	               Check command

4                      Check command arguments

5                      Normal check interval

6                      Retry check interval

7                      Maximum check attempts

8                      *1* when active checks are enabled, *0* otherwise

9                      *1* when passive checks are enabled, *0* otherwise
====================== =====================================================


Add
---

In order to add a service template, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a add -v "MyTemplate;mytemplate;Ping-LAN" 

The required fields are:

======= =====================================================================
Order	Description
======= =====================================================================
1	Service template description

2       Alias will be used when services are deployed through host templates

3       Service template; Only one service template can be defined
======= =====================================================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Del
---

In order to remove a service template, use the **DEL** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a del -v "MyTemplate" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Setparam
--------

In order to set a specific parameter for a service template, use the **SETPARAM** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a setparam -v "MyTemplate;max_check_attempts;10" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a setparam -v "MyTemplate;normal_check_interval;2" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a setparam -v "MyTemplate;normal_check_interval;http://192.168.1.2/admincp" 

The required fields that you have pass in options are:

======= ====================================
Order   Description
======= ====================================
1       service template description

2       parameter that you want to update

3       new paramater value
======= ====================================


Parameters that may be modified:

================================== ==========================================================================================================
Parameter	                   Description
================================== ==========================================================================================================
activate                           1 when service is enabled, 0 otherwise

description	                   Service template description

alias	                           Service template alias

template                           Name of the service template

is_volatile	                   1 when service is volatile, 0 otherwise

check_period                       Name of the check period

check_command                      Name of the check command

check_command_arguments            Arguments that go along with the check command,
                                   prepend each argument with the '!' characteri

max_check_attempts                 Maximum number of attempt before a HARD state is declared

normal_check_interval              value in minutes

retry_check_interval               value in minutes

active_checks_enabled	           1 when active checks are enabled, 0 otherwise

passive_checks_enabled	           1 when passive checks are enabled, 0 otherwise

contact_additive_inheritance       Enables contact additive inheritance=

cg_additive_inheritance            Enables contactgroup additive inheritance

notification_interval              value in minutes

notification_period                Name of the notification period

notification_options               Status linked to notifications

first_notification_delay           First notification delay in seconds

recovery_notification_delay        Recovery notification delay

parallelize_check	           1 when parallelize checks are enabled, 0 otherwise

obsess_over_service	           1 when obsess over service is enabled, 0 otherwise

check_freshness	                   1 when check freshness is enabled, 0 otherwise

freshness_threshold	           Service freshness threshold in seconds

event_handler_enabled	           1 when event handler is enabled, 0 otherwise

flap_detection_enabled	           1 when flap detection is enabled, 0 otherwise

process_perf_data	           1 when process performance data is enabled, 0 otherwise

retain_status_information	   1 when status information is retained, 0 otherwise

retain_nonstatus_information	   1 when non status information is retained, 0 otherwise

stalking_options	           Comma separated options: 'o' for OK, 'w' for Warning, 'u' for Unknown and 'c' for Critical

event_handler	                   Name of the event handler command

event_handler_arguments	           Arguments that go along with the event handler, prepend each argument with the "!" character

notes	                           Notes

notes_url	                   Notes URL

action_url	                   Action URL

icon_image	                   Icon image

icon_image_alt	                   Icon image alt text

graphtemplate	                   Graph template namei

comment                            Comment

service_notification_options       Notification options (w,u,c,r,f,s)
================================== ==========================================================================================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

.. _addhosttemplate-and-sethosttemplate:

Addhosttemplate and Sethosttemplate
-----------------------------------

You may want to tie a service template to an extra host template. In order to do so, use the **ADDHOSTTEMPLATE** or **SETHOSTTEMPLATE** actions where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a sethosttemplate -v "MyTemplate;generic-host-template" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a addhosttemplate -v "MyTemplate;Linux-Servers" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Delhosttemplate
---------------

In order to remove the relation between a host template and a service template, use the **DELHOSTTEMPLATE** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a delhosttemplate -v "MyTemplate;Linux-Servers" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Getmacro
--------

In order to view the custom macro list of a service template, use the **GETMACRO** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a getmacro -v "MyTemplate" 
  macro name;macro value;description;is_password
  $_SERVICETIME$;80;description of macro1;0
  $_SERVICEPL$;400;description of macro2;0


Setmacro
--------

In order to set a macro for a specific service template use the **SETMACRO** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a setmacro -v "MyTemplate;time;80" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a setmacro -v "MyTemplate;pl;400;description"
  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a setmacro -v "MyTemplate;password;mypassword;;1"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Delmacro
--------

In order to remove a macro from a specific service template, use the **DELMACRO** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a delmacro -v "MyTemplate;time" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a delmacro -v "MyTemplate;pl" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Getcontact
----------

In order to view the contact list of a service template, use the **GETCONTACT** action::

  [root@localhost core]# ./centreon -u admin -p centreon -o STPL -a getcontact -v "MyTemplate" 
  id;name
  28;Contact_1
  29;Contact_2


Addcontact and Setcontact
-------------------------

In order to add a new contact to notification contact list, use **ADDCONTACT** or **SETCONTACT** actions where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a addcontact -v "MyTemplate;User1" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a setcontact -v "MyTemplate;User1|User2" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Delcontact
----------

In order to remove a contact from the notification contact list, use the **DELCONTACT** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a delcontact -v "MyTemplate;User1" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a delcontact -v "MyTemplate;User2" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Getcontactgroup
---------------

In order to view the contactgroup list of a service template, use the **GETCONTACTGROUP** action::

  [root@localhost core]# ./centreon -u admin -p centreon -o STPL -a getcontactgroup -v "MyTemplate" 
  id;name
  28;ContactGroup_1
  29;ContactGroup_2


Setcontactgroup
---------------

In order to add a new contactgroup to notification contactgroup list, use the **ADDCONTACTGROUP** or **SETCONTACTGROUP** actions where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a addcontactgroup -v "MyTemplate;Group1" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a setcontactgroup -v "MyTemplate;Group1|Group2" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Delcontactgroup
---------------

In order to remove a contactgroup from the notification contactgroup list, use the **DELCONTACTGROUP** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a delcontactgroup -v "MyTemplate" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a delcontactgroup -v "MyTemplate;Group1" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Gettrap
--------

In order to view the trap list of a service template, use the **GETTRAP** action::

  [root@localhost core]# ./centreon -u admin -p centreon -o "STPL" -a gettrap -v "Ping-LAN" 
  id;name
  48;ciscoConfigManEvent
  39;ospfVirtIfTxRetransmit

Settrap
--------

In order to add a trap to a service template, use the **ADDTRAP** or **SETTRAP** actions where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a addtrap -v "Ping-LAN;snOspfVirtIfConfigError" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a settrap -v "Ping-LAN;snOspfVirtNbrStateChange|snTrapAccessListDeny" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Deltrap
-------

In order to remove a trap from a service template, use the **DELTRAP** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o STPL -a deltrap -v "Ping-LAN;snOspfVirtIfConfigError" 
