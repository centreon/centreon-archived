User
====

Overview
--------

Object name: **centreon-administration:user**

Available parameters are the following:

================== =========================
Parameter          Description
================== =========================
**login**          User login

**is-admin**       Admin (0 or 1)

**is-activated**   Enable (0 or 1)

firstname          User firstname

lastname           User lastname

password           User password

language-id        Slug name of Language

timezone-id        Slug name of Timezone

auth-type          Authentication type
================== =========================

List
----

In order to list users, use **list** action::

  ./centreonConsole centreon-administration:user:list
  id;firstname;lastname;login;admin;enabled;language;timezone;authentication type
  1;admin;admin;admin;1;1;;;
  2;guest;guest;guest;0;1;;;local

Columns are the following:

==================== ====================
Column               Description
==================== ====================
id                   User id

firstname            User firstname

lastname             User lastname

login                User login

admin                Admin (0 or 1)

enabled              Enable (0 or 1)

language             Language id

timezone             Timezone id

authentication type  Authentication type
==================== ====================

Show
----

In order to show a user, use **show** action::

  ./centreonConsole centreon-administration:user:show --user 'jdoe'
  id: 5
  login: jdoe
  slug: jdoe
  password:
  admin: 1
  is_locked: 0
  enabled: 1
  is_password_old: 0
  language:
  timezone:
  contact_id: 5
  createdat: 2015-06-01 17:00:58
  updatedat: 2015-06-01 17:00:58
  authentication type:
  firstname: John
  lastname: Doe
  autologin_key:

Create
------

In order to create a user, use **create** action::

  ./centreonConsole centreon-administration:user:create --login 'jdoe' --password 'johndoe' --firstname 'John' --lastname 'Doe' --is-admin '1' --is-activated '1'
  Object successfully created

Update
------

In order to update a user, use **update** action::

  ./centreonConsole centreon-administration:user:update --user 'jdoe' --is-admin "0"
  Object successfully updated

Delete
------

In order to delete a user, use **delete** action::

  ./centreonConsole centreon-administration:user:delete --user "jdoe"
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a user, use **duplicate** action::

  ./centreonConsole centreon-administration:user:duplicate --user "jdoe"
  Object successfully duplicated

