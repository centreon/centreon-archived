============
Resource ACL
============

Overview
--------

Object name: **ACLRESOURCE**

Show
----

In order to list available ACL Resources, use the **SHOW** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLRESOURCE -a show 
  id;name;alias;comment;activate
  1;All Resources;All Resources;;1
  [...]


Columns are the following :

========== =================================================
Column	   Description
========== =================================================
ID	   ID

Name	   Name

Alias	   Alias

Comment	   Comment

Activate   1 when ACL Resource is enabled, 0 otherwise
========== =================================================


Add
---

In order to add an ACL Resource, use the **ADD** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLRESOURCE -a add -v "ACL Resource test;my alias" 

Required fields are:

======= ===============
Column	Description
======= ===============
Name	Name

Alias	Alias
======= ===============


Del
---

If you want to remove an ACL Resource, use the **DEL** action. The Name is used for identifying the ACL Resource to delete:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLRESOURCE -a del -v "ACL Resource test" 


Setparam
--------

If you want to change a specific parameter of an ACL Resource, use the **SETPARAM** action. The Name is used for identifying the ACL Resource to update:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLRESOURCE -a setparam -v "ACL Resource test;alias;my new alias" 

Arguments are composed of the following columns:

======== ===========================
Order	 Column description
======== ===========================
1	 Name of ACL resource rule

2	 Parameter name

3	 Parameter value
======== ===========================


Parameters that you may change are:

======== ===========================================
Column	 Description
======== ===========================================
name	 Name

alias	 Alias

activate 1 when ACL Resource is enabled, 0 otherwise
======== ===========================================


Getaclgroup
-----------

If you want to retrieve the ACL Groups that are linked to a specific ACL Resource, use the **GETACLGROUP** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLRESOURCE -a getaclgroup -v "ACL Resource test" 
  id;name
  1;ALL
  3;Operators
  
Arguments are composed of the following columns:

======= ===================
Order	Column description
======= ===================
1	Name of ACL group
======= ===================


Grant and revoke
----------------

Arguments are composed of the following columns:

======= ===================
Order	Column description
======= ===================
1	 Name of ACL group

2	 Name of resource
======= ===================

If you want to grant or revoke resources in an ACL Resource rule definition, use the following commands:

========================= ================================== ===================================================================== ============================
Command	                  Description	                     Example	                                                           Wildcard '*' supported
========================= ================================== ===================================================================== ============================
grant_host	          Put host name(s)	             [...] -a grant_host -v "ACL Resource Test;srv-esx"	                   Yes
grant_hostgroup	          Put hostgroup name(s)	             [...] -a grant_hostgroup -v "ACL Resource Test;Linux servers"	   Yes
grant_servicegroup	  Put servicegroup name(s)	     [...] -a grant_servicegruop -v "ACL Resource Test;Ping"	           Yes
grant_metaservice	  Put metaservice name(s)	     [...] -a grant_metaservice -v "ACL Resource Test;Traffic Average"	   No
addhostexclusion	  Put host name(s)	             [...] -a addhostexclusion -v "ACL Resource Test;srv-test|srv-test2"   No
revoke_host	          Put host name(s)	             [...] -a revoke_host -v "ACL Resource Test;srv-esx"	           Yes
revoke_hostgroup	  Put hostgroup name(s)	             [...] -a revoke_hostgroup -v "ACL Resource Test;Linux servers"	   Yes
revoke_servicegroup	  Put servicegroup name(s)	     [...] -a revoke_servicegroup -v "ACL Resource Test;Ping"	           Yes
revoke_metaservice	  Put metaservice name(s)	     [...] -a revoke_metaservice -v "ACL Resource Test;Traffic Average"	   Yes
addfilter_instance	  Put instance name(s)	             [...] -a addfilter_instance -v "ACL Resource Test;Monitoring-2"	   No
addfilter_hostcategory	  Put host category name(s)	     [...] -a addfilter_hostcategory -v "ACL Resource Test;Customer-1"	   No
addfilter_servicecategory Put service category name(s)	     [...] -a addfilter_servicecategory -v "ACL Resource Test;System"	   No
delfilter_instance	  Put instance name(s)	             [...] -a delfilter_instance -v "ACL Resource Test;Monitoring-2"	   Yes
delfilter_hostcategory	  Put host category name(s)	     [...] -a delfilter_hostcategory -v "ACL Resource Test;Customer-1"	   Yes
delfilter_servicecategory Put service category name(s)	     [...] -a delfilter_servicecategory -v "ACL Resource Test;System"	   Yes
========================= ================================== ===================================================================== ============================

.. note:: 
	Use delimiter "|" for defining multiple resources.


