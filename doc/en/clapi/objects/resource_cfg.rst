================
Resource CFG
================

Overview
--------

Object name: **RESOURCECFG**

Show
----

In order to list available Resource variables, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RESOURCECFG -a show 
  id;name;value;comment;activate;instance
  1;$USER1$;/usr/local/nagios/libexec;path to the plugins;1;Central
  [...]


Columns are the following :

=========== ============================================
Column	    Description
=========== ============================================
ID	    ID

Name	    Name

Value	    Value of $USERn$ macro

Comment	    Comment

Activate    *1* when activated, *0* otherwise

Instance    Instances that are tied to the $USERn$ macro
=========== ============================================


Add
---

In order to add a resource macro, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RESOURCECFG -a add -v "USER2;public;Poller test;my comment" 


Required fields are:

========== =================================================
Column	   Description
========== =================================================
Name	   Macro name; do not use the $ symbols

Value	   Macro value

Instances  Instances that are tied to $USERn$ macro

Comment	   Comment
========== =================================================


Del
---

If you want to remove a Resource variable, use the **DEL** action. The ID is used for identifying the variable to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RESOURCECFG -a del -v "1" 


Setparam
--------

If you want to change a specific parameter of a Resource macro, use the **SETPARAM** action. The ID is used for identifying the macro to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RESOURCECFG -a setparam -v "1;instance;Poller test|AnotherPoller" 

Arguments are composed of the following columns:

=========== ====================================
Order	    Column description
=========== ====================================
1	    Name of resource configuration

2	    Parameter name

3	    Parameter value
=========== ====================================

Parameters that you may change are:

=========== =======================================================================
Column	    Description
=========== =======================================================================
name	    Macro name; do not use the $ symbols

value	    Macro value

activate    *1* when activated, *0* otherwise

comment	    Comment

instance    Instances that are tied to $USERn$ macro
            Use delimiter *|* for multiple instance definitions
=========== =======================================================================

