Host
====

Overview
--------

Object name: **centreon-configuration:host**

Available parameters are the following:

============================== ================================
Parameter                      Description
============================== ================================
**--name**                     Host name

**--address**                  Host address

**--disable**                  Enable (0 or 1)

--alias                        Host alias

--host-templates               Linked host templates slug

--check-interval               Check interval

--retry-check-interval         Retry interval

--max-check-attempts           Max check attempts

--poller                       Poller slug

--timezone                     Timezone slug

--icon                         Host icon

--environment                  Environment slug

--timeperiod                   Timeperiod slug

--command                      Check command slug

--active-checks-enabled        Active check enable (0 or 1)

--comment                      Host comments

--obsess                       TODO

--check-freshness              Freshness enable (0 or 1)

--freshness-threshold          Freshness threshold

--flap-detection-enabled       Flap detection enable (0 or 1)

--low-flap-threshold           Low flap detection threshold

--high-flap-threshold          High flap detection threshold

--flap-detection-enabled       Flap detection options

--eventhandler-enabled         Event handler enable (0 or 1)

--command2                     Event handler command

--parents                      Host parents slug

--childs                       Host children slug
============================== ================================

List
----

In order to list hosts, use **list** action::

  ./centreonConsole centreon-configuration:host:list
  id;name;slug;description;address;activate
  4;Centreon-export;centreon-export;;10.30.2.87;1
  5;CES3-RWE-PP;ces3-rwe-pp;;10.30.2.127;1
  6;CES3-QDE-PP-CES22;ces3-qde-pp-ces22;;10.50.1.84;1
  7;CES3-QDE-PP-CES3;ces3-qde-pp-ces3;;10.50.1.85;1
  8;SRVI-WIN-TEST;srvi-win-test;;10.50.1.158;1
  9;Terminal server GSO;terminal-server-gso;;10.41.1.28;1
  11;Centreon-export_new;centreon-export-new;;10.30.2.87;1
  12;CES3-QDE-PP-CES22_new;ces3-qde-pp-ces22-new;;10.50.1.84;1

Columns are the following:

============== ======================
Column         Description
============== ======================
id             Host id

name           Host name

slug           Host slug

description    Host description

address        Ip address

activate       Enable (0 or 1)
============== ======================

Show
----

In order to show a host, use **show** action::

  ./centreonConsole centreon-configuration:host:show --host centreon-export
  id: 4
  command: 
  command_command_id_arg1: 
  timeperiod: 
  command2: 
  command_command_id_arg2: 
  name: Centreon-export
  slug: centreon-export
  description: 
  address: 10.30.2.87
  max-check-attempts: 
  check-interval: 
  retry-check-interval: 
  active-checks-enabled: 2
  host_checks_enabled: 
  initial_state: 
  obsess: 2
  check-freshness: 2
  freshness-threshold: 
  eventhandler-enabled: 2
  low-flap-threshold: 
  high-flap-threshold: 
  flap-detection-enabled: 2
  flap_detection_options: 
  snmp-community: 
  snmp-version: 
  host_location: 0
  comment: 
  host_register: 1
  activate: 1
  organization_id: 1
  environment: 
  poller: 1
  timezone: 
  timeout: 


Create
------

In order to create a host, use **create** action::

  ./centreonConsole centreon-configuration:Host:create --name='Centreon-export' --address='10.30.2.87' --poller='central'
  centreon-export
  Object successfully created


Slug
----
In order to get slug of host, use **getSlug** action::
  ./centreonConsole centreon-configuration:Host:getSlug --resource-name 'Centreon-export'
  centreon-export


Update
------

In order to update a host, use **update** action::

  ./centreonConsole centreon-configuration:host:update --host "centreon-export" --host-templates 'host-tpl' --poller 'central'
  Object successfully updated

Delete
------

In order to delete a host, use **delete** action::

  ./centreonConsole centreon-configuration:host:delete --host "centreon-export"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a host, use **duplicate** action::

  ./centreonConsole centreon-configuration:host:duplicate --host "host1"
  Object successfully duplicated

List tag
--------

In order to list tags of a host, use **listTag** action::

  ./centreonConsole centreon-configuration:host:listTag --host "centreon-export"
  paris
  rennes

Add tag
-------

In order to add a tag to a host, use **addTag** action::

  ./centreonConsole centreon-configuration:Host:addTag --host='centreon-export' --tag='paris,rennes'
  tags has been successfully added to the object

Remove tag
----------

In order to remove a tag from a host, use **removeTag** action::

  ./centreonConsole centreon-configuration:host:removeTag --host 'centreon-export' --tag "paris"
  tag has been successfully removed from the object




List Macro
----------

In order to list macros of a host, use **listMacro** action::

  ./centreonConsole centreon-configuration:host:listMacro --host 'centreon-export'
  macro_name;macro_value;macro_hidden
  $_HOSTmacro1name$;macro1value;1

Add Macro
---------

In order to add a macro to a host, use **addMacro** action::

  ./centreonConsole centreon-configuration:host:addMacro --host "centreon-export" --name "macro1name" --value "macro1value" --hidden 0
  The macro 'macro1name' has been successfully added to the object


Remove Macro
------------

In order to remove a macro from a host, use **removeMacro** action::

  ./centreonConsole centreon-configuration:host:removeMacro --host "centreon-export" --macro "macro1name"
  The macro 'macro1name' has been successfully removed from the object

Update Macro
------------

In order to update a macro from a host, use **updateMacro** action::

  ./centreonConsole centreon-configuration:host:updateMacro --host "centreon-export" --macro "macro1name" --value "macro1newvalue" --name="macro1newname" --hidden 1
  The macro 'macro1name' has been successfully updated

