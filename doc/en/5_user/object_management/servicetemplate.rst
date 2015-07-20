Service Template
================

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
  id;name;slug;status
  1;generic-service;generic-service;1
  2;ping-lan;ping-lan;1
  3;OS-Linux-SNMP-load;os-linux-snmp-load;1
  4;OS-Linux-SNMP-cpu;os-linux-snmp-cpu;1
  5;OS-Linux-SNMP-memory;os-linux-snmp-memory;1
  6;OS-Linux-SNMP-swap;os-linux-snmp-swap;1
  7;OS-Linux-SNMP-traffic-name;os-linux-snmp-traffic-name;1
  8;OS-Linux-SNMP-disk-name;os-linux-snmp-disk-name;1
  9;OS-Windows-SNMP-cpu;os-windows-snmp-cpu;1
  10;OS-Windows-SNMP-memory;os-windows-snmp-memory;1
  11;OS-Windows-SNMP-swap;os-windows-snmp-swap;1


Columns are the following:

============ ============================
Column       Description
============ ============================
id           Service template id

name         Service template description

slug         Service template slug

status       Enable (0 or 1)
============ ============================

Show
----

In order to show a service, use **show** action::

  ./centreonConsole centreon-configuration:serviceTemplate:show --service-template 'generic-service'
  id: 1
  template-model-stm: 
  command: 
  timeperiod: 1
  command2: 
  name: generic-service
  slug: generic-service
  alias: 
  volatile: 2
  max-check-attempts: 3
  normal-check-interval: 
  retry-check-interval: 
  active-checks-enabled: 2
  initial_state: 
  service_obsess_over_service: 
  check-freshness: 2
  freshness-threshold: 
  eventhandler-enabled: 2
  low-flap-threshold: 
  high-flap-threshold: 
  flap-detection-enabled: 2
  service_comment: 
  command_command_id_arg: 
  command_command_id_arg2: 
  service_locked: 0
  service_register: 0
  status: 1
  service_type: 1
  organization_id: 1
  environment_id: 
  domain: 
  timeout: 


Create
------

In order to create a service template, use **create** action::

  ./centreonConsole centreon-configuration:serviceTemplate:create --description "ST1"
  st1
  Object successfully created


Slug
----
In order to get slug of service template, use **getSlug** action::
  ./centreonConsole centreon-configuration:serviceTemplate:getSlug --servicetemplate-name OS-Linux-SNMP-load
  os-linux-snmp-load


Update
------

In order to update a service template, use **update** action::

  ./centreonConsole centreon-configuration:serviceTemplate:update --service-template 'ST1' --alias 'service template 1' --max-check-attempts "4"
  Object successfully updated

Delete
------

In order to delete a service template, use **delete** action::

  ./centreonConsole centreon-configuration:serviceTemplate:delete --service-template "st1"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a service template, use **duplicate** action::

  ./centreonConsole centreon-configuration:serviceTemplate:duplicate --service-template "st1"
  Object successfully duplicated

List tag
--------

In order to list tags of a service template, use **listTag** action::

  ./centreonConsole centreon-configuration:serviceTemplate:listTag --service-template "st1"
  tag2

Add tag
-------

In order to add a tag to a service template, use **addTag** action::

  ./centreonConsole centreon-configuration:serviceTemplate:addTag --service-template "st1" --tag "tag2"
  tag2 has been successfully added to the object

Remove tag
----------

In order to remove a tag from a service template, use **removeTag** action::

  ./centreonConsole centreon-configuration:serviceTemplate:removeTag --service-template "st1" --tag "tag2"
  The tag has been successfully removed from the object


List Macro
----------

In order to list macros of a service template, use **listMacro** action::

  ./centreonConsole centreon-configuration:serviceTemplate:listMacro --service-template st1
  macro_name;macro_value;macro_hidden
  macro1newname;macro1newvalue;1

Add Macro
---------

In order to add a macro to a service template, use **addMacro** action::

  ./centreonConsole centreon-configuration:serviceTemplate:addMacro --service-template st1 --name macro1name --value macro1value --hidden 0
  The macro 'macro1name' has been successfully added to the object

Remove Macro
------------

In order to remove a macro from a service template, use **removeMacro** action::

  ./centreonConsole centreon-configuration:serviceTemplate:removeMacro --service-template st1 --macro "macro1name"
  The macro 'macro1name' has been successfully removed from the object

Update Macro
------------

In order to update a macro from a service template, use **updateMacro** action::

  ./centreonConsole centreon-configuration:serviceTemplate:updateMacro --service-template st1 --macro 'macro1name' --value macro1newvalue --name macro1newname --hidden '1'
  The macro 'macro1name' has been successfully updated