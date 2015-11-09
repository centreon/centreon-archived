==========
Ndo2db CFG
==========

Overview
--------

Object name: **NDO2DBCFG**

Show
----

In order to list available Ndo2db CFG, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o NDO2DBCFG -a show 
  id;description;instance;socket type;tcp port;db servertype;db host;db name;db port;db user
  1;Principal;Central;tcp;5668;mysql;localhost;centstatus;3306;centreon
  [...]

Columns are the following :

==================== =======================================
Column	             Description
==================== =======================================
ID	             ID

Description	     Description

Instance	     Instance that is linked to ndo2db.cfg

Socket Type	     Socket type: tcp by default

TCP Port	     TCP port

Database Server Type mysql by default

Database Host	     IP Address of database

Database Name	     Database name of monitoring table

Database Port	     Database port

Database User	     Database user name
==================== =======================================


Add
---

In order to add an Ndo2db CFG you use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o NDO2DBCFG -a add -v "ndo2db for poller test;Poller test" 

Required fields are:

================ ========================================
Column	         Description
================ ========================================
Description	 Description

Instance	 Instance that is linked to ndo2db.cfg
================ ========================================

Del
---

If you want to remove a Ndo2db configuration, use the **DEL** action. The Description is used for identifying the configuration to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o NDO2DBCFG -a del -v "ndo2db for poller test" 


Setparam
--------

If you want to change a specific parameter of an Ndo2db configuration, use the **SETPARAM** action. The Description is used for identifying the configuration to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o NDO2DBCFG -a setparam -v "ndo2db for poller test;db_host;10.30.2.95" 

Parameters are composed of the following columns:

=========== ============================
Order	    Description
=========== ============================
1	    Name of Ndo2db configuration

2	    Parameter name

3	    Parameter value
=========== ============================


Parameters that you may change are:

============================== =======================================================================================
Column	                       Description
============================== =======================================================================================
description	               Description

ndo2db_user	               default: nagios

ndo2db_group	               default: nagios

socket_type	               default: tcp

socket_name	               default: /var/run/ndo.sock

tcp_port	               default: 5668

db_servertype	               default: mysql

db_host	                       IP Address of database server

db_name	                       default: centreon_status, name of database

db_port                        default: 3306, port of database

db_prefix	               default: nagios\_, prefix of tables

db_user	                       database user

db_pass	                       database password

max_timedevents_age	       default: 1440, event history retention retention (minutes)

max_systemcommands_age	       default: 1440, command history retention duration (minutes)

max_servicechecks_age	       default: 1440, service check history retention duration (minutes)

max_hostchecks_age	       default: 1440, host check history retention duration (minutes)

max_eventhandlers_age	       default: 1440, event handler history retention duration (minutes)

activate	               *1* if activated, *0* otherwise
============================== =======================================================================================

