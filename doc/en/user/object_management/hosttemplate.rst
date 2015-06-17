Host template
=============

Overview
--------

Object name: **centreon-configuration:hostTemplate**

Available parameters are the following:

============================== ================================
Parameter                      Description
============================== ================================
**--name**                     Host name

**--enabled**                  Enable (0 or 1)

--alias                        Host alias

--host-templates               Linked host templates slug

--service-templates            Linked service templates slug

--check-interval               Check interval

--retry-check-interval         Retry interval

--max-check-attempts           Max check attempts

--poller                       Poller slug

--timezone                     Timezone slug

--icon                         Host icon

--environment                  Environment slug

--timeperiod                   Timeperiod slug

--command                      Check command slug

--comment                      Host comments

--obsess                       TODO

--check-freshness              Freshness enable (0 or 1)

--freshness-threshold          Freshness threshold

--flap-detection-enabled       Flap detection enable (0 or 1)

--low-flap-threshold           Low flap detection threshold

--high-flap-threshold          High flap detection threshold

--flap-detection-enabled       Flap detection options

--comment                      Host comments

--eventhandler-enabled         Event handler enable (0 or 1)

--command2                     Event handler command

--parents                      Host parents slug

--childs                       Host children slug
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

  ./centreonConsole centreon-configuration:hostTemplate:show --hosttemplate "HT1"
  id: 2
  command_command_id:
  command_command_id_arg1:
  timeperiod_tp_id:
  command_command_id2:
  command_command_id_arg2:
  name: HT1
  slug: HT1
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

  ./centreonConsole centreon-configuration:hostTemplate:create --name "HT1" --enabled
  Object successfully created

Update
------

In order to update a host template, use **update** action::

  ./centreonConsole centreon-configuration:hostTemplate:update --hosttemplate "HT1" --alias "host template 1"
  Object successfully updated

Delete
------

In order to delete a host template, use **delete** action::

  ./centreonConsole centreon-configuration:hostTemplate:delete --hosttemplate "HT1"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a host template, use **duplicate** action::

  ./centreonConsole centreon-configuration:hostTemplate:duplicate --hosttemplate "HT1"
  Object successfully duplicated

List tag
--------

In order to list tags of a host template, use **listTag** action::

  ./centreonConsole centreon-configuration:hostTemplate:listTag --hosttemplate "HT1"
  tag1

Add tag
-------

In order to add a tag to a host template, use **addTag** action::

  ./centreonConsole centreon-configuration:hostTemplate:addTag --hostTemplate "HT1" --tag "tag1"

Remove tag
----------

In order to remove a tag from a host template, use **removeTag** action::

  ./centreonConsole centreon-configuration:hostTemplate:removeTag --hostTemplate "HT1" --tag "tag1"

