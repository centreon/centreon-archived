==========
ACL Groups
==========

Overview
--------

Object name: **ACLGROUP**

Show
----

In order to list available ACL Groups, use the **SHOW** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a show 
  id;name;alias;activate
  1;ALL;ALL;1
  [...]

Columns are the following :

========= =========================================
Column	  Description
========= =========================================
ID	  ID

Name	  Name

Alias	  Alias

Activate  1 when ACL Group is enabled, 0 otherwise
========= =========================================


Add
---

In order to add an ACL Group, use the **ADD** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a add -v "ACL Group test;my alias" 

Required fields are:

======= ===========
Column	Description
======= ===========
Name	Name

Alias	Alias
======= ===========

Del
---

If you want to remove an ACL Group, use the **DEL** action. The Name is used for identifying the ACL Group to delete:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a del -v "ACL Group test"


Setparam
--------

If you want to change a specific parameter of an ACL Group, use the **SETPARAM** action. The Name is used for identifying the ACL Group to update:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a setparam -v "ACL Group test;alias;my new alias" 


Arguments are composed of the following columns:

=========== =======================
Order	    Column description
=========== =======================
1	    Name of ACL Group

2	    Parameter name

3	    Parameter value
=========== =======================


Parameters that you may change are:

=========== =========================================
Column	    Description
=========== =========================================
name	

alias	

activate    1 when ACL Group is enabled, 0 otherwise
=========== =========================================



Getmenu
-------

If you want to retrieve the Menu Rules that are linked to a specific ACL Group, use the **GETMENU** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a getmenu -v "ACL Group test" 
  id;name
  1;Configuration
  3;Reporting
  4;Graphs
  2;Monitoring + Home

Arguments are composed of the following columns:

======= ===================
Order	Column description
======= ===================
1	 Name of ACL group
======= ===================


Getaction
---------

If you want to retrieve the Action Rules that are linked to a specific ACL Group, use the **GETACTION** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a getaction -v "ACL Group test" 
  id;name
  1;Simple action rule

Arguments are composed of the following columns:

======= ===================
Order	Column description
======= ===================
1	 Name of ACL group
======= ===================


Getresource
-----------

If you want to retrieve the Resource Rules that are linked to a specific ACL Group, use the **GETRESOURCE** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a getresource -v "ACL Group test" 
  id;name
  1;All Resources

Arguments are composed of the following columns:

======= ===================
Order	Column description
======= ===================
1	Name of ACL group
======= ===================


Getcontact and Getcontactgroup
------------------------------

If you want to retrieve the Contacts that are linked to a specific ACL Group, use the **GETCONTACT** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a getcontact -v "ACL Group test" 
  id;name
  1;user1


If you want to retrieve the Contact Groups that are linked to a specific ACL Group, use the **GETCONTACTGROUP** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a getcontactgroup -v "ACL Group test" 
  id;name
  1;usergroup1

Arguments are composed of the following columns:

======= ===================
Order	Column description
======= ===================
1	Name of ACL group
======= ===================


Setmenu, Setaction, Setresource, Addmenu, Addaction, Addresource
----------------------------------------------------------------

If you want to link rules to a specific ACL Group, use the following actions: **SETMENU**, **SETACTION**, **SETRESOURCE**, **ADDMENU**, **ADDACTION**, **ADDRESOURCE**::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a setmenu -v "ACL Group test;Menu rule 1|Menu rule 2" 

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a addresource -v "ACL Group test;All Routers"

============= ========================================================================================
Command type  Description
============= ========================================================================================
set*	      Overwrites previous definitions. Use the delimiter | to set multiple rules

add*	      Appends new rules to the previous definitions. Use the delimiter | to add multiple rules
============= ========================================================================================

Arguments are composed of the following columns:

======== ==============================
Order	 Column description
======== ==============================
1	 Name of ACL group

2	 Name of the ACL rule to link
======== ==============================


Delmenu, Delaction, Delresource
-------------------------------

If you want to remove rules from a specific ACL Group, use the following actions: **DELMENU**, **DELACTION**, **DELRESOURCE**::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a delaction -v "ACL Group test;Ack rule|Downtime rule"

Arguments are composed of the following columns:

======== ==================================
Order	 Column description
======== ==================================
1	 Name of ACL group

2	 Name of the ACL rule to remove
======== ==================================


Setcontact, Setcontactgroup, Addcontact, Addcontactgroup
--------------------------------------------------------

If you want to link contacts or contact groups to a specific ACL Group, use the following actions: **SETCONTACT**, **SETCONTACTGROUP**, **ADDCONTACT**, **ADDCONTACTGROUP**::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a setcontact -v "ACL Group test;user1" 

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a addcontactgroup -v "ACL Group test;usergroup1" 


Arguments are composed of the following columns:

======== ==================================
Order	 Column description
======== ==================================
1	 Name of ACL group

2	 Contact/Contact group to add/set
======== ==================================


================ ===========================================================================================================
Command type	 Description
================ ===========================================================================================================
set*	         Overwrites previous definitions. Use the delimiter | to set multiple contacts/contact groups

add*	         Appends new contacts/contact groups to the previous definitions. Use the delimiter | to add multiple rules
================ ===========================================================================================================


Delcontact, Delcontactgroup
----------------------------

If you want to remove rules from a specific ACL Group, use the following actions: **DELCONTACT**, **DELCONTACTGROUP**::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLGROUP -a delcontact -v "ACL Group test;user1" 


Arguments are composed of the following columns:

======== ===============================================
Order 	 Column description
======== ===============================================
1	 Name of ACL group

2	 Contact/Contact group to remove from ACL group
======== ===============================================
