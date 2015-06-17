Host
====

Overview
--------

Object name: **centreon-configuration:host**

Available parameters are the following:

============================== ================================
Parameter                      Description
============================== ================================
**--name**                     Host name

**--address**                  Host address

**--disable**                  Enable (0 or 1)

--alias                        Host alias

--host-templates               Linked host templates slug

--check-interval               Check interval

--retry-check-interval         Retry interval

--max-check-attempts           Max check attempts

--poller                       Poller slug

--timezone                     Timezone slug

--icon                         Host icon

--environment                  Environment slug

--timeperiod                   Timeperiod slug

--command                      Check command slug

--active-checks-enabled        Active check enable (0 or 1)

--comment                      Host comments

--obsess                       TODO

--check-freshness              Freshness enable (0 or 1)

--freshness-threshold          Freshness threshold

--flap-detection-enabled       Flap detection enable (0 or 1)

--low-flap-threshold           Low flap detection threshold

--high-flap-threshold          High flap detection threshold

--flap-detection-enabled       Flap detection options

--eventhandler-enabled         Event handler enable (0 or 1)

--command2                     Event handler command

--parents                      Host parents slug

--childs                       Host children slug
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

  ./centreonConsole centreon-configuration:host:show --host "host1"
  id: 1
  command_command_id:
  command_command_id_arg1:
  timeperiod_tp_id:
  command_command_id2:
  command_command_id_arg2:
  name: host1
  sluge: host1
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

  ./centreonConsole centreon-configuration:host:create --name "host1" --enabled --address "127.0.0.1" ---max-check-attempts "5"
  Object successfully created

Update
------

In order to update a host, use **update** action::

  ./centreonConsole centreon-configuration:host:update --host "host1" --host-templates 'host-tpl' --poller 'central'
  Object successfully updated

Delete
------

In order to delete a host, use **delete** action::

  ./centreonConsole centreon-configuration:host:delete --host "host1"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a host, use **duplicate** action::

  ./centreonConsole centreon-configuration:host:duplicate --host "host1"
  Object successfully duplicated

List tag
--------

In order to list tags of a host, use **listTag** action::

  ./centreonConsole centreon-configuration:host:listTag --host "host1"
  tag1

Add tag
-------

In order to add a tag to a host, use **addTag** action::

  ./centreonConsole centreon-configuration:host:addTag --host "host1" --tag "tag1"

Remove tag
----------

In order to remove a tag from a host, use **removeTag** action::

  ./centreonConsole centreon-configuration:host:removeTag --host "host1" --tag "tag1"



List Macro
----------

In order to list macros of a host, use **listMacro** action::

  ./centreonConsole centreon-configuration:host:listMacro --host "host1"
  tag1

Add Macro
---------

In order to add a macro to a host, use **addMacro** action::

  ./centreonConsole centreon-configuration:host:addMacro --host "host1" --name "macro1name" --value "macro1value" --hidden 0

Remove Macro
------------

In order to remove a macro from a host, use **removeMacro** action::

  ./centreonConsole centreon-configuration:host:removeMacro --host "host1" --macro "macro1name"

Update Macro
------------

In order to update a macro from a host, use **updateMacro** action::

  ./centreonConsole centreon-configuration:host:updateMacro --host "host1" --macro "macro1name" --value "macro1newvalue" --name"macro1newname" --hidden 1

