Service
=======

Overview
--------

Object name: **centreon-configuration:serviceTemplate**

Available parameters are the following:

===================================== ================================
Parameter                             Description
===================================== ================================
**--description**                     Service template description

--alias                               Service template alias

--host-templates                      Linked host templates id

--command                             Slug name of Check command

--template-model-stm                  Slug name of Linked service template

--normal-check-interval               Normal check interval

--retry-check-interval                Retry check interval

--max-check-attempts                  Max check attempts

--domain                              Slug name of Domain

--icon                                Service template icon

--timeperiod                          Slug name of Timeperiod

--volatile                            Service volatile

--traps                               Slug name of Linked service traps

--service-obsess-over-host            TODO

--check-freshness                     Freshness enable (0 or 1)

--freshness-threshold                 Freshness threshold

--flap-detection-enabled              Flap detection enable (0 or 1)

--low-flap-threshold                  Low flap detection threshold

--high-flap-threshold                 High flap detection threshold

--eventhandler-enabled                Event handler enable (0 or 1)

--command2                            Event handler command
===================================== ================================

List
----

In order to list service templates, use **list** action::

  ./centreonConsole centreon-configuration:serviceTemplate:list
  id;name;status
  2;ST1;1

Columns are the following:

============ ============================
Column       Description
============ ============================
id           Service template id

name         Service template description

status       Enable (0 or 1)
============ ============================

Show
----

In order to show a service, use **show** action::

  ./centreonConsole centreon-configuration:serviceTemplate:show --servicetemplate 'ST1'
  id: 2
  service_template_model_stm_id:
  command_command_id: 4
  timeperiod_tp_id:
  command_command_id2:
  name: ST1
  service_alias: ST1
  display_name:
  service_is_volatile: 2
  service_max_check_attempts:
  service_normal_check_interval:
  service_retry_check_interval:
  service_active_checks_enabled: 2
  service_passive_checks_enabled: 2
  initial_state:
  service_parallelize_check: 2
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
  service_register: 0
  status: 1
  service_type: 1
  organization_id: 1
  environment_id:
  domain_id:

Create
------

In order to create a service template, use **create** action::

  ./centreonConsole centreon-configuration:serviceTemplate:create --description "ST1"
  Object successfully created

Update
------

In order to update a service template, use **update** action::

  ./centreonConsole centreon-configuration:serviceTemplate:update --service-template 'ST1' --alias 'service template 1' --max-check-attempts "4"
  Object successfully updated

Delete
------

In order to delete a service template, use **delete** action::

  ./centreonConsole centreon-configuration:serviceTemplate:delete --service-template "ST1"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a service template, use **duplicate** action::

  ./centreonConsole centreon-configuration:serviceTemplate:duplicate --service-template "ST1"
  Object successfully duplicated

List tag
--------

In order to list tags of a service template, use **listTag** action::

  ./centreonConsole centreon-configuration:serviceTemplate:listTag --service-template "ST1"
  tag1

Add tag
-------

In order to add a tag to a service template, use **addTag** action::

  ./centreonConsole centreon-configuration:serviceTemplate:addTag --service-template "ST1" --tag "tag2
  The tag has been successfully added to the object

Remove tag
----------

In order to remove a tag from a service, use **removeTag** action::

  ./centreonConsole centreon-configuration:serviceTemplate:removeTag --service-template "ST1" --tag "tag2"
  The tag has been successfully removed from the object


List Macro
----------

In order to list macros of a service, use **listMacro** action::

  ./centreonConsole centreon-configuration:serviceTemplate:listMacro --service-template service1
  tag1

Add Macro
---------

In order to add a macro to a service, use **addMacro** action::

  ./centreonConsole centreon-configuration:serviceTemplate:addMacro --service-template service1 --name macro1name --value macro1value --hidden 0

Remove Macro
------------

In order to remove a macro from a service, use **removeMacro** action::

  ./centreonConsole centreon-configuration:serviceTemplate:removeMacro --service-template service1 --macro "macro1name"

Update Macro
------------

In order to update a macro from a service, use **updateMacro** action::

  ./centreonConsole centreon-configuration:serviceTemplate:updateMacro --service=service1 --macro="macro1name" --value=macro1newvalue --name=macro1newname --hidden=1