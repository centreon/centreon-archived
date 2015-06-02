Manufacturer
============

Overview
--------

id,name,alias,description,organization_id

Object name: **centreon-configuration:Manufacturer**

Available parameters are the following:

==============  ===========================
Parameter           Description
==============  ===========================
*name**         Manufacturer name

alias           Manufacturer alias 

description     Description of manufacturer

organization_id Organization id

=============== ===========================

List
----

In order to list manufacturer, use **list** action::

  ./centreonConsole centreon-configuration:Manufacturer:list
  id;name;alias;description;organization id
  1;DELL;DELL;DELL desciption;1
  3;HP;HP1;HPPPP;1



Columns are the following:

=============== ===========================
Column          Description
=============== ===========================
id              Manufacturer id

name            Manufacturer name

alias           Alias manufacturer

description     Identifiant of manufacturer

organization id organization id 

=============== ===========================

Show
----

In order to show a manufacturer, use **show** action::

  ./centreonConsole centreon-configuration:Manufacturer:show object="manufacturer[DELL]"
  id: 1
  name: DELL
  alias: DELL
  description: DELL desciption
  organization id: 1


Create
------

In order to create a manufacturer, use **create** action::

  ./centreonConsole centreon-configuration:manufacturer:create params="name[HP];alias[HP1];description[HP description];"
  Object successfully created

Update
------

In order to update a manufacturer, use **update** action::

  ./centreonConsole centreon-configuration:Manufacturer:update object="manufacturer[HP]":params="name[HP2];alias[HP1];description[HP description];"
  Object successfully updated

Delete
------

In order to delete a manufacturer, use **delete** action::

  ./centreonConsole centreon-configuration:Manufacturer:delete object="manufacturer[HP]"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a manufacturer, use **duplicate** action::

  ./centreonConsole centreon-configuration:Manufacturer:duplicate object="manufacturer[HP]"
  Object successfully duplicated

