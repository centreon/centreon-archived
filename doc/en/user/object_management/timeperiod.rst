Timeperiod
==========

Overview
--------

Object name: **centreon-configuration:Timeperiod**

Available parameters are the following:

================== =========================
Parameter          Description
================== =========================
**tp-name**        Timeperiod name

tp-alias           Timeperiod alias

tp-sunday          Sunday period

tp-monday          Monday period

tp-tuesday         Tuesday period

tp-wednesday       Wednesday period

tp-thursday        Thursday period

tp-friday          Friday period

tp-saturday        Saturday period
================== =========================

List
----

In order to list timeperiods, use **list** action::

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

  ./centreonConsole centreon-configuration:timeperiod:show --timeperiod '24x7'
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

  ./centreonConsole centreon-configuration:timeperiod:create --tp-name '24x7' --tp-alias '24x7' --tp-sunday '00:00-24:00' --tp-monday '00:00-24:00' --tp-tuesday '00:00-24:00' --tp-wednesday '00:00-24:00' --tp-thursday '00:00-24:00' --tp-friday '00:00-24:00' --tp-saturday '00:00-24:00'
  Object successfully created

Update
------

In order to update a timeperiod, use **update** action::

  ./centreonConsole centreon-configuration:timeperiod:update --timeperiod "24x7" --tp-sunday '00:00-24:00'
  Object successfully updated

Delete
------

In order to delete a timeperiod, use **delete** action::

  ./centreonConsole centreon-configuration:timeperiod:delete --timeperiod '24x7'
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a timeperiod, use **duplicate** action::

  ./centreonConsole centreon-configuration:timeperiod:duplicate --timeperiod '24x7'
  Object successfully duplicated

