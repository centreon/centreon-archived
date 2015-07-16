Indicator
=========

Overview
--------

Object name: **centreon-bam:Indicator**

Available parameters are the following:

======================= ================================
Parameter               Description
======================= ================================
**--type**              Indicator type

--service-slug          Service indicator slug

--host-slug             Host indicator slug

--indicator-ba-slug     BA indicator slug

--boolean-slug          Boolean indicator slug

--bolean-expression     Boolean expression slug

--boolean-state         Boolean return state

--ba                    Impacted BA

--drop-warning          Warning threshold

--drop-critical         Critical threshold

--drop-unknown          Unknown threshold
======================= ================================

List
----

In order to list indicator, use **list** action::

  ./centreonConsole centreon-bam:Indicator:list
  id;state_type;type;current_status
  1;2;2;0
  2;2;2;0

Columns are the following:

=============== ==============================
Column          Description
=============== ==============================
id              Business activity id

state_type      Business activity name

type            Business activity type

current_status  Business activity description
=============== ==============================

Show
----

In order to show an indicator, use **show** action::

  ./centreonConsole centreon-bam:Indicator:show

Create
------

In order to create an indicator, use **create** action::

  ./centreonConsole centreon-bam:Indicator:create --ba='ba1' --type='service' -service-slug='centreon-export-ping' --drop-warning='10' --drop-critical='50' --drop-unknown='30'
  Object successfully created

Update
------

In order to update an indicator, use **update** action::

  ./centreonConsole centreon-bam:Indicator:update
  Object successfully updated

Delete
------

In order to delete an indicator, use **delete** action::

  ./centreonConsole centreon-bam:Indicator:delete
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate an indicator, use **duplicate** action::

  ./centreonConsole centreon-bam:Indicator:duplicate
  Object successfully duplicated

