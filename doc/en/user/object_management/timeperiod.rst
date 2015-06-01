Timeperiod
==========

Overview
--------

Object name: **centreon-configuration:Timeperiod**

Available parameters are the following:

================== =========================
Parameter          Description
================== =========================
**tp_name**        Timeperiod name

tp_alias           Timeperiod alias

tp_sunday          Sunday period

tp_monday          Monday period

tp_tuesday         Tuesday period

tp_wednesday       Wednesday period

tp_thursday        Thursday period

tp_friday          Friday period

tp_saturday        Saturday period
================== =========================

List
----

In order to list commands, use **list** action::

  ./centreonConsole centreon-configuration:timeperiod:list
  id;name;sunday;monday;tuesday;wednesday;thursday;friday;saturday
  3;24x7;00-00:24:00;00-00:24:00;00-00:24:00;00-00:24:00;00-00:24:00;00-00:24:00;00-00:24:00

Columns are the following:

============== =================
Column         Description
============== =================
id             Timeperiod id

name           Timeperiod name

sunday         Sunday period

monday         Monday period

tuesday        Tuesday period

wednesday      Wednesday period

thursday       Thursday period

friday         Friday period

saturday       Saturday period
============== =================

Show
----

In order to show a timeperiod, use **show** action::

  ./centreonConsole centreon-configuration:timeperiod:show object=timeperiod[24x7]
  id: 3
  name: 24x7
  alias: 00:00-24:00
  sunday: 00-00:24:00
  monday: 00-00:24:00
  tuesday: 00-00:24:00
  wednesday: 00-00:24:00
  thursday: 00-00:24:00
  friday: 00-00:24:00
  saturday: 00-00:24:00
  organization_id: 1

Create
------

In order to create a timeperiod, use **create** action::

  ./centreonConsole centreon-configuration:timeperiod:create params="tp_name[24x7];tp_alias[24x7];tp_sunday[00:00-24:00];tp_monday[00:00-24:00];tp_tuesday[00:00-24:00];tp_wednesday[00:00-24:00];tp_thursday[00:00-24:00];tp_friday[00:00-24:00];tp_saturday[00:00-24:00]"
  Object successfully created

Update
------

In order to update a timeperiod, use **update** action::

  ./centreonConsole centreon-configuration:timeperiod:update object="timeperiod[24x7]":params="tp_sunday[00:00-24:00]"
  Object successfully updated

Delete
------

In order to delete a timeperiod, use **delete** action::

  ./centreonConsole centreon-configuration:timeperiod:delete object="timeperiod[24x7]"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a timeperiod, use **duplicate** action::

  ./centreonConsole centreon-configuration:timeperiod:duplicate object="timeperiod[24x7]"
  Object successfully duplicated

