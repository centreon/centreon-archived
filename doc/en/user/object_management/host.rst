Host
====

Overview
--------

Object name: **centreon-configuration:host**

Available parameters are the following:

============================== ================================
Parameter                      Description
============================== ================================
**host_name**                  Host name

**host_address**               Host address

**host_activate**              Enable (0 or 1)

host_alias                     Host alias

host_hosttemplates             Linked host templates id

host_check_interval            Check interval

host_retry_interval            Retry interval

host_max_check_attempts        Max check attempts

poller_id                      Poller id

timezone_id                    Timezone id

host_icon                      Host icon

environment_id                 Environment id

organization_id                Organization id

timeperiod_tp_id               Timeperiod id

command_command_id             Check command id

host_active_checks_enabled     Active check enable (0 or 1)

host_passive_checks_enabled    Passive check enable (0 or 1)

host_comment                   Host comments

host_obsess_over_host          TODO

host_check_freshness           Freshness enable (0 or 1)

host_freshness_threshold       Freshness threshold

host_flap_detection_enabled    Flap detection enable (0 or 1)

host_low_flap_threshold        Low flap detection threshold

host_high_flap_threshold       High flap detection threshold

flap_detection_options         Flap detection options

host_snmp_community            Host snmp community

host_snmp_version              Host snmp version

host_location                  TODO

host_comment                   Host comments

host_event_handler_enabled     Event handler enable (0 or 1)

command_command_id2            Event handler command

host_parents                   Host parents id

host_childs                    Host children id

activate                       Host enable (0 or 1)
============================== ================================

List
----

In order to list hosts, use **list** action::

  ./centreonConsole centreon-configuration:host:list
  id;name;description;address;activate
  1;host1;host1;127.0.0.1;1

Columns are the following:

============== ======================
Column         Description
============== ======================
id             Host id

name           Host name

description    Host description

address        Ip address

activate       Enable (0 or 1)
============== ======================

Show
----

In order to show a host, use **show** action::

  ./centreonConsole centreon-configuration:host:show object="host[host1]"
  id: 1
  command_command_id:
  command_command_id_arg1:
  timeperiod_tp_id:
  command_command_id2:
  command_command_id_arg2:
  name: host1
  description: host1
  address: 127.0.0.1
  host_max_check_attempts:
  host_check_interval:
  host_retry_check_interval:
  host_active_checks_enabled:
  host_passive_checks_enabled:
  host_checks_enabled:
  initial_state:
  host_obsess_over_host:
  host_check_freshness:
  host_freshness_threshold:
  host_event_handler_enabled:
  host_low_flap_threshold:
  host_high_flap_threshold:
  host_flap_detection_enabled:
  flap_detection_options:
  host_snmp_community:
  host_snmp_version:
  host_location: 0
  host_comment:
  host_register: 1
  activate: 1
  organization_id: 1
  environment_id:
  poller_id: 1
  timezone_id:

Create
------

In order to create a host, use **create** action::

  ./centreonConsole centreon-configuration:host:create params="host_name[host1];host_activate[1];host_address[127.0.0.1];host_max_check_attempts[5]"
  Object successfully created

Update
------

In order to update a host, use **update** action::

  ./centreonConsole centreon-configuration:host:update object="host[host1]":params="host_hosttemplates[1];poller_id[1]"
  Object successfully updated

Delete
------

In order to delete a host, use **delete** action::

  ./centreonConsole centreon-configuration:host:delete object="host[host1]"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a host, use **duplicate** action::

  ./centreonConsole centreon-configuration:host:duplicate object="host[host1]"
  Object successfully duplicated

List tag
--------

In order to list tags of a host, use **listTag** action::

  ./centreonConsole centreon-configuration:host:listTag object="host[host1]"
  tag1

Add tag
-------

In order to add a tag to a host, use **addTag** action::

  ./centreonConsole centreon-configuration:host:addTag object="host[host1]":tag="tag1"

Remove tag
----------

In order to remove a tag from a host, use **removeTag** action::

  ./centreonConsole centreon-configuration:host:removeTag object="host[host1]":tag="tag1"



List Macro
----------

In order to list macros of a host, use **listMacro** action::

  ./centreonConsole centreon-configuration:host:listMacro object="host[host1]"
  tag1

Add Macro
---------

In order to add a macro to a host, use **addMacro** action::

  ./centreonConsole centreon-configuration:host:addMacro object="host[host1]":params="name[macro1name];value[macro1value];hidden[0]"

Remove Macro
------------

In order to remove a macro from a host, use **removeMacro** action::

  ./centreonConsole centreon-configuration:host:removeMacro object="host[host1]":macro="macro1name"

Update Macro
------------

In order to update a macro from a host, use **updateMacro** action::

  ./centreonConsole centreon-configuration:host:updateMacro object="host[host1]":macro="macro1name":params="value[macro1newvalue];name[macro1newname];hidden[1];"

