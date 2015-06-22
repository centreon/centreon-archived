Trap
====

Overview
--------

Object name: **centreon-configuration:Trap**

Available parameters are the following:

==================  ===========================
Parameter           Description
==================  ===========================
*traps-name**       Trap name

**traps-oid**       OID

**manufacturer-id** Identifiant of manufacturer

**traps-args**      Output message

traps-status        Default status

================== =============================

List
----

In order to list trap, use **list** action::

  ./centreonConsole centreon-configuration:Trap:list
  id;name;traps oid;manufacturer;traps args;traps status
  4;ccmCLIRunningConfigChanged;.1.3.6.1.4.1.9.9.43.2.0.2;1;This notification indicates that the running $*;2
  5;linkDown;.1.3.6.1.6.3.1.1.5.3;1;Link down on interface . State: .;3
  6;linkUp;.1.3.6.1.6.3.1.1.5.4;1;Link up on interface . State: .;2


Columns are the following:

============== ===========================
Column         Description
============== ===========================
id             Trap id

name           Trap name

traps oid      OID

manufacturer   Identifiant of manufacturer

traps args     Output message

traps status   Trap type

============== ===========================

Show
----

In order to show a trap, use **show** action::

  ./centreonConsole centreon-configuration:Trap:show --traps "ccmCLIRunningConfigChanged"
  id: 4
  name: ccmCLIRunningConfigChanged
  traps oid: .1.3.6.1.4.1.9.9.43.2.0.2
  traps args: This notification indicates that the running $*
  traps status: 2
  manufacturer: 1
  traps_reschedule_svc_enable: 0
  traps_execution_command: 
  traps_execution_command_enable: 0
  traps_submit_result_enable: 0
  traps_advanced_treatment: 0
  traps_advanced_treatment_default: 0
  traps_timeout: 
  traps_exec_interval: 
  traps_exec_interval_type: 0
  traps_log: 0
  traps_routing_mode: 0
  traps_routing_value: 
  traps_exec_method: 0
  traps_comments: 
  organization_id: 1


Create
------

In order to create a trap, use **create** action::

  ./centreonConsole centreon-configuration:trap:create --traps-name "linkDown" --traps-oid '.1.3.6.1.6.3.1.1.5.3' --manufacturer-id 'dell' --traps-args 'Link down on interface $2. State: $4.' --traps-status "3"
  Object successfully created

Update
------

In order to update a trap, use **update** action::

  ./centreonConsole centreon-configuration:trap:update --traps "linkDown" --traps-name 'linkDown2' --traps-oid '.1.3.6' --manufacturer-id 'dell' --traps-args 'Link down on interface $2. State: $4.' --traps-status "3"
  Object successfully updated

Delete
------

In order to delete a trap, use **delete** action::

  ./centreonConsole centreon-configuration:Trap:delete --traps "linkDown"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a trap, use **duplicate** action::

  ./centreonConsole centreon-configuration:Trap:duplicate --traps 'linkDown'
  Object successfully duplicated

