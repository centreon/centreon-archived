=======
Vendors
=======

Overview
--------

Object name: **VENDOR**

Show
----

In order to list available vendors, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o VENDOR -a show
  id;name;alias
  1;Cisco;Cisco Networks
  2;HP;HP Networks
  3;3com;3Com
  4;Linksys;Linksys
  6;Dell;Dell
  7;Generic;Generic
  9;Zebra;Zebra
  11;HP-Compaq;HP and Compaq Systems

Add
---

In order to add a Vendor, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o VENDOR -a add -v "DLink;DLink routers" 

Required fields are:

====== ============
Order  Description
====== ============
1      Name

2      Alias
====== ============


Del
---

If you want to remove a Vendor, use the **DEL** action. The Name is used for identifying the Vendor to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o VENDOR -a del -v "DLink" 

Setparam
--------

If you want to change a specific parameter of a Vendor, use the **SETPARAM** command. The Name is used for identifying the Vendor to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o VENDOR -a setparam -v "3com;name;HP" 

Arguments are composed of the following columns:

======== =========================
Order	 Column description
======== =========================
1	 Name of Vendor

2	 Parameter name

3	 Parameter value
======== =========================

Parameters that you may change are:

=========== =================
Column	    Description
=========== =================
name	    Name

alias	    Alias

description Description
=========== =================


Generatetraps
-------------

It is possible to generate new SNMP traps from a given MIB file. In order to do so, use the **GENERATETRAPS** command::


  [root@centreon ~]# ./centreon -u admin -p centreon -o VENDOR -a generatetraps -v "3com;/usr/share/mymibs/3com/A3COM-SWITCHING-SYSTEMS-MIB.mib" 
  [...]
  Done

  Total translations:        10
  Successful translations:   10
  Failed translations:       0

.. note::
  Make sure to put all the mib file dependencies in the /usr/share/snmp/mibs/ directory before starting the generation. Then, remove them when it is done.

Required fields are:

======== =================
Column	 Description
======== =================
Name	 Name of Vendor
Mib file File path of .mib
======== =================
