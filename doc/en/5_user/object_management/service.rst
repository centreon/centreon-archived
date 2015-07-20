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
  id;name;slug;activate
  12;ping;centreon-export-ping;1
  13;load;ces3-rwe-pp-load;1;5;CES3-RWE-PP
  14;cpu;ces3-rwe-pp-cpu;1;5;CES3-RWE-PP
  15;memory;ces3-rwe-pp-memory;1;5;CES3-RWE-PP
  16;swap;ces3-rwe-pp-swap;1;5;CES3-RWE-PP
  17;ping;ces3-rwe-pp-ping;1;5;CES3-RWE-PP
  18;load;ces3-qde-pp-ces22-load;1;6;CES3-QDE-PP-CES22
  19;cpu;ces3-qde-pp-ces22-cpu;1;6;CES3-QDE-PP-CES22
  20;memory;ces3-qde-pp-ces22-memory;1;6;CES3-QDE-PP-CES22
  21;swap;ces3-qde-pp-ces22-swap;1;6;CES3-QDE-PP-CES22
  22;ping;ces3-qde-pp-ces22-ping;1;6;CES3-QDE-PP-CES22
  23;load;ces3-qde-pp-ces3-load;1;7;CES3-QDE-PP-CES3
  24;cpu;ces3-qde-pp-ces3-cpu;1;7;CES3-QDE-PP-CES3
  25;memory;ces3-qde-pp-ces3-memory;1;7;CES3-QDE-PP-CES3
  26;swap;ces3-qde-pp-ces3-swap;1;7;CES3-QDE-PP-CES3
  27;ping;ces3-qde-pp-ces3-ping;1;7;CES3-QDE-PP-CES3


Columns are the following:

============================== ==========================
Column                         Description
============================== ==========================
id                             Service id

name                           Service name

slug                           Service slug

activate                       Service enabled

host_id                        Host id

host_name                      Host name
============================== ==========================

Show
----

In order to show a service, use **show** action::

  ./centreonConsole centreon-configuration:service:show --service=centreon-export-ping
  id: 12
  template: 2
  command2: 
  timeperiod: 
  command_command_id2: 
  name: ping
  slug: centreon-export-ping
  service_alias: 
  volatile: 2
  max-check-attempts: 
  normal-check-interval: 
  retry-check-interval: 
  active-checks-enabled: 
  initial_state: 
  service_obsess_over_service: 
  check-freshness: 
  freshness-threshold: 
  eventhandler-enabled: 
  low-flap-threshold: 
  high-flap-threshold: 
  flap-detection-enabled: 
  service_comment: 
  command_command_id_arg: 
  command_command_id_arg2: 
  service_locked: 0
  service_register: 1
  activate: 1
  type: 1
  organization_id: 1
  environment: 
  domain: 
  timeout:

Create
------

In order to create a service, use **create** action::

  ./centreonConsole centreon-configuration:service:create --description 'service1' --host 'centreon-export'
  Object successfully created

Slug
----
In order to get slug of service, use **getSlug** action::
  ./centreonConsole centreon-configuration:service:getSlug --host-name 'Centreon_export' --service-description 'service1'
  centreon-export-service1


Update
------

In order to update a service, use **update** action::

  ./centreonConsole centreon-configuration:service:update --service 'centreon-export-service1' --description 'service2'
  Object successfully updated

Delete
------

In order to delete a service, use **delete** action::

  ./centreonConsole centreon-configuration:service:delete --service 'centreon-export-service1'
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a service, use **duplicate** action::

  ./centreonConsole centreon-configuration:service:duplicate --service 'centreon-export-service1'
  Object successfully duplicated

List tag
--------

In order to list tags of a service, use **listTag** action::

  ./centreonConsole centreon-configuration:service:listTag --service 'centreon-export-service1'
  tag-service-1
  tag1

Add tag
-------

In order to add a tag to a service, use **addTag** action::

  ./centreonConsole centreon-configuration:service:addTag --service 'centreon-export-service1' --tag "tag1"
  The tag has been successfully added to the object

Remove tag
----------

In order to remove a tag from a service, use **removeTag** action::

  ./centreonConsole centreon-configuration:service:removeTag --service 'centreon-export-service1' --tag "tag1"
  The tag has been successfully removed from the object


List Macro
----------

In order to list macros of a service, use **listMacro** action::

  ./centreonConsole centreon-configuration:service:listMacro --service 'centreon-export-service1'
  macro_name;macro_value;macro_hidden
  $_SERVICEmacro1name$;macro1value;1

Add Macro
---------

In order to add a macro to a service, use **addMacro** action::

  ./centreonConsole centreon-configuration:service:addMacro --service 'centreon-export-service1' --name 'macro1name' --value 'macro1value' --hidden '0'
  The macro 'macro1name' has been successfully added to the object

Remove Macro
------------

In order to remove a macro from a service, use **removeMacro** action::

  ./centreonConsole centreon-configuration:service:removeMacro --service "centreon-export-service1" --macro "macro1name"
  The macro 'macro1name' has been successfully removed from the object

Update Macro
------------

In order to update a macro from a service, use **updateMacro** action::

  ./centreonConsole centreon-configuration:service:updateMacro --service "centreon-export-service1" --macro "macro1name" --value "macro1newvalue" --name "macro1newname" --hidden "1"
  The macro 'macro1name' has been successfully updated
