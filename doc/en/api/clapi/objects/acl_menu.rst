========
Menu ACL
========

Overview
--------

Object name: **ACLMENU**

Show
----

In order to list available ACL Menus, use the **SHOW** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLMENU -a show 
  id;name;alias;comment;activate
  1;Configuration;Configuration;;1
  2;Monitoring + Home;Monitoring + Home;;1
  3;Reporting;Reporting;;1
  4;Graphs;Graphs;just a comment;1
  [...]

Columns are the following :

======== =======================================
Column	 Description
======== =======================================
ID	 ID

Name	 Name

Alias	 Alias

Comment	 Comment

Activate 1 when ACL Menu is enabled, 0 otherwise
======== =======================================


Add
---

In order to add an ACL Menu, use the **ADD** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLMENU -a add -v "ACL Menu test;my alias"

Required fields are:

======= ============
Column	Description
======= ============
Name	Name

Alias	Alias
======= ============


Del
---

If you want to remove an ACL Menu, use the **DEL** action. The Name is used for identifying the ACL Menu to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLMENU -a del -v "ACL Menu test" 


Setparam
--------

If you want to change a specific parameter of an ACL Menu, use the **SETPARAM** action. The Name is used for identifying the ACL Menu to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLMENU -a setparam -v "ACL Menu test;alias;my new alias" 


Arguments are composed of the following columns:

========== =======================
Order	   Column description
========== =======================
1	   Name of ACL menu rule

2	   Parameter name

3	   Parameter value
========== =======================


Parameters that you may change are:

========= =======================================
Column	  Description
========= =======================================
name	  Name

alias	  Alias

activate  1 when ACL Menu is enabled, 0 otherwise

comment   Comment
========= =======================================


Getaclgroup
-----------

If you want to retrieve the ACL Groups that are linked to a specific ACL Menu, use the **GETACLGROUP** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLMENU -a getaclgroup -v "ACL Menu test" 
  id;name
  1;ALL
  3;Operators

Arguments are composed of the following columns:

======= =======================
Order	Column description
======= =======================
1	Name of ACL menu rule
======= =======================


Grant and Revoke
----------------

If you want to grant in Read/Write, Read Only or revoke menus in an ACL Menu rule definition, use the following actions: **GRANTRW**, **GRANTRO**, **REVOKE**

Let's assume that you would like to grant full access to the [Monitoring] menu in your ACL Menu rule:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLMENU -a grantrw -v "ACL Menu test;1;Monitoring"

Then, you would like to grant access to the [Home] > [Poller statistics] menu:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLMENU -a grantrw -v "ACL Menu test;1;Home;Poller statistics"

Then, you would like to grant access in read only to the [Configuration] > [Hosts] menu:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLMENU -a grantro -v "ACL Menu test;1;Configuration;Hosts"

Then, you decide to revoke access from [Monitoring] > [Event Logs]:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLMENU -a revoke -v "ACL Menu test;1;Monitoring;Event Logs"


Arguments are composed of the following columns:

======= ============================
Order	Column description
======= ============================
1	    Name of ACL menu rule

2       Grant/revoke children menus

3	    Menu name to grant/revoke

n	    Possible sub menu name
======= ============================
