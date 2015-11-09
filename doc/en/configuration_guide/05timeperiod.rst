============
Time periods
============

********** 
Definition
**********

A time period is the definition of a time interval for every day of the week. These time periods serve to enable the functionalities of the scheduler on a given period.

Time periods apply to two types of actions:

* Execution of  check commands
* Sending of notifications

*************
Configuration
*************

The configuration of time periods is done in the menu: **Configuration ==> Users ==> Time periods**.

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
* The character ”,” serve s to separate two time periods

Here are a few examples:

* 24 hours a day and 7 days a week: 00:00-24:00 (to be applied on every day of the week).
* From 08h00 to 12h00 and from 14h00 to 18h45 (to be applied on weekdays only).

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
|     1 january         |       00:00-24:00       |   All day on 1 January of every year                            |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|     2014-02-10        |       00:00-24:00       |   All day on 10 February 2014                                   |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|  1 july - 1 august    |       00:00-24:00       |   Every day from the 1 July to 1 August, every year             |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|     30 november       |       08:00-19:00       |   From 08h00 to 19h00 every 30 November, every year             |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|      day 1 - 20       |       00:00-24:00       |   All day from 1 to 20 of every month                           |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|     saturday -1       | 08:00-12:00,14:00-18:45 |   Every last Saturday of the month during opening hours         |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|     monday -2         |       00:00-24:00       |   Every last but one Monday of the month during all day         |
+-----------------------+-------------------------+-----------------------------------------------------------------+

Extended Settings
=================

In the extended settings, it is possible to **include** or to **exclude** periods in the definition of the object. 

Example of application: Let us take two time periods:

* One period is defined as 24 hours a day / 7 days a week, called **24x7**
* Another which covers the office opening hours, called **working_hours**

To obtain the office closing hours, we simply have to create a time period in which we include the period **24x7** and from which we exclude the **working_hours** period.

.. |navigate_plus|      image:: /images/navigate_plus.png
