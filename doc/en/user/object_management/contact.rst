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
  id;description;slug
  1;admin contact;admin-contac

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
  john
  Object successfully created


Slug
----
In order to get slug of contact, use **getSlug** action::
  ./centreonConsole centreon-administration:contact:getSlug --description 'john'
  john


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


List tag
--------

In order to list tags of a contact, use **listTag** action::

  ./centreonConsole centreon-administration:contact:listTag --contact "john"
  tag2

Add tag
-------

In order to add a tag to a contact, use **addTag** action::

  ./centreonConsole centreon-administration:contact:addTag --contact "john" --tag "tag2"
  tag2 has been successfully added to the object

Remove tag
----------

In order to remove a tag from a contact, use **removeTag** action::

  ./centreonConsole centreon-administration:contact:removeTag --contact "john" --tag "tag2"
  The tag has been successfully removed from the object

