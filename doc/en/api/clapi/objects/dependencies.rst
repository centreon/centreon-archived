============
Dependencies
============

Overview
--------

Object name: **DEP**

Show
----

In order to list available dependencies, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DEP -a show
  id;name;description;inherits_parent;execution_failure_criteria;notification_failure_criteria
  62;my dependency;a description;1;n;n

Columns are the following:

================================= ===========================================================================
Column	                          Description
================================= ===========================================================================
ID	                              Unique ID of the dependency

Name	                          Name

Description	                      Short description of the dependency

inherits_parent					  Whether or not dependency inherits higher level dependencies

execution_failure_criteria        Defines which parent states prevent dependent resources from being checked

notification_failure_criteria     Defines which parent states prevent notifications on dependent resources
================================= ===========================================================================


Add
---

In order to add a new dependency, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DEP -a ADD \
  -v "my new dependency;any description;HOST;dummy-host" 


The required parameters are the following:

========= ============================================
Order     Description
========= ============================================
1         Name of the dependency

2         Description of the dependency

3         Dependency type: HOST, HG, SG, SERVICE, META

4         Name of the parent resource(s)
========= ============================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Del
---

In order to delete a dependency, use the **DEL** action. The dependency name is used for identifying the dependency you would like to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DEP -a DEL -v "my dependency" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Setparam
--------

In order to set a specific parameter for a dependency, use the **SETPARAM** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DEP -a setparam \
  -v "my dependency;name;my new dependency name" 

You may change the following parameters:

============================== =============================
Parameter	                   Description
============================== =============================
name	                       Name

description	                   Description

comment	                       Comment

inherits_parent	               *0* or *1*

execution_failure_criteria     o,w,u,c,p,d,n

notification_failure_criteria  o,w,u,c,p,d,n
============================== =============================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Listdep
-------

If you want to retrieve the dependency definition of a dependency object, use the **LISTDEP** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DEP -a LISTDEP -v "my dependency" 
  parents;children
  HostParent1|HostParent2;HostChild1|HostChild2,ServiceChild2


Addparent and Addchild
----------------------

If you want to add a new parent or a new child in a dependency definition, use the **ADDPARENT** or **ADDCHILD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DEP -a ADDPARENT \
  -v "my dependency;my_parent_host" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o DEP -a ADDCHILD \
  -v "my dependency;my_child_host" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o DEP -a ADDCHILD \
  -v "my dependency;my_child_host2,my_child_service2" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Delparent and Delchild
----------------------

If you want to add a new parent or a new child in a dependency definition, use the **DELPARENT** or **DELCHILD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DEP -a DELPARENT \
  -v "my dependency;my_parent_host" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o DEP -a DELCHILD \
  -v "my dependency;my_child_host" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o DEP -a DELCHILD \
  -v "my dependency;my_child_host2,my_child_service2" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.
