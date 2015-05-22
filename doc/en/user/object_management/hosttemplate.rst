Host template
=============

Overview
--------

Object name: **centreon-configuration:hostTemplate**

Available parameters are the following:

============================== ================================
Parameter                      Description
============================== ================================
**host_name**                  Host name

**host_activate**              Enable (0 or 1)

host_alias                     Host alias

host_hosttemplates             Linked host templates id

hosttemplate_servicetemplates  Linked service templates id

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

In order to list host templates, use **list** action::

  ./centreonConsole centreon-configuration:hostTemplate:list
  id;name;description;activate
  2;HT1;HT1;1

Columns are the following:

============== =========================
Column         Description
============== =========================
id             Host template id

name           Host template name

description    Host template description

activate       Enable (0 or 1)
============== =========================

Show
----

In order to show a host template, use **show** action::

  ./centreonConsole centreon-configuration:hostTemplate:show object="hosttemplate[HT1]"
  id: 2
  command_command_id:
  command_command_id_arg1:
  timeperiod_tp_id:
  command_command_id2:
  command_command_id_arg2:
  name: HT1
  description: HT1
  host_address:
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
  host_register: 0
  activate: 1
  organization_id: 1
  environment_id:
  poller_id:
  timezone_id:

Create
------

In order to create a host template, use **create** action::

  ./centreonConsole centreon-configuration:hostTemplate:create params="host_name[HT1];host_activate[1]"
  Object successfully created

Update
------

In order to update a host template, use **update** action::

  ./centreonConsole centreon-configuration:hostTemplate:update object="hosttemplate[HT1]":params="host_alias[host template 1]"
  Object successfully updated

Delete
------

In order to delete a host template, use **delete** action::

  ./centreonConsole centreon-configuration:hostTemplate:delete object="hosttemplate[HT1]"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a host template, use **duplicate** action::

  ./centreonConsole centreon-configuration:hostTemplate:duplicate object="hosttemplate[HT1]"
  Object successfully duplicated

List tag
--------

In order to list tags of a host template, use **listTag** action::

  ./centreonConsole centreon-configuration:hostTemplate:listTag object="hosttemplate[HT1]"
  tag1

Add tag
-------

In order to add a tag to a host template, use **addTag** action::

  ./centreonConsole centreon-configuration:hostTemplate:addTag object="hostTemplate[HT1]":tag="tag1"

Remove tag
----------

In order to remove a tag from a host template, use **removeTag** action::

  ./centreonConsole centreon-configuration:hostTemplate:removeTag object="hostTemplate[HT1]":tag="tag1"

