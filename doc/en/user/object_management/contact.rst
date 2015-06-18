Contact
=======

Overview
--------

Object name: **centreon-administration:contact**

Available parameters are the following:

================== =========================
Parameter          Description
================== =========================
**description**    Contact description

timezone-id        Timezone id
================== =========================

List
----

In order to list contacts, use **list** action::

  ./centreonConsole centreon-administration:contact:list
  id;description;timezone
  1;admin contact;
  2;jdoe contact;

Columns are the following:

==================== ====================
Column               Description
==================== ====================
id                   Contact id

description          Contact description

timezone             Timezone id
==================== ====================

Show
----

In order to show a contact, use **show** action::

  ./centreonConsole centreon-administration:contact:show --contact "john"
  id: 5
  description: john
  slug: john
  timezone: 

Create
------

In order to create a contact, use **create** action::

  ./centreonConsole centreon-administration:contact:create --description 'john'
  Object successfully created

Update
------

In order to update a contact, use **update** action::

  ./centreonConsole centreon-administration:contact:update --contact "john" --timezone-id 'africa-accra'
  Object successfully updated

Delete
------

In order to delete a contact, use **delete** action::

  ./centreonConsole centreon-administration:contact:delete --contact "john"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a contact, use **duplicate** action::

  ./centreonConsole centreon-administration:contact:duplicate --contact "john"
  Object successfully duplicated

