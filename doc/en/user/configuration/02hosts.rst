.. _hostconfiguration:

=====
Hosts
=====

A host is any entity having an IP address corresponding to a resource of the information system.
E.g.: A server, network printer, a NAS server, a temperature sensor, an IP camera, etc.

All these host additions take place in the menu: **Configuration** ==> **Hosts** ==> **Add**.

.. image :: /images/user/configuration/02addhost.png
   :align: center

*************************
Configuration of the host
*************************

General information
===================

*	The **Host Name** field defines the host name that will be used by the Monitoring Engine.
*	The **Alias** field shows the alias of the host.
*	The **IP address / DNS** field defines IP address or DNS name of the host. The **Resolve** button enables us to resolve the domain name by questioning the DNS server configured on the central server.
*	The **SNMP Community & Version** fields contain the name of the community and the SNMP version.
*	The **Monitored from** field indicates which poller server is charged with monitoring this host.
*	The **Host Templates** field enables us to associated one or more models of hosts with this object. To add  a host model, click on the button |navigate_plus|.

In case of conflicts of settings present on multiple models, the host model above overwrites the identical  properties defined in host models below.
The button |move| enables us to change the order of host models. The button |delete| serves to delete the host model.

*	If the **Create Services linked to the Template too** field is defined as **Yes**, Centreon automatically generates the services based their self on the service templates linked to the host templates defined above (see the chapter :ref:`hosttemplates`).


Monitoring properties of the host
=================================

*	The **Check Period** field defines the time period during which the scheduler checks the status of the object.
*	The **Check Command** field indicates the command use to check the availability of the host.
*	The **Args** field defines the arguments given to the check command (each argument starts with a ”!”).
*	The **Max Check Attempts** field defines the number of checks to be performed before confirming the status of the host: when the status is confirmed the notification process is triggered.
*	The **Normal Check Interval** is expressed in minutes. It defined the interval between checks when the host status is OK.
*	The **Retry Check Interval** is expressed in minutes. It defined the check interval of the Not-OK status of the host.
*	The **Active Checks Enabled** and **Passive Checks Enabled** fields enable / disable the active and passive checks.

Macros
======

The Macros part serves to add custom macros.

*	The **Macro name** and **Macro value** field enable us to define the name and value of the macro.
*	The **Password** box enables the value of the macro to be hidden.

To delete the macro, click on |delete|.
To change the order of the macros, click on |move|.

Notification
============

*	The **Notification Enabled** field enables us to enable or disable the notifications concerning the object.
*	If the **Contact additive inheritance** box is checked, Centreon does not overwrite the configuration of the parent host model but adds the contacts in addition to the contacts defined in the parent model.
*	The list of **Linked contacts** indicates the contacts which will receive the notifications.
*	If the **Contact group additive inheritance** box is checked, Centreon does not overwrite the configuration of the parent host template but adds the contact groups in addition to the contact groups defined in the parent template.
*	Within the **Linked Contact Groups** list all the contacts belonging to the contact groups defined will receive the notifications.
*	The **Notification Interval** is expressed in minutes. It indicates the time between sending each notifications when the status is Not-OK. If the value is defined as 0 the scheduler sends a single notification per status change.
*	The **Notification Period** field indicates the time period during which the notifications will be enabled.
*	The **Notification Options** define the statuses for which a notification will be sent.
*	The **First notification delay** is expressed in minutes. It refers to the time delay to be respected before sending the first notification when a Not-OK status is validated.

*************
Relations tab
*************

*	The **Parent Host Groups** list defined the host groups to which the host belongs.
*	The **Parent Host Categories** list defined the categories to which the host belongs.
*	The **Parent Hosts** list enables us to define the physical family relationships between objects.
*	The **Child Hosts** list enables us to define the physical family relationships between objects.

*******************
Data processing tab
*******************

*	If **Obsess Over Host** is enabled, the host check feedback command will be enabled.
*	The **Check Freshness** field allows us to enable or disable the result freshness check.
*	The **Freshness Threshold** is expressed in seconds. if during this period no host status change request (passive command) is received the active check command is executed.
*	The **Flap Detection Enabled** field allows us to enable or disable the detection flapping in the statuses (status value changing too often on a given period).
*	The **Low Flap Threshold** and **High Flap Threshold** fields define the high and low thresholds for the detection of flapping in percentage of status change.
*	The **Process Perf Data** field allows us to enable or disable performance data processing (and so the generation of performance graphics). This option is not necessary when Centreon Broker is use.
*	The **Retain Status Information** and **Retain Non Status Information** fields indicate if the information concerning the status is saved after every time the check command is repeated.
*	The **Stalking Options** field defined the options to be recorded if retention is enabled.
*	The **Event Handler Enabled** field allows us to enable or disable the events handler.
*	The **Event Handler** field defined the command to be executed if the event handler is enabled.
*	The **Args** field defined the arguments of the events handler command.

***********************
Host Extended Infos tab
***********************

Monitoring engine
=================

*	The **URL** field defined a URL that can be used to give more information on the host.
*	The **Notes** field permits us to add  optional notes concerning the host.
*	The **Action URL** field defined a URL normally use for giving information on actions on the host (maintenance, etc.).
*	The **Icon** field indicates the icon use for the host.
*	The **Alt Icon** field is the text use if the icon cannot be Display.
*   The **Severity level** field indicates the severity level of the host.

The fields presented below are fields that are only use by the CGI of the scheduler (usually Nagios). Consequently, they do not present much interest if Centreon Engine and Centreon Broker are in use.

*	The **VRML image** field defined the logo for the 3D engine of the host (not compatible with Centreon Engine).
*	The **Status Map Image** field defined the logo for the scheduler CGI.
*	The **2d Coords** and **3d Coords** fields indicates the 2D and 3D coordinates use by the CGI.

Access groups
=============

* The **ACL Resource Groups** (only displayed for non administrator) allows to link this host to an hostgroup in order to visualise it (See :ref:`acl` chapter).

Additional Information
======================

*	The **Status** field allows us to enable or disable the host.
*	The **Comments** field can be used to add a comment concerning the host.

.. |delete|    image:: /images/delete.png
.. |move|    image:: /images/move.png
.. |navigate_plus|    image:: /images/navigate_plus.png

