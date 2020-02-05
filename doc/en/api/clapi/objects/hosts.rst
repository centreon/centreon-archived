.. _hosts:

=====
Hosts
=====

Overview
--------

Object name: HOST

Show
----

In order to list available hosts, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a show
  id;name;alias;address;activate
  82;sri-dev1;dev1;192.168.2.1;1
  83;sri-dev2;dev2;192.168.2.2;1
  84;sri-dev3;dev3;192.168.2.3;0
  85;sri-dev4;dev4;192.168.2.4;1
  86;sri-dev5;dev5;192.168.2.5;1
  87;sri-dev6;dev6;192.168.2.6;1
  94;sri-dev7;dev7;192.168.2.7;1
  95;sri-dev8;dev8;192.168.2.8;1

Columns are the following :

=========== ===================================
Column      Description
=========== ===================================
ID          ID of host

Name        Host name

Alias       Host alias

IP/Address  IP of host

Activate    1 when enabled, 0 when disabled
=========== ===================================


Add
---

In order to add a host, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a ADD -v "test;Test host;127.0.0.1;generic-host;central;Linux"

Required parameters:

===== ==================================
Order Description
===== ==================================
1     Host name

2     Host alias

3     Host IP address

4     Host templates; for multiple
      definitions, use delimiter **|**

5     Instance name (poller)

6     Hostgroup; for multiple
      definitions, use delimiter **|**
===== ==================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Del
---

In order to delete one host, use the **DEL** action. You have to list the available hosts in order to identify the one you want to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a DEL -v "test"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

.. _clapi-hosts-setparam:

Setparam
--------

In order to change parameters on a host configuration, use the **SETPARAM** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a setparam -v "test;alias;Development test "
  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a setparam -v "test;address;192.168.1.68"
  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a setparam -v "test;check_period;24x7"
  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a setparam -v "test;timezone;Europe/Berlin"


You may edit the following parameters:

==================================== =================================================================================
Parameter	                     Description
==================================== =================================================================================
geo_coords	                     Geo coordinates

2d_coords	                     2D coordinates (used by statusmap)

3d_coords	                     3D coordinates (used by statusmap)

action_url	                     Action URL

activate	                     Whether or not host is enabled

active_checks_enabled	             Whether or not active checks are enabled

acknowledgement_timeout            Acknowledgement timeout (in seconds)

address	                             Host IP Address

alias	                             Alias

check_command	                     Check command

check_command_arguments	             Check command arguments

check_interval	                     Normal check interval

check_freshness	                     Enables check freshness

check_period	                     Check period

contact_additive_inheritance         Enables contact additive inheritance

cg_additive_inheritance              Enables contactgroup additive inheritance

event_handler	                     Event handler command

event_handler_arguments	             Event handler command arguments

event_handler_enabled	             Whether or not event handler is enabled

first_notification_delay	     First notification delay (in seconds)

flap_detection_enabled	             Whether or not flap detection is enabled

flap_detection_options	             Flap detection options 'o' for Up, 'd' for Down, 'u' for Unreachable

host_high_flap_threshold             High flap threshold

host_low_flap_threshold              Low flap threshold

icon_image	                     Icon image

icon_image_alt	                     Icon image text

max_check_attempts                   Maximum number of attempt before a HARD state is declared

name	                             Host name

notes	                             Notes

notes_url	                     Notes URL

notifications_enabled	             Whether or not notification is enabled

notification_interval	             Notification interval

notification_options	             Notification options

notification_period	             Notification period

recovery_notification_delay          Recovery notification delay

obsess_over_host	             Whether or not obsess over host option is enabled

passive_checks_enabled	             Whether or not passive checks are enabled

retain_nonstatus_information         Whether or not there is non-status retention

retain_status_information	     Whether or not there is status retention

retry_check_interval                 Retry check interval

snmp_community                       Snmp Community

snmp_version                         Snmp version

stalking_options	             Comma separated options: 'o' for OK, 'd' for Down, 'u' for Unreachable

statusmap_image	                     Status map image (used by statusmap

host_notification_options            Notification options (d,u,r,f,s)

timezone                             Timezone
==================================== =================================================================================


.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Getparam
--------

In order to get specific parameters on a host configuration, use the **GETPARAM** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a getparam -v "test;alias"
  alias
  test
  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a getparam -v "test;alias|alia|timezone"
  Object not found:alia
  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a getparam -v "test;alias|address|timezone"
  alias;address;timezone
  test;192.168.56.101;Europe/Berlin

You may edit the following parameters:

==================================== =================================================================================
Parameter	                     Description
==================================== =================================================================================
2d_coords	                     2D coordinates (used by statusmap)

3d_coords	                     3D coordinates (used by statusmap)

action_url	                     Action URL

activate	                     Whether or not host is enabled

active_checks_enabled	             Whether or not active checks are enabled

acknowledgement_timeout            Acknowledgement timeout (in seconds)

address	                             Host IP Address

alias	                             Alias

check_command	                     Check command

check_command_arguments	             Check command arguments

check_interval	                     Normal check interval

check_freshness	                     Enables check freshness

check_period	                     Check period

contact_additive_inheritance         Enables contact additive inheritance

cg_additive_inheritance              Enables contactgroup additive inheritance

event_handler	                     Event handler command

event_handler_arguments	             Event handler command arguments

event_handler_enabled	             Whether or not event handler is enabled

first_notification_delay	     First notification delay (in seconds)

flap_detection_enabled	             Whether or not flap detection is enabled

flap_detection_options	             Flap detection options 'o' for Up, 'd' for Down, 'u' for Unreachable

host_high_flap_threshold             High flap threshold

host_low_flap_threshold              Low flap threshold

icon_image	                     Icon image

icon_image_alt	                     Icon image text

max_check_attempts                   Maximum number of attempt before a HARD state is declared

name	                             Host name

notes	                             Notes

notes_url	                     Notes URL

notifications_enabled	             Whether or not notification is enabled

notification_interval	             Notification interval

notification_options	             Notification options

notification_period	             Notification period

recovery_notification_delay          Recovery notification delay

obsess_over_host	             Whether or not obsess over host option is enabled

passive_checks_enabled	             Whether or not passive checks are enabled

process_perf_data	             Process performance data command

retain_nonstatus_information         Whether or not there is non-status retention

retain_status_information	     Whether or not there is status retention

retry_check_interval                 Retry check interval

snmp_community                       Snmp Community

snmp_version                         Snmp version

stalking_options	             Comma separated options: 'o' for OK, 'd' for Down, 'u' for Unreachable

statusmap_image	                     Status map image (used by statusmap

host_notification_options            Notification options (d,u,r,f,s)

timezone                             Timezone
==================================== =================================================================================


Setinstance
-----------

In order to set the instance from which a host will be monitored, use the **SETINSTANCE** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a setinstance -v "Centreon-Server;Poller 1"

Showinstance
------------

To determine the instance from which a host will be monitored, use the **SHOWINSTANCE** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a showinstance -v "Centreon-Server"
  id;name
  2;Poller 1

Getmacro
--------

In order to view the custom macro list of a host, use the **GETMACRO** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a getmacro -v "Centreon-Server"
  macro name;macro value;is_password;description
  $_HOSTMACADDRESS$;00:08:C7:1B:8C:02;0;description of macro

Setmacro
--------

In order to set a custom host macro, use the **SETMACRO** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a setmacro -v "Centreon-Server;warning;80;0;description of macro"
  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a setmacro -v "Centreon-Server;critical;90;0;description of macro"

.. note::
  If the macro already exists, this action will only update the macro value. Otherwise, macro will be created.

Delmacro
--------

In order to delete a macro host, use the **DELMACRO** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a delmacro -v "Centreon-Server;warning"
  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a delmacro -v "Centreon-Server;critical"

Gettemplate
-----------

In order to view the template list of a host, use the **GETTEMPLATE** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a gettemplate -v "Centreon-Server"
  id;name
  2;generic-host
  12;Linux-Servers


Addtemplate and Settemplate
----------------------------

In order to add a host template to an existing host, use the **ADDTEMPLATE** or the **SETTEMPLATE** action, where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a addtemplate -v "Centreon-Server;srv-Linux"
  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a settemplate -v "Centreon-Server;hardware-Dell"

.. note::
  All service templates linked to the new host template will be automatically deployed on the existing host. (no longer the case with version later than 1.3.0, use the 'applytpl' action manually)

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Deltemplate
-----------

In order to remove a host template to an existing host, use the **DELTEMPLATE** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a deltemplate -v "test;srv-Linux|hardware-Dell"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Applytpl
--------

When a template host undergoes modified link-level service template, the change is not automatically reflected in hosts belonging to that template. For the change to take effect, it must then re-apply the template on this host. For this, use the **APPLYTPL** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a applytpl -v "test"
  All new services are now created.

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Getparent
---------
In order to view the parents of a host, use the **GETPARENT** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a getparent -v "Centreon-Server"
  id;name
  43;server-parent1
  44;server-parent2

Addparent and Setparent
-----------------------

In order to add a host parent to an host, use the **ADDPARENT** or **SETPARENT** actions where *add* will append and *set* will overwrite the previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a addparent -v "host;hostParent1"
  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a setparent -v "host;hostParent1|hostParent2"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Delparent
---------

In order to remove a parent, use the **DELPARENT** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a delparent -v "Centreon-Server;server-parent1|server-parent2"


Getcontactgroup
---------------

In order to view the notification contact groups of a host, use the **GETCONTACTGROUP** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a getcontactgroup -v "Centreon-Server"
  id;name
  17;Administrators


Addcontactgroup and Setcontactgroup
-----------------------------------

If you want to add notification contactgroups to a host, use the **ADDCONTACTGROUP** or **SETCONTACTGROUP** actions where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a addcontactgroup -v "Centreon-Server;Contactgroup1"
  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a setcontactgroup -v "Centreon-Server;Contactgroup1|Contactgroup2"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Delcontactgroup
---------------

If you want to remove notification contactgroups from a host, use the **DELCONTACTGROUP** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a delcontactgroup -v "Centreon-Server;Contactgroup2"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Getcontact
----------

In order to view the notification contacts of a host, use the **GETCONTACT** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a getcontact -v "Centreon-Server"
  id;name
  11;guest


Addcontact and Setcontact
-------------------------

If you want to add notification contacts to a host, use the **ADDCONTACT** or **SETCONTACT** actions where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a addcontact -v "Centreon-Server;Contact1"
  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a setcontact -v "Centreon-Server;Contact1|Contact2"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Delcontact
----------

If you want to remove a notification contacts from a host, use the **DELCONTACT** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a delcontact -v "Centreon-Server;Contact2"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Gethostgroup
------------
In order to view the hostgroups that are tied to a host, use the **GETHOSTGROUP** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a gethostgroup -v "Centreon-Server"
  id;name
  9;Linux-Servers


Addhostgroup and Sethostgroup
-----------------------------

If you want to tie hostgroups to a host, use the **ADDHOSTGROUP** or **SETHOSTGROUP** actions where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a addhostgroup -v "Centreon-Server;Hostgroup1"
  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a sethostgroup -v "Centreon-Server;Hostgroup1|Hostgroup2"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Delhostgroup
------------

If you want to remove hostgroups from a host, use the **DELHOSTGROUP** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a delhostgroup -v "Centreon-Server;Hostgroup2"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Setseverity
-----------

In order to associate a severity to a host, use the **SETSEVERITY** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a setseverity -v "Centreon-Server;Critical"


Required parameters:

===== ==================================
Order Description
===== ==================================
1     Host name

2     Severity name
===== ==================================


Unsetseverity
-------------

In order to remove the severity from a host, use the **UNSETSEVERITY** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a unsetseverity -v "Centreon-Server"


Required parameters:

===== ==================================
Order Description
===== ==================================
1     Host name
===== ==================================


Enable
------

In order to enable an host, use the **ENABLE** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a enable -v "test"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Disable
-------

In order to disable a host, use the **DISABLE** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a disable -v "test"

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.
