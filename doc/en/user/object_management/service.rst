Service
=======

Overview
--------

Object name: **centreon-configuration:service**

Available parameters are the following:

===================================== ================================
Parameter                             Description
===================================== ================================
**service_description**               Service description

**service_hosts**                     Linked hosts id

**command_command_id**                Check command id

service_template_model_stm_id         Linked service template id

service_normal_check_interval         Normal check interval

service_retry_check_interval          Retry check interval

service_max_check_attempts            Max check attempts

domain_id                             Domain id

environment_id                        Environment id

service_icon                          Service icon

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

In order to list services, use **list** action::

  ./centreonConsole centreon-configuration:service:list
  service_id;service_description;service_template_model_stm_id;service_activate
  1;service1;;1

Columns are the following:

============================== ==========================
Column                         Description
============================== ==========================
service_id                     Service id

service_description            Service description

service_template_model_stm_id  Linked service template id

service_activate               Enable (0 or 1)
============================== ==========================

Show
----

In order to show a service, use **show** action::

  ./centreonConsole centreon-configuration:service:show object="service[service1];host[host1]"
  id: 1
  template:
  command_command_id: 1
  timeperiod_tp_id:
  command_command_id2:
  name: service1
  service_alias:
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
  service_register: 1
  activate: 1
  service_type: 1
  organization_id: 1
  environment_id:
  domain_id:

Create
------

In order to create a service, use **create** action::

  ./centreonConsole centreon-configuration:service:create object="service[service1]":params="service_description[service1];timeperiod_tp_id[1];command_command_id[1];service_max_check_attempts[3];service_hosts[1]"
  Object successfully created

Update
------

In order to update a service, use **update** action::

  ./centreon/external/bin/centreonConsole centreon-configuration:service:update object="service[service1];host[host1]":params="service_max_check_attempts[1]"
  Object successfully updated

Delete
------

In order to delete a service, use **delete** action::

  ./srv/centreon/external/bin/centreonConsole centreon-configuration:service:delete object="service[service1]"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a service, use **duplicate** action::

  ./centreonConsole centreon-configuration:service:duplicate object="service[service1]"
  Object successfully duplicated

List tag
--------

In order to list tags of a service, use **listTag** action::

  ./centreonConsole centreon-configuration:service:listTag object="service[service1];host[host1]"
  tag-service-1
  tag1

Add tag
-------

In order to add a tag to a service, use **addTag** action::

  ./centreonConsole centreon-configuration:service:addTag object="service[service1];host[host1]":tag="tag1"
  The tag has been successfully added to the object

Remove tag
----------

In order to remove a tag from a service, use **removeTag** action::

  ./centreonConsole centreon-configuration:service:removeTag object="service[service1];host[host1]":tag="tag1"
  The tag has been successfully removed from the object


List Macro
----------

In order to list macros of a service, use **listMacro** action::

  ./centreonConsole centreon-configuration:service:listMacro object="service[service1]"
  tag1

Add Macro
---------

In order to add a macro to a service, use **addMacro** action::

  ./centreonConsole centreon-configuration:service:addMacro object="host[host1];service[service1]":params="name[macro1name];value[macro1value];hidden[0]"

Remove Macro
------------

In order to remove a macro from a service, use **removeMacro** action::

  ./centreonConsole centreon-configuration:service:removeMacro object="host[host1];service[service1]":macro="macro1name"

Update Macro
------------

In order to update a macro from a service, use **updateMacro** action::

  ./centreonConsole centreon-configuration:service:updateMacro object="host[host1];service[service1]":macro="macro1name":params="value[macro1newvalue];name[macro1newname];hidden[1];"
