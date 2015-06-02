Trap
====

Overview
--------

Object name: **centreon-configuration:Trap**

Available parameters are the following:

==================  ===========================
Parameter           Description
==================  ===========================
*traps_name**       Trap name

**traps_oid**       OID

**manufacturer_id** Identifiant of manufacturer

**traps_args**      Output message

traps_status        Default status

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

  ./centreonConsole centreon-configuration:Trap:show object="traps[ccmCLIRunningConfigChanged]"
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

  ./centreonConsole centreon-configuration:trap:create params="traps_name[linkDown];traps_oid[.1.3.6.1.6.3.1.1.5.3];manufacturer_id[1];traps_args[Link down on interface $2. State: $4.];traps_status[3]"
  Object successfully created

Update
------

In order to update a trap, use **update** action::

  ./centreonConsole centreon-configuration:trap:update object="traps[linkDown]":params="traps_name[linkDown2];traps_oid[.1.3.6];manufacturer_id[1];traps_args[Link down on interface $2. State: $4.];traps_status[3]"
  Object successfully updated

Delete
------

In order to delete a trap, use **delete** action::

  ./centreonConsole centreon-configuration:Trap:delete object="traps[linkDown]"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a trap, use **duplicate** action::

  ./centreonConsole centreon-configuration:Trap:duplicate object="traps[linkDown]"
  Object successfully duplicated

