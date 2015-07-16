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
  id;firstname;lastname;login;slug;admin;enabled;timezone;authentication type
  1;admin;admin;admin;admin;1;1;;

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
  id: 1
  login: admin
  slug: admin
  password: 725a9a9d352762de6835ae68f047eaaea9c56e31ec99132f341193ec79a8f0a7::8000::1d9350cce7a647f5ff4aa4810e5d13dd5960dd02bcd9c9decfd64de37fa975db0346507f0d188a18e095f4e7fea351dd7e4ae07283159b300885f3c1a2baf056fa438164167941b600b0c9de62bb41a2d9c5f7c1e8c22ce82d37850
  admin: 1
  is_locked: 0
  enabled: 1
  is_password_old: 1
  language: 
  timezone: 
  contact_id: 1
  createdat: 2015-07-13 15:54:19
  updatedat: 2015-07-13 15:54:19
  authentication type: 
  firstname: admin
  lastname: admin
  autologin-key: 

Create
------

In order to create a user, use **create** action::

  ./centreonConsole centreon-administration:user:create --login 'jdoe' --password 'johndoe' --firstname 'John' --lastname 'Doe' --is-admin '1' --is-activated '1'
  jdoe
  Object successfully created

Slug
----
In order to get slug of user, use **getSlug** action::
  ./centreonConsole centreon-administration:user:getSlug --user-name admin
  admin

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

