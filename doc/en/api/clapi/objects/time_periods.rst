============
Time periods
============

Overview
--------

Object name: **TIMEPERIOD**

Show
----

In order to list available time periods, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TP -a show
  id;name;alias;sunday;monday;tuesday;wednesday;thursday;friday,saturday
  1;24x7;24_Hours_A_Day,_7_Days_A_Week;00:00-24:00;00:00-24:00;00:00-24:00;00:00-24:00;00:00-24:00;00:00-24:00;00:00-24:00
  2;none;No Time Is A Good Time;;;;;;;
  3;nonworkhours;Non-Work Hours;00:00-24:00;00:00-09:00,17:00-24:00;00:00-09:00,17:00-24:00;00:00-09:00,17:00-24:00;00:00-09:00,17:00-24:00;00:00-09:00,17:00-24:00;00:00-24:00
  4;workhours;Work hours;;09:00-17:00;09:00-17:00;09:00-17:00;09:00-17:00;09:00-17:00;


Add
---

In order to add a Time Period, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TP -a add -v "Timeperiod_Test;Timeperiod_Test" 

Required fields are:

======== ============
Order	 Description
======== ============
1	 Name

2	 Alias
======== ============


Del
---

If you want to remove a Time Period, use the **DEL** action. The Name is used for identifying the Time Period to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TP -a del -v "Timeperiod_Test" 


Setparam
--------

If you want to change a specific parameter of a time period, use the **SETPARAM** action. The Name is used for identifying the Time Period to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TP -a setparam -v "Timeperiod_Test;monday;00:00-24:00" 

Arguments are composed of the following columns:

======== ======================
Order	 Column description
======== ======================
1	 Name of time period

2	 Parameter name

3	 Parameter value
======== ======================


Parameters that you may change are:

========== ==============================================================
Column	   Description
========== ==============================================================
name	   Name

alias	   Alias

sunday	   Time Period definition for Sunday

monday	   Time Period definition for Monday

tuesday	   Time Period definition for Tuesday

wednesday  Time Period definition for Wednesday

thursday   Time Period definition for Thursday

friday	   Time Period definition for Friday

saturday   Time Period definition for Saturday

include	   example: [...] -v "Timeperiod_Test;include;workhours";
	   Use delimiter *|* for multiple inclusion definitions

exclude	   example: [...] -v "Timeperiod_Test;exclude;weekend"
	   use delimiter *|* for multiple exclusion definitions

========== ==============================================================


Getexception
------------

In order to view the exception list of a time period, use the **GETEXCEPTION** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TP -a getexception -v "mytimeperiod" 
  days;timerange
  january 1;00:00-00:00
  december 25;00:00-00:00


Setexception
------------

In order to set an exception on a timeperiod, use the **SETEXCEPTION** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TP -a setexception -v "mytimeperiod;january 1;00:00-24:00" 

.. note::
  If exception does not exist, it will be created, otherwise it will be overwritten.


Delexception
------------

In order to delete an exception, use the **DELEXCEPTION** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TP -a delexception -v "mytimeperiod;january 1" 

Arguments are composed of the following columns:

======= =====================================
Order	Column description
======= =====================================
1	 Name of timeperiod

2	 Exception to remove from timeperiod
======= =====================================
