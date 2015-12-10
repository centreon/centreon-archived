==========
Action ACL
==========

Overview
--------

Object name: **ACLACTION**


Show
----

In order to list available ACL Actions, use the **SHOW** action::
  
  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLACTION -a show 
  id;name;description;activate
  1;Simple User;Simple User;1
  [...]

Columns are the following:

============== ==========================================
Column         Description
============== ==========================================
ID

Name

Description

Activate       1 when ACL Action is enabled, 0 otherwise
============== ==========================================

Add
---

In order to add an ACL Action, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLACTION -a add -v "ACL Action test;my description" 


Required fields:

============== ==========================================
Column         Description
============== ==========================================
Name

Description
============== ==========================================


Del
---

If you want to remove an ACL Action, use the **DEL** action. The Name is used for identifying the ACL Action to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLACTION -a del -v "ACL Action test" 


Setparam
--------

If you want to change a specific parameter of an ACL Action, use the **SETPARAM** action. The Name is used for identifying the ACL Action to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLACTION -a setparam -v "ACL Action test;description;my new description" 

Arguments are composed of the following columns:

============== ==========================================
Order          Column description
============== ==========================================
1              Name of ACL action rule

2              Parameter name

3              Parameter value
============== ==========================================


Parameters that you may change are the following:

============== ==========================================
Column         Description
============== ==========================================
name           

description    

activate       1 when ACL Action is enabled, 0 otherwise
============== ==========================================


Getaclgroup
-----------

If you want to retrieve the ACL Groups that are linked to a specific ACL Action, use the **GETACLGROUP** command.

Arguments are composed of the following columns:

============== ==========================================
Order          Column description
============== ==========================================
1              Name of ACL action rule
============== ==========================================

Example:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLACTION -a getaclgroup -v "ACL Action test" 
  id;name
  1;ALL
  3;Operators


Grant and Revoke
----------------

If you want to grant or revoke actions in an ACL Action rule definition, use the following commands: **GRANT**, **REVOKE**.

Arguments are composed of the following columns:

============== ==========================================
Order          Column description
============== ==========================================
1              Name of ACL action rule

2              Actions to grant/revoke
============== ==========================================

Example:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLACTION -a grant -v "ACL Action test;host_acknowledgement|service_acknowledgement" 


  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLACTION -a revoke -v "ACL Action test;host_schedule_downtime|service_schedule_downtime" 


The **`*`** wildcard can be used in order to grant or revoke all actions:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLACTION -a grant -v "ACL Action test;*" 

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACLACTION -a revoke -v "ACL Action test;*" 


Below is the list of actions that you can grant/revoke:

=================================== =============================================================================
Action                              Description
=================================== =============================================================================
global_event_handler	            Permission to globally enable/disable event handlers

global_flap_detection	            Permission to globally enable/disable flap detection

global_host_checks	            Permission to globally enable/disable host active checks

global_host_obsess	            Permission to globally enable/disable obsess over host

global_host_passive_checks          Permission to globally enable/disable host passive checks

global_notifications	            Permission to globally enable/disable notifications

global_perf_data	            Permission to globally enable/disable performance data

global_restart	                    Permission to restart the monitoring engine

global_service_checks	            Permission to globally enable/disable service active checks

global_service_obsess	            Permission to globally enable/disable obsess over service

global_service_passive_checks       Permission to globally enable/disable service passive checks

global_shutdown	                    Permission to shut down the monitoring engine

host_acknowledgement	            Permission to acknowledge hosts

host_checks	                    Permission to enable/disable host active checks

host_checks_for_services	    Permission to enable/disable active checks of a host's services

host_comment	                    Permission to put comments on hosts

host_event_handler	            Permission to enable/disable event handlers on hosts

host_flap_detection	            Permission to enable/disable flap detection on hosts

host_notifications	            Permission to enable/disable notification on hosts

host_notifications_for_services	    Permission to enable/disable notification on hosts' services

host_schedule_check	            Permission to schedule a host check

host_schedule_downtime	            Permission to schedule a downtime on a host

host_schedule_forced_check	    Permission to schedule a host forced check

host_submit_result	            Permission to submit a passive check result to a host

poller_listing	                    Permission to see the Poller list on the monitoring console

poller_stats	                    Permission to see the poller statistics (on top screen)

service_acknowledgement	            Permission to acknowledge services

service_checks	                    Permission to enable/disable service active checks

service_comment	                    Permission to put comments on services

service_event_handler	            Permission to enable/disable event handlers on services

service_flap_detection	            Permission to enable/disable flap detection on services

service_notifications	            Permission to enable/disable notification on services

service_passive_checks	            Permission to enable/disable service passive checks

service_schedule_check	            Permission to schedule a service check

service_schedule_downtime	    Permission to schedule a downtime on a service

service_schedule_forced_check	    Permission to schedule a service forced check

service_submit_result	            Permission to submit a passive check result to a service

top_counter	                    Permission to see the quick status overview (top right corner of the screen)
=================================== =============================================================================
