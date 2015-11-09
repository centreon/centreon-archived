=========
Downtimes
=========

Overview
--------

Object name: **DOWNTIME**

Show
----

In order to list available recurring downtimes, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a show
  id;name;description;activate
  1;mail-backup;sunday backup;1
  1;my downtime;a description;1

Columns are the following:

================================= ===========================================================================
Column	                          Description
================================= ===========================================================================
ID	                              Unique ID of the recurring downtime

Name	                          Name

Description	                      Short description of the recurring downtime

Activate     					  Whether or not the downtime is activated

================================= ===========================================================================


Add
---

In order to add a new downtime, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a ADD -v "my new downtime;any description"


The required parameters are the following:

========= ============================================
Order     Description
========= ============================================
1         Name of the downtime

2         Description of the downtime

========= ============================================


Del
---

In order to delete a downtime, use the **DEL** action. The downtime name is used for identifying the recurring downtime
you would like to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a DEL -v "my downtime" 


Setparam
--------

In order to set a specific parameter for a downtime, use the **SETPARAM** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a setparam -v "my downtime;name;my new downtime name"

You may change the following parameters:

============================== =============================
Parameter	                   Description
============================== =============================
name	                       Name

description	                   Description

============================== =============================


Listperiods
-----------

If you want to retrieve the periods set on a recurring downtime, use the **LISTPERIODS** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a LISTPERIODS -v "my downtime" 
  position;start time;end time;fixed;duration;day of week;day of month;month cycle
  1;1;23:00:00;24:00:00;1;;7;;all
  2;1;00:00:00;02:00:00;1;;;1,2;none
  3;1;13:45:00;14:40:00;1;;5;;first

Columns are the following: 

============================== ============================================================================================
Column                            Description
============================== ============================================================================================
Position                       Position of the period; used for deleting a period from
                               a recurring downtime

Start time                     Start time of the recurring downtime

End time                       End time of the recurring downtime

Fixed                          Type of downtime (1 = fixed, 0 = flexible)

Duration                       Duration of downtime when in flexible mode (seconds)

Day of week                    1 - 7 (1 = monday ... 7 = sunday)

Day of month                   1 - 31

Month cycle                    "all", "none", "first" or "last". Determines when the downtime 
							   will be effective on specific weekdays (i.e: all Sundays, last
							   Sunday of the month, first Sunday of the month...)

============================== ============================================================================================


Addweeklyperiod
---------------

In order to add a weekly period, use the **ADDWEEKLYPERIOD** action::

   [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a ADDWEEKLYPERIOD \
   -v "my downtime;00:00;04:00;0;7200;saturday,sunday" 

The above example will set a downtime every saturday and sunday between 00:00 and 04:00.

============================== ===========================================
Parameter	                   Description
============================== ===========================================
Name	                       Name of the recurring downtime

Start time	               Start time of the recurring downtime    

End time                       End time of the recurring downtime

Fixed                          0 for flexible downtime, 1 for fixed

Duration		       Duration of downtime when in flexible mode (seconds)		

Day of week                    Can be written with letters or numbers
                               (1 to 7 or monday to sunday)

============================== ===========================================


Addmonthlyperiod
----------------

In order to add a monthly period, use the **ADDMONTHLYPERIOD** action::

   [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a ADDMONTHLYPERIOD \
   -v "my downtime;19:00;22:00;1;;14,21" 


The above example will set a downtime on every 14th and 21st day for all months.

============================== ===========================================
Parameter	                   Description
============================== ===========================================
Name	                       Name of the recurring downtime

Start time	               Start time of the recurring downtime    

End time                       End time of the recurring downtime

Fixed                          0 for flexible downtime, 1 for fixed

Duration                       Duration of downtime when in flexible mode (seconds)

Day of month                   1 to 31

============================== ===========================================


Addspecificperiod
-----------------

In order to add a specific period, use the **ADDSPECIFICPERIOD** action::

   [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a ADDSPECIFICPERIOD \
   -v "my downtime;19:00;22:00;1;;wednesday;first" 


The above example will set a downtime on every first wednesday for all months.


============================== ===========================================
Parameter	                   Description
============================== ===========================================
Name	                       Name of the recurring downtime

Start time	               Start time of the recurring downtime

End time                       End time of the recurring downtime

Fixed                          0 for flexible downtime, 1 for fixed

Duration                       Duration of downtime when in flexible mode (seconds)

Day of week                    Can be written with letters or numbers
                               (1 to 7 or monday to sunday)

Month cycle                    first or last

============================== ===========================================


Addhost, addhostgroup, addservice, addservicegroup
--------------------------------------------------

If you want to associate a host, host group, service or service group to a recurring downtime, use the
**ADDHOST**, **ADDHOSTGROUP**, **ADDSERVICE** or **ADDSERVICEGROUP** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a ADDHOST -v "my downtime;host_1"
  [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a ADDSERVICE -v "my downtime;host_1,service_1"

Use the "|" delimiter in order to define multiple relationships.


Delhost, delhostgroup, delservice, delservicegroup
--------------------------------------------------

If you want to remove a host, host group, service or service group from a recurring downtime, use the
**DELHOST**, **DELHOSTGROUP**, **DELSERVICE** or **DELSERVICEGROUP** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a DELHOST -v "my downtime;host_1"
  [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a DELSERVICE -v "my downtime;host_1,service_1"


Sethost, sethostgroup, setservice, setservicegroup
--------------------------------------------------

The **SETHOST**, **SETHOSTGROUP**, **SETSERVICE** AND **SETSERVICEGROUP** actions are similar to their **ADD** 
counterparts, but they will overwrite the relationship definitions instead of appending them::

  [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a ADDHOST -v "my downtime;host_1|host_2"
  [root@centreon ~]# ./centreon -u admin -p centreon -o DOWNTIME -a ADDSERVICE -v "my downtime;host_1,service_1|host_2,service_2"

Use the "|" delimiter in order to define multiple relationships.
