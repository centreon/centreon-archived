Service
=======

Overview
--------

Object name: **centreon-configuration:serviceTemplate**

Available parameters are the following:

===================================== ================================
Parameter                             Description
===================================== ================================
**service_description**               Service template description

service_alias                         Service template alias

service_template_hosts                Linked host templates id

command_command_id                    Check command id

service_template_model_stm_id         Linked service template id

service_normal_check_interval         Normal check interval

service_retry_check_interval          Retry check interval

service_max_check_attempts            Max check attempts

domain_id                             Domain id

environment_id                        Environment id

service_icon                          Service template icon

timeperiod_tp_id                      Timeperiod id

service_type                          Service type

service_is_volatile                   Service volatile

service_traps                         Linked service traps id

service_active_checks_enabled         Active check enable (0 or 1)

service_passive_checks_enabled        Passive check enable (0 or 1)

service_obsess_over_host              TODO

service_check_freshness               Freshness enable (0 or 1)

service_freshness_threshold           Freshness threshold

service_flap_detection_enabled        Flap detection enable (0 or 1)

service_low_flap_threshold            Low flap detection threshold

service_high_flap_threshold           High flap detection threshold

service_event_handler_enabled         Event handler enable (0 or 1)

command_command_id2                   Event handler command
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

  ./centreonConsole centreon-configuration:serviceTemplate:show object=servicetemplate[ST1]
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

  ./centreonConsole centreon-configuration:serviceTemplate:create params="service_description[ST1]"
  Object successfully created

Update
------

In order to update a service template, use **update** action::

  ./centreonConsole centreon-configuration:serviceTemplate:update object="servicetemplate[ST1]":params="service_alias[service template 1];service_max_check_attempts[4]"
  Object successfully updated

Delete
------

In order to delete a service template, use **delete** action::

  ./centreonConsole centreon-configuration:serviceTemplate:delete object="servicetemplate[ST1]"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a service template, use **duplicate** action::

  ./centreonConsole centreon-configuration:serviceTemplate:duplicate object="servicetemplate[ST1]"
  Object successfully duplicated

List tag
--------

In order to list tags of a service template, use **listTag** action::

  ./centreonConsole centreon-configuration:serviceTemplate:listTag object="servicetemplate[ST1]"
  tag1

Add tag
-------

In order to add a tag to a service template, use **addTag** action::

  ./centreonConsole centreon-configuration:serviceTemplate:addTag object="servicetemplate[ST1]":tag="tag2
  The tag has been successfully added to the object

Remove tag
----------

In order to remove a tag from a service, use **removeTag** action::

  ./centreonConsole centreon-configuration:serviceTemplate:removeTag object="servicetemplate[ST1]":tag="tag2"
  The tag has been successfully removed from the object

