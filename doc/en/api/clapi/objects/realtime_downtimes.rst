=========
Realtime Downtimes
=========

Overview
--------

Object name: **RTDOWNTIME**

Show host realtime downtime
---------------------------

In order to list available realtime downtimes, use the **SHOW** action::
You can use the value "HOST" to display all the downtimes::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a show -v "HOST;generic-host"
  host_name;author;start_time;end_time;comment_data;duration;fixed
  generic-host;admin;1506423060;1506426660;'generic-comment';3600;1

Columns are the following:

================================= ===========================================================================
Column	                          Description
================================= ===========================================================================
Host_name	                      Name of the host

Author	                          Name of the author

Actual_start	                  Beginning of downtime

Actual_end                        End of downtime

Comment_data                      Short description of the realtime downtime

Duration                          Duration of Downtime

Fixed                             Downtime starts and stops at the exact start and end times

================================= ===========================================================================

Show service realtime downtime
------------------------------

In order to list available realtime downtimes, use the **SHOW** action::
You can use the value "SVC" to display all the downtimes::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a show -v "SVC;generic-host|generic-service"
  host_name;service_name;service_name;author;start_time;end_time;comment_data;duration;fixed
  generic-host;generic-service;admin;1506423060;1506426660;'generic-comment';3600;1

Columns are the following:

================================= ===========================================================================
Column	                          Description
================================= ===========================================================================
Host_name	                      Name of the host

Service_name	                  Name of the service

Author	                          Name of the author

Actual_start	                  Beginning of downtime

Actual_end                        End of downtime

Comment_data                      Short description of the realtime downtime

Duration                          Duration of Downtime

Fixed                             Downtime starts and stops at the exact start and end times

================================= ===========================================================================

Realtime Downtime for : Addhost, addhostgroup, addservice, addservicegroup
-----------------------------------------------------------

If you want to associate a host, host group, service or service group to a realtime downtime, use the
**ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a add -v "HOST;central;2017/09/24 10:00;2017/09/24 12:00;1;3600;1;my comments"
  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a add -v "SVC;central|ping;2017/09/24 10:00;2017/09/24 12:00;1;3600;1;my comments"
  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a add -v "HG;linux-servers;2017/09/24 10:00;2017/09/24 12:00;1;3600;1;my comments"
  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a add -v "SG;servicegroup1;2017/09/24 10:00;2017/09/24 12:00;1;3600;1;my comments"

The required parameters are the following:

========= ============================================
Order     Description
========= ============================================
1         Value you want to associate

2         Name of the host (Name of the service)

3         Beginning of downtime

4         End of downtime

5         Type of downtime (1 = fixed, 0 = flexible)

6         Duration of downtime for flexible mode (seconds)

7         Apply downtime on linked services (0/1)

8         Short description of the realtime downtime

========= ============================================

Add instance realtime downtime
------------------------------

In order to add a new realtime downtime for a poller, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a add -v "INSTANCE;Central;2017/09/24 10:00;2017/09/24 12:00;1;3600;1;my comments

The required parameters are the following:

========= ============================================
Order     Description
========= ============================================
1         Value you want to associate

2         Name of the poller

3         Beginning of downtime

4         End of downtime

5         Type of downtime (1 = fixed, 0 = flexible)

6         Duration of downtime for flexible mode (seconds)

7         Apply downtime on linked services (0/1)

8         Short description of the realtime downtime

========= ============================================
