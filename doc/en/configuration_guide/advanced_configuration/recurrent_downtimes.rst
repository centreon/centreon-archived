===================
Recurrent downtimes
===================

**********
Definition
**********

A downtime period is a time period during which the notifications to a host or a service are disabled. Downtime periods are convenient during maintenance operations on a host or a service: they allow us to avoid receiving false positive.

Recurrent Downtime periods are Downtime periods that recurs repetitively.

E.g.: A back-up of the virtual machines is performed every day from 20h00 to midnight. This type of back-up has a tendency to saturate the CPU use of all the virtual machines. It is necessary to program recurrent Downtime periods on the services concerned to avoid receiving notifications from 20h00 to midnight.

.. note::
   The Downtime periods are taken into account in the calculation of the availability ratio of the resource in the menu: "Dashboard".

*************************
Types of Downtime periods
*************************
 
There are two types of Downtime periods:

* The **fixed** downtime period: This means that the downtime period takes place during exactly the time period defined.
* The **flexible** downtime period: This means that if during the time period defined the service or the host returns a Not-OK status the downtime period lasts a certain number of seconds (to be defined in the form) from the moment when the host or the status returns a Not-OK status.

*************
Configuration
*************

To add a recurrent downtime period:

1. Go into the menu: **Configuration ==> Hosts** (or **Services** depending on the type of object on which the downtime period is to be implemented)
2. In the left menu, click on **Downtimes**
3. Click on **Add**
 
.. image:: /images/user/configuration/10advanced_configuration/05recurrentdowntimes.png
      :align: center

Configuration of Downtime periods 
=================================

* The **Name** and **Description** fields serve to give a name and describe the recurrent downtime period.
* The **Enable** field serves to enable or disable the downtime period.
* The **Periods** field serves to define one or more periods of recurrent downtime periods. To add a period, click on the symbol. 

It is possible to choose three types of period:

* Weekly: to choose the days of the week
* Monthly: to choose the days of the month
* Specific date: to choose specific dates

* The **Days** field defines the day(s) concerned.
* The **Time period** field contains the time period concerned (expressed in HH:MM - HH:MM).
* The **Downtime type** field defines the type of downtime period desired.

.. note:: 
   It is possible to combine several types of periods within the same downtime period.

Relations
=========

* The **Linked with Hosts** list can be used to choose the host(s) concerned by the recurrent downtime period.
* If **Linked with Host Groups** is chosen with the list Linked with the host group all the hosts belonging to this group are concerned by the recurrent downtime period.
* The **Linked with Services** list can be used to choose the service(s) concerned by the recurrent downtime period.
* If a service group is chosen with the list **Linked with Service Groups** all the services belonging to this group are concerned by the recurrent downtime period.

.. |navigate_plus|  image:: /images/navigate_plus.png
