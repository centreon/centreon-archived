===========
Host groups
===========

Overview
--------

Object name: **HG**

Show
----

In order to list available host groups, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HG -a show
  id;name;alias
  53;Linux-Servers;All linux servers
  54;Windows-Servers;All windows servers
  55;Networks;All other equipments
  56;Printers;All printers
  58;Routers;All routers
  59;Switchs;All switchs
  60;Firewall;All firewalls
  61;Unix-Servers;All unix servers

Columns are the following:

======= ===============
Column	Description
======= ===============
ID	ID

Name	Name

Alias	Alias
======= ===============


Add
---

In order to add a hostgroup, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HG -a add -v "SAP;SAP servers" 


The required parameters are the following:

========= ====================
Order     Description
========= ====================
1         Name of host group

2         Alias of host group
========= ====================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Del
---

In order to delete one hostgroup, use the **DEL** action. The host group name is used for identifying the host group you would like to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HG -a DEL -v "SAP" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Setparam
--------

In order to set a specific parameter for a host group, use the **SETPARAM** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HG -a setparam -v "SAP;name;hg1" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o HG -a setparam -v "SAP;alias;hg2" 

You may change the following parameters:

=============== =============================
Parameter	Description
=============== =============================
name	        Name

alias	        Alias

comment	        Comment

activate	*1* when enabled, *0* otherwise

notes	        Notes

notes_url	Notes URL

action_url	Action URL

icon_image	Icon image

map_icon_image	Map icon image
=============== =============================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Getmember
---------

If you want to retrieve the members of a host group, use the **GETMEMBER** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HG -a getmember -v "Linux-Servers" 
  id;name
  34;Centreon-Server
  35;srv-web


Addmember and Setmember
-----------------------

If you want to add members to a specific host group, use the **SETMEMBER** or **ADDMEMBER** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HG -a setmember -v "Linux-Servers;srv-test|srv-test2" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o HG -a addmember -v "Linux-Servers;srv-new" 

======= =======================================================================================
Action	Description
======= =======================================================================================
set*	 Overwrites previous definitions. Use the delimiter | to set multiple members

add*	 Appends new members to the existing ones. Use the delimiter | to add multiple members
======= =======================================================================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Delmember
---------

If you want to remove members from a specific host group, use the **DELMEMBER** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HG -a delmember -v "Linux-Servers;srv-test" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.
