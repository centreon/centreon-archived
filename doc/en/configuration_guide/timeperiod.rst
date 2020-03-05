============
Time periods
============

********** 
Definition
**********

A time period is the definition of a time interval for each day of the week. These time periods enable the functionalities of the scheduler over a given period of time.

Time periods apply to two types of actions:

* Execution of  check commands
* Sending of notifications

*************
Configuration
*************

The configuration of time periods is done in the menu: **Configuration > Users > Time periods**.

Basic options 
=============

* The **Time period name** and **Alias** fields define the name and description of the time period respectively.
* The fields belonging to the **Time range** sub-category define the days of the week for which it is necessary to define time periods.
* The **Exceptions** table enables us to include days excluded from the time period.

Syntax of a time period
=======================

When creating a time period, the following characters serve to define the time periods :

* The character “:” separates the hours from the minutes. E.g.: HH:MM
* The character “-” indicates continuity between two time periods
* The character ”,” serves to separate two time periods

Here are a few examples:

* 24 hours a day and 7 days a week: 00:00-24:00 (to be applied on every day of the week).
* From 08h00 to 12h00 and from 14h00 to 18h45 on weekdays: 08:00-12:00,14:00-18:45 (to be applied on weekdays only).

.. image :: /images/user/configuration/05timeperiod.png
      :align: center

Time Range exceptions
=====================

The exceptions allow us to include exceptional days in the time period (overload of the definition of regular functioning of the day).

E.g.: An administrator wants to define a time period which covers the times when the offices are closed i.e.:

* From 18h00 to 07h59 on weekdays
* Round the clock at weekends
* National holidays and exceptional closure days

To be able to define the national holidays days and the exceptional closure days, it is necessary to use the exceptions.
To add  an exception, click on the button |navigate_plus|. 
For each exceptional day, you will need to define a time period. The table below shows some possible examples :

+-----------------------+-------------------------+-----------------------------------------------------------------+
|         Day(s)        |       Time period       |                            Meaning                              |
+=======================+=========================+=================================================================+
|     january 1         |       00:00-24:00       |   All day on the 1st of January, every year.                    |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|     2014-02-10        |       00:00-24:00       |   All day on 10 February 2014                                   |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|  july 1 - august 1    |       00:00-24:00       |   All day, every day from July 1 to August 1, every year        |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|     november 30       |       08:00-19:00       |   From 08h00 to 19h00 every November 30, every year             |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|      day 1 - 20       |       00:00-24:00       |   All day from the 1st to 20th of every month                   |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|     saturday -1       | 08:00-12:00,14:00-18:45 |   Every last Saturday of the month during opening hours         |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|     monday -2         |       00:00-24:00       |   All day every second to the last Monday of the month          |
+-----------------------+-------------------------+-----------------------------------------------------------------+

.. |navigate_plus|      image:: /images/navigate_plus.png
