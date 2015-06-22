Service
=======

Overview
--------

Object name: **centreon-configuration:service**

Available parameters are the following:

===================================== ================================
Parameter                             Description
===================================== ================================
**--description**                     Service description

--template-model-stm                  Linked service template

--icon                                Service icon

--environment                         Service environment

--domain                              Service domain

--type                                Service type (alerting, metrology)

--timeperiod                          Check timeperiod

--command2                            Check command

--max-check-attempts                  Max check attempts

--normal-check-interval               Normal check interval

--retry-check-interval                Retry check interval

--active-checks-enabled               Active checks enabled

--volatile                            Service volatile

--traps                               Linked service traps id

--service-obsess-over-host            TODO

--check-freshness                     Freshness enabled

--freshness-threshold                 Freshness threshold

--flap-detection-enabled              Flap detection enbled

--low-flap-threshold                  Low flap threshold

--high-flap-detection                 High flap threshold

--eventhandler-enabled                Event handler enabled
===================================== ================================

List
----

In order to list services, use **list** action::

  ./centreonConsole centreon-configuration:service:list
  id;name;activate;host_id;host_name
  9;ping;1;2;OS-Linux-SNMP

Columns are the following:

============================== ==========================
Column                         Description
============================== ==========================
id                             Service id

name                           Service name

activate                       Service enabled

host_id                        Host id

host_name                      Host name
============================== ==========================

Show
----

In order to show a service, use **show** action::

  ./centreonConsole centreon-configuration:service:show --host=Centreon-export --service=memory
  id: 13
  template: 5
  command_command_id:
  timeperiod_tp_id:
  command_command_id2:
  name: memory
  service_slug: memory
  service_alias:
  service_is_volatile: 2
  service_max_check_attempts:
  service_normal_check_interval:
  service_retry_check_interval:
  service_active_checks_enabled: 2
  initial_state:
  service_obsess_over_service: 2
  service_check_freshness: 2
  service_freshness_threshold:
  service_event_handler_enabled: 2
  service_low_flap_threshold:
  service_high_flap_threshold:
  service_flap_detection_enabled: 2
  service_comment:
  command_command_id_arg:
  command_command_id_arg2:
  service_locked: 0
  service_register: 1
  activate: 1
  service_type: 1
  organization_id: 1
  environment_id:
  domain_id:

Create
------

In order to create a service, use **create** action::

  ./centreonConsole centreon-configuration:service:create --description 'service1' --host 'Centreon-export'
  Object successfully created

Update
------

In order to update a service, use **update** action::

  ./centreon/external/bin/centreonConsole centreon-configuration:service:update --service 'service1' --host 'Centreon-export' --description 'service2'
  Object successfully updated

Delete
------

In order to delete a service, use **delete** action::

  ./centreonConsole centreon-configuration:service:delete --service 'service1' --host 'Centreon-export'
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a service, use **duplicate** action::

  ./centreonConsole centreon-configuration:service:duplicate --service 'service1' --host 'Centreon-export'
  Object successfully duplicated

List tag
--------

In order to list tags of a service, use **listTag** action::

  ./centreonConsole centreon-configuration:service:listTag --service 'service1' --host 'Centreon-export'
  tag-service-1
  tag1

Add tag
-------

In order to add a tag to a service, use **addTag** action::

  ./centreonConsole centreon-configuration:service:addTag --service 'service1' --host 'Centreon-export' --tag "tag1"
  The tag has been successfully added to the object

Remove tag
----------

In order to remove a tag from a service, use **removeTag** action::

  ./centreonConsole centreon-configuration:service:removeTag --service 'service1' --host 'Centreon-export' --tag "tag1"
  The tag has been successfully removed from the object


List Macro
----------

In order to list macros of a service, use **listMacro** action::

  ./centreonConsole centreon-configuration:service:listMacro --service 'service1' --host 'Centreon-export'
  tag1

Add Macro
---------

In order to add a macro to a service, use **addMacro** action::

  ./centreonConsole centreon-configuration:service:addMacro --service 'service1' --host 'Centreon-export' --name 'macro1name' --value 'macro1value' --hidden '0'

Remove Macro
------------

In order to remove a macro from a service, use **removeMacro** action::

  ./centreonConsole centreon-configuration:service:removeMacro --service "service1" --host "Centreon-export" --macro "macro1name"

Update Macro
------------

In order to update a macro from a service, use **updateMacro** action::

  ./centreonConsole centreon-configuration:service:updateMacro --service "service1" --host "Centreon-export" --macro "macro1name" --value "macro1newvalue" --name "macro1newname" --hidden "1"
