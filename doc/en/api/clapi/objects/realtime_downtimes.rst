=========
Real time Downtimes
=========

Overview
--------

Object name: **RTDOWNTIME**

Show host real time downtime
---------------------------

In order to list available real time downtimes, use the **SHOW** action::
You can use the value "HOST" to display all the downtimes::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a show -v "HOST;generic-host"
  host_name;author;actual_start_time;actual_end_time;start_time;end_time;comment_data;duration;fixed
  generic-host;admin;2017/09/28 14:21;N/A;2017/09/26 17:00;2017/09/30 19:00;'generic-comment';3600;1

Columns are the following :

================================= ===========================================================================
Column	                          Description
================================= ===========================================================================
Host_name	                      Name of the host

Author	                          Name of the author

Actual_start_time                 Actual start date in case of flexible downtime

Actual_end_time                   Actual end date in case of flexible downtime

Start_time	                      Beginning of downtime

End_time                          End of downtime

Comment_data                      Short description of the real time downtime

Duration                          Duration of Downtime

Fixed                             Downtime starts and stops at the exact start and end times

================================= ===========================================================================

Show service real time downtime
------------------------------

In order to list available real time downtimes, use the **SHOW** action::
You can use the value "SVC" to display all the downtimes::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a show -v "SVC;generic-host,generic-service"
  host_name;service_name;author;start_time;end_time;comment_data;duration;fixed
  generic-host;generic-service;admin;2017/09/28 14:21;N/A;2017/09/26 17:00;2017/09/30 19:00;'generic-comment';3600;1

Columns are the following :

================================= ===========================================================================
Column	                          Description
================================= ===========================================================================
Host_name	                      Name of the host

Service_name	                  Name of the service

Author	                          Name of the author

Actual_start_time                 Actual start date in case of flexible downtime

Actual_end_time                   Actual end date in case of flexible downtime

Start_time	                      Beginning of downtime

End_time                          End of downtime

Comment_data                      Short description of the real time downtime

Duration                          Duration of Downtime

Fixed                             Downtime starts and stops at the exact start and end times

================================= ===========================================================================

Real time Downtime for : Addhost, addhostgroup
-----------------------------------------------------------

If you want to associate a host, host group to a real time downtime, use the **ADD** action::
To set the value of the start/end, use following format : YYYY/MM/DD HH:mm::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a add -v "HOST;central;2017/09/24 10:00;2017/09/24 12:00;1;3600;my comments;1"
  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a add -v "HG;linux-servers;2017/09/24 10:00;2017/09/24 12:00;1;3600;my comments;1"

The required parameters are the following :

========= ============================================
Order     Description
========= ============================================
1         Value you want to associate

2         Name of the host (Name of the service)

3         Beginning of downtime

4         End of downtime

5         Type of downtime (1 = fixed, 0 = flexible)

6         Duration of downtime for flexible mode (seconds)

7         Short description of the real time downtime

8         Apply downtime on linked services (0/1)

========= ============================================


Real time Downtime for : addservice, addservicegroup
-----------------------------------------------------------

If you want to associate a service or service group to a real time downtime, use the **ADD** action::
To set the value of the start/end, use following format : YYYY/MM/DD HH:mm::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a add -v "SVC;central|ping;2017/09/24 10:00;2017/09/24 12:00;1;3600;my comments"
  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a add -v "SG;servicegroup1;2017/09/24 10:00;2017/09/24 12:00;1;3600;my comments"

The required parameters are the following :

========= ============================================
Order     Description
========= ============================================
1         Value you want to associate

2         Name of the host (Name of the service)

3         Beginning of downtime

4         End of downtime

5         Type of downtime (1 = fixed, 0 = flexible)

6         Duration of downtime for flexible mode (seconds)

7         Short description of the real time downtime

========= ============================================

Add instance real time downtime
------------------------------

In order to add a new real time downtime for a poller, use the **ADD** action::
To set the value of the start/end, use following format : YYYY/MM/DD HH:mm::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTDOWNTIME -a add -v "INSTANCE;Central;2017/09/24 10:00;2017/09/24 12:00;1;3600;my comments

The required parameters are the following :

========= ============================================
Order     Description
========= ============================================
1         Value you want to associate

2         Name of the poller

3         Beginning of downtime

4         End of downtime

5         Type of downtime (1 = fixed, 0 = flexible)

6         Duration of downtime for flexible mode (seconds)

7         Short description of the real time downtime

========= ============================================
