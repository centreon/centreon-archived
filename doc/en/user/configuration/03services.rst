.. _serviceconfiguration:

========
Services
========

A service is a check point linked / attached to a host. E.g.: Percentage of partition use on a server, ink level in a printer.

All additions of services are done in the menu: **Configuration ==> Services ==> Add**.

.. image :: /images/user/configuration/03addservice.png
      :align: center

****************************
Configuration of the service
****************************

General information
===================

* The **Description** field defined the name of the service.
* The **Service template** field indicates the model of service to which the service is linked.

Service status
==============

* The field **Is Volatile** indicates if the service is volatile or not (normally only passive services are volatile).
* The **Check Period** field defined the time period during which the scheduler checks the status of the service.
* The **Check Command** field indicates the command use to check the availability of the service.
* The **Argument** table defined the arguments given for the check command (the number of arguments varies according to the check command chosen).
* The **Max Check Attempts** of the status field defined the number of checks to be carried out to confirm the status of the service. When the status is validated, the notification process is engaged
* The **Normal Check Interval** field is expressed in minutes. It defined the interval between checks when the service status is OK.
* The **Retry Check Interval** field is expressed in minutes. It defined the confirmation interval for the Not-OK service status
* The **Active Checks Enabled** and **Passive Checks Enabled** fields enable / disable the type of check on the service.

Macros
======

The **Macros** part serves to add customised macros. 
The **macro name** and **macro value** fields allow us to define the name and value of the macro. The **Password** box can be used to hide the value of the macro.

To delete the macro, click on |delete|.
To change the order of the macros, click on |move|.

Notification
============

* The **Notification Enabled** field allows us to enable or disable the notifications for the object.
* The **Inherit contacts from host** field allows us to cause the contacts to be inherited from the configuration of the host.
* If the **Contact additive inheritance** box is checked, Centreon does not overwrite the configuration of the parent service model but adds the contacts in addition to the contacts defined at the parent model level.
* The **Implied Contacts** indicates the contacts that will receive the notifications.
* If **Contact group additive inheritance** box is checked, Centreon does not overwrite the configuration of the parent service model but adds the contact groups in addition to the contact groups defined at the parent model level.
* In the **Implied Contact Groups** list all the contacts belonging to the contact groups defined will receive the notifications.
* The **Notification Interval** field is expressed in minutes. It indicates the time between sending of notifications when the status is Not-OK. If the value is defined as 0 the scheduler sends a single notification per status change.
* The **Notification Type** define the statuses for which a notification will be sent.
* The **First notification delay** time is expressed in minutes. It refers to the time delay to be respected before sending the first notification when a Not-OK status is validated.

*************
Relations tab
*************

Relations
=========

* The **Linked to hosts** list allows us to define the host(s) to which to link this service.
* The **Linked to service groups** list allows us to link the service to one or more service groups.

SNMP traps 
==========

The **SNMP Traps linked to the service** field allows us to define the SNMP traps that will be able to change the behavior of the service.

***************
Data processing
***************

* If the **Obsess over service** field is enabled, the monitoring feedback command of the host will be enabled.
* The **Check freshness** field allows us to enable or disable the check on the freshness of the result.
* The **Freshness threshold** field is expressed in seconds. If during this period no request for a change in the status of the service (passive command) is received the check command is executed.
* The **Flap Detection Enabled** field allows us to enable or disable the detection of disruption in the statuses (status value changing too often on a given period).
* The **Low flap threshold** and **High flap threshold** fields define the high and low thresholds for the detection of disruption in percentage of status change.
* The **Performance data processing** field allows us to enable or disable performance data processing (and hence the generation of performance graphics). This option is not necessary when Centreon Broker is use.
* The **Retain status information** and **Retention non status information** fields indicate if the information concerning or not concerning the status is saved after every time the check command is repeated.
* The **Stalking Options** field defined the options to be recorded if retention is enabled.
* The **Event handler enabled** field allows us to enable or disable the events manager.
* The **Event handler** field defined the command to be executed if the event manager is enabled.
* The **Args** field defined the arguments of the events handler command.

*************************************
Additional information on the service
*************************************

Centreon
========

* **Graphics template**: Defines the graphics model to be use to present the performance data linked to the service.
* **Categories**: Defines the category(s) to which the service belongs.

Monitoring engine
=================

* The **URL** field defined a URL that can be used to give more information on the service.
* The **Notes** field permits us to add  optional notes concerning the service.
* The **Action** URL field defined a URL normally use for giving information on actions on the service (maintenance, etc.).
* The **Icon** field indicates the icon use for the service.
* The **Alternative icon** field is the text use if the icon cannot be Displays.
* The **Severity level** field indicates the criticality level of the service.

Additional information 
======================

* The **Status** field allows us to enable or disable the service.
* The **Comment** field can be used to add a comment concerning the service.

***********************
Detachment of a service
***********************

If a service is linked to several hosts, it will be identical for each one of them. Hence it will not be possible to modify the service of one host individually to change a property. This why it is possible to convert this service linked to multiple hosts into a single service for each host:

#.      In the list of services, select the service linked to multiple hosts (this service is usually highlighted in orange)
#.      In the **more actions....**  menu click on **Detach** and confirm

There is now a single service per host.

.. |delete|    image:: /images/delete.png
.. |move|    image:: /images/move.png


