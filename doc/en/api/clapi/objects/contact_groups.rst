==============
Contact Groups
==============

Overview
--------

Object name: **CG**

Show
----
In order to list available contact groups, use the **SHOW** action::

  [root@centreon core]# ./centreon -u admin -p centreon -o CG -a show
  id;name;alias;members
  Guest;Guests Group;gest-user1,guest-user2
  Supervisors;Centreon supervisors;Admin
  
Columns are the following:

========== ===============================================
Column     Description
========== ===============================================
Name       

Alias

Members    List of contacts that are in the contact group
========== ===============================================


Add
---

In order to add a contact group, use the **ADD** action::

  [root@centreon core]# ./centreon -u admin -p centreon -o CG -a ADD -v "Windows;Windows admins" 

Required fields are the following:

======== ===============
Column   Description
======== ===============
Name     Name

Alias    Alias
======== ===============

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Del
---

In order to delete one contact group, use the **DEL** action::

  [root@centreon core]# ./centreon -u admin -p centreon -o CG -a DEL -v "Windows" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Setparam
--------

In order to change the name or the alias of a contactgroup, use the **SETPARAM** action::

  [root@centreon core]# ./centreon -u admin -p centreon -o CG -a setparam -v "Windows;name;Windows-2K" 
  [root@centreon core]# ./centreon -u admin -p centreon -o CG -a setparam -v "Cisco;alias;Cisco-Routers" 

Parameters that you can change are the following:

========= ===================
Parameter Description
========= ===================
name      Name
alias     Alias
========= ===================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Enable
------

In order to enable a contact group, use the **ENABLE** action::

  [root@centreon core]# ./centreon -u admin -p centreon -o CG -a enable -v "Guest" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Disable
-------

In order to disable a contact group, use the **DISABLE** action::

  [root@centreon core]# ./centreon -u admin -p centreon -o CG -a disable -v "Guest" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Getcontact
----------

In order to view the contact list of a contact group, use the **GETCONTACT** action::

  [root@centreon core]# ./centreon -u admin -p centreon -o CG -a getcontact -v "Guest" 
  id;name
  1;User1
  2;User2

Columns are the following:

======= ================
Column  Description
======= ================
ID      Id of contact

Name    Name of contact
======= ================


Addcontact and Setcontact
-------------------------

In order to add a contact to a contact group, use the **ADDCONTACT** or **SETCONTACT** action where 'add' will append and 'set' will overwrite previous definitions::

  [root@centreon core]# ./centreon -u admin -p centreon -o CG -a addcontact -v "Guest;User1" 
  [root@centreon core]# ./centreon -u admin -p centreon -o CG -a setcontact -v "Guest;User1|User2" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Delcontact
----------

In order to remove a contact from a contact group, use the **DELCONTACT** action::

  [root@centreon core]# ./centreon -u admin -p centreon -o CG -a delcontact -v "Guest;User1" 
  [root@centreon core]# ./centreon -u admin -p centreon -o CG -a delcontact -v "Guest;User2" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.
