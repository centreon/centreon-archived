================
Host categories
================

Overview
--------

Object name: **HC**

Show
----

In order to list available host categories, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HC -a show
  id;name;alias;members
  1;Linux;Linux Servers;host1
  2;Windows;Windows Server;host2
  3;AS400;AS400 systems;host3,host4

Columns are the following:

====== ======================
Column Description
====== ======================
Name   Name of host category

Alias  Alias of host category
====== ======================


Add
---

In order to add a host category, use the **ADD**::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HC -a add -v "Databases;Databases servers" 

Required parameters are the following:

============ ===========================
Order        Description
============ ===========================
1            Name of host category 

2            Alias of host category
============ ===========================


Del
---

In order to delete a host category, use the **DEL** action. The name is used for identifying the  host category you want to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HC -a DEL -v "Databases" 


Getmember
---------

In order to view the list hosts in a host category, use the **GETMEMBER** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HC -a getmember -v "Linux" 
  id;name
  14;Centreon-Server
  15;srv-test

Addmember and Setmember
-----------------------

In order to add a host or a host template into a host category, use the **ADDMEMBER** or **SETMEMBER** action where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HC -a addmember -v "Linux;host7" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o HC -a setmember -v "Windows;host7|host8|host9" 

The needed parameters are the following:

============ ============================================================
Order        Description
============ ============================================================
1            Host category name

2            Host names to add/set.
             For multiple definitions, use the *|* delimiter
============ ============================================================


Setseverity
-----------

In order to turn a host category into a severity, use the **SETSEVERITY** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HC -a setseverity -v "Critical;3;16x16/critical.gif" 

The needed parameters are the following:

============ ============================================================
Order        Description
============ ============================================================
1            Host category name

2            Severity level - must be a number

3            Icon that represents the severity
============ ============================================================


Unsetseverity
-------------

In order to turn a severity into a regular host category, use the **UNSETSEVERITY** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HC -a unsetseverity -v "Critical" 

The needed parameters are the following:

============ ============================================================
Order        Description
============ ============================================================
1            Host category name
============ ============================================================



Delmember
---------

In order to remove a host or a host template from a host category, use the **DELMEMBER** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o HC -a delmember -v "Linux;host7" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o HC -a delmember -v "Windows;host8" 

The needed parameters are the following:

============ ============================================================
Order        Description
============ ============================================================
1            Host category name

2            Host names to remove from host category
============ ============================================================
