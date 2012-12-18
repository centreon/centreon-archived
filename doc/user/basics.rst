######
Basics
######

Here, you will see how to add your own resources, how to monitor them
and how to get notified when problems arise.

********
Commands
********

Check Commands
==============

Check commands are used for checking hardware and/or application
statuses of your Hosts/Services.

Command Creation
----------------

.. image:: /_static/images/user/add_command_1.png
   :align: center



.. image:: /_static/images/user/check_arg_2.png
   :align: center

========================  ==============================================================================
 Field name                Description
========================  ==============================================================================
 Command Name              Name which will be used for identifying the command

 Command Type              Select the *Check* type

 Command Line              This will be executed by the scheduler, note that this line 
                           contains macros that will be replaced before execution. Always 
                           possible macros when possible. e.g: ``$USER1$/check_centreon_dummy``

 Argument example          This will provide argument example to the end users. The example 
                           apply to ``$ARGn$`` macros only and the expression is separated by the ``!``
                           character. In our case, *Hello world* will match ``$ARG1$`` and *0* will 
                           match ``$ARG2$``

========================  ==============================================================================

End users may not know the meaning of the arguments even though you
provided an example. You can hit the *Describe argument* button and
give a description to each of your ``$ARGn$`` macros.

.. image:: /_static/images/user/check_arg_3.png
   :align: center

Hit the *Save* button of the modal box to apply the descriptions, then
hit the *Save* button of the form to save your check command.

Notification Commands
=====================

Notification commands work pretty much like check commands but they
are used for notifying users and ``$ARGn$`` are not supported here.

.. image:: /_static/images/user/notif_check_1.png
   :align: center

Select the *Notification* type. The following command line will send
an email to the contact with the **mail** binary:

.. image:: /_static/images/user/notif_cmd_2.png
   :align: center

********
Contacts
********

Contacts are used for two main purposes:

* for logging in Centreon user interface
* for notifications

Now, let's see how to add a new user:

.. image:: /_static/images/user/add_user_1.png
   :align: center

Fill the mandatory fields:

.. image:: /_static/images/user/add_user_2.png
   :align: center

======================  =============================================================
 Field name              Description                                                   
======================  =============================================================
 Full Name               Usually the first name and the last name of the user          
 Alias / Login           Used for logging in                                           
 Email                   E-mail address of the user, used for notification purpose     
 Enable Notifications    Choose whether or not the user will receive notifications     
======================  =============================================================

For more information regarding Notification configuration, refer to this documentation.

.. image:: /_static/images/user/add_user_3.png
   :align: center

=====================================  ===============================================================
 Field name                             Description                                                     
=====================================  ===============================================================
 Reach Centreon Front-end               Choose whether or not the user can acccess the user interface   
 Password                               Password used for logging in                                    
 Admin                                  Choose whether or not the user is an administrator              
=====================================  ===============================================================

Hit the save button to add the new user.

*****
Hosts
*****

Hosts are basically devices that you monitor. Most of the time they
are just servers, routers, switches, firewalls, temperature probes
etc... Anything that own an IP address and that can communicate with
the Centreon server can be monitored.

.. image:: /_static/images/user/add_host_1.png
   :align: center

Fill the main fields of the form:

.. image:: /_static/images/user/add_host_2.png
   :align: center

======================== =================================================================================
Field Names              Description                                                                     
======================== =================================================================================
Host Name                Name used for identifying the host.                                             

Alias                    Description of the host.                                                        

IP Address / DNS         IP address that will be used by most check plugins.                             

Host Templates           Templates are used for quick deployment. You can leave the parameters empty if 
                         you wish to use the ones that are set on the template. You can also set 
                         multiple templates.

Check Period             Time Period within which checks will be actively made.                          

Check Command            Check command that will be used for checking the status of the host. It is 
                         usually a ping check plugin that is behind a host check command.
Args                     ``$ARGn$`` arguments that will be used with the check command.                      

Max Check Attempts       Number of checks necessary to make sure that a Host is really DOWN (HARD state).

Normal Check Interval    The check frequency. e.g: Centreon-Server will be checked every 5 minutes.      

Retry Check Interval     The check frequency that will be used when a Host goes DOWN.                    

Notification Enabled     Whether or not notification is enabled for this Host.                           

Linked Contacts          Contacts that will be notified when the Host is subject to a status change.     

Notification Interval    Notification frequency. e.g: admin user will be notified only once.             

Notification Period      Period within which, notification will be sent out regarding the Host.          

Notification Options     Statuses for which notification will be sent out. e.g: notifications will be
                         sent out only if Centreon-Server goes DOWN.
======================== =================================================================================

********
Services
********

Services are used for monitoring hardware and/or applications of a Host.

.. image:: /_static/images/user/add_svc_1.png
   :align: center

Fill the fields:

.. image:: /_static/images/user/add_svc_2.png
   :align: center

======================  ==================================================================================================================================================
 Field Names             Description
======================  ==================================================================================================================================================
 Description             Description of the service.

 Service Template        Templates are used for quick deployment. You can leave the parameters empty if you wish to use the ones that are set on the template.

 Check Period            Time Period within which checks will be actively made.

 Check Command           Check command that will be used for checking the status of the service. It is usually a ping check plugin that is behind a service check command.

 Args                    $ARGn$ arguments that will be used with the check command.

 Max Check Attempts      Number of checks necessary to make sure that the Service is really non OK (HARD state).

 Normal Check Interval   The check frequency. e.g: The traffic service will be checked every 5 minutes.

 Retry Check Interval    The check frequency that will be used when the Service goes to an non OK status.

 Notification Enabled    Whether or not notification is enabled for this Service.

 Linked Contacts         Contacts that will be notified when the Service is subject to a status change.

 Notification Interval   Notification frequency. e.g: admin user will be notified every 5 minutes.

 Notification Period     Period within which, notification will be sent out regarding the Service.

 Notification Options    Statuses for which notification will be sent out. e.g: notifications will be sent out only if Centreon-Server goes WARNING or CRITICAL.
======================  ==================================================================================================================================================

Link this service to a Host:

.. image:: /_static/images/user/add_svc_3.png
   :align: center

Though it is possible to make one Service linked to multiple Hosts, we
strongly advise you not to do so. Refer to the "Best Practices" section.

Hit the *Save* button to add this service.

************
Time Periods
************

The installation of Centreon comes with default timeperiods which
should be sufficient for basic usage. Nonetheless, timeperiods are an
important component of our monitoring system. To put it simply, they
are used for notifications (notification periods) and checks (check
periods).

.. image:: /_static/images/user/add_tp_1.png
   :align: center

Here, you can specify the time range which is covered by the time
period, for each day:

.. image:: /_static/images/user/add_tp_2.png
   :align: center

===================  =====================================
Field name           Description 
===================  =====================================
Time Period Name     Name for identifying the timeperiod
Alias                Short description of the timeperiod
===================  =====================================

.. _acl:

***
ACL
***

It is possible to configure ACL rules in order to restrict access to non admin users. 

.. image:: /_static/images/user/acl/acl_1.png
   :align: center


Access Groups
=============

Obviously, you will need to have contacts and/or contact groups defined before going any further. You can now start with the Access Group configuration:

.. image:: /_static/images/user/acl/acl_conf_1.png
   :align: center

======================  =====================================================
Field name              Description
======================  =====================================================
Group Name              Name for identifying the Access Group
Alias                   Short description of the Access Group
Linked Contacts	        Users who will be part of the Access Group
Linked Contact Groups   Contact Groups that will be part of the Access Group 
======================  =====================================================


Menu Access Rules
=================

Then, you can define the Menu access rules that will be applied to your Access Group:

.. image:: /_static/images/user/acl/acl_conf_2.png
   :align: center

======================  =====================================================
Field name              Description
======================  =====================================================
ACL Definition          Name for identifying the Access Rule
Alias                   Short description of the Access Rule
Status                  Whether or not the rule is enabled
Linked Groups           ACL rule will be applied to these Access Groups
======================  =====================================================

Grant access on the pages to the Access Groups:

.. image:: /_static/images/user/acl/acl_conf_3.png
   :align: center

It is possible to define *Read Only* restriction on some menus:

.. image:: /_static/images/user/acl/acl_conf_4.png
   :align: center


Action Access Rules
===================

Define the action privileges that will be granted to the Access groups.

Global Functionalities
----------------------

======================================= =====================================================
Action name                             Elements on Centreon User Interface
======================================= =====================================================
Display Top Counter	                .. image:: /_static/images/user/acl/acl_conf_8.png
Display Top Counter pollers statistics  .. image:: /_static/images/user/acl/acl_conf_9.png
Display Poller Listing                  .. image:: /_static/images/user/acl/acl_conf_10.png
======================================= =====================================================

Global Actions on Monitoring Engine
-----------------------------------

+-----------------------------------------+----------------------------------------------------+
| Action name                             |  Elements on Centreon User Interface               |
+=========================================+====================================================+
| Shutdown Monitoring Engine              |                                                    |
+-----------------------------------------+                                                    |
| Restart Monitoring Engine               |                                                    |
+-----------------------------------------+                                                    |
| Enable/Disable notifications	          |                                                    |
+-----------------------------------------+                                                    |
| Enable/Disable service checks           |                                                    |
+-----------------------------------------+                                                    |
| Enable/Disable passive service checks   |                                                    |
+-----------------------------------------+                                                    |
| Enable/Disable host checks              |                                                    |
+-----------------------------------------+                                                    |
| Enable/Disable passive host checks      | .. image:: /_static/images/user/acl/acl_conf_11.png|
+-----------------------------------------+                                                    |
| Enable/Disable Event Handlers           |                                                    |
+-----------------------------------------+                                                    |
| Enable/Disable Flap Detection           |                                                    |
+-----------------------------------------+                                                    |
| Enable/Disable Obsessive service checks |                                                    |
+-----------------------------------------+                                                    |
| Enable/Disable Obsessive host checks    |                                                    |
+-----------------------------------------+                                                    |
| Enable/Disable Performance Data         |                                                    |
+-----------------------------------------+----------------------------------------------------+


Actions on Services
-------------------

+---------------------------------------------------+----------------------------------------------------+
| Action name                                       |  Elements on Centreon User Interface               |
+===================================================+====================================================+
| Enable/Disable Checks for a service               |                                                    |
+---------------------------------------------------+                                                    |
| Enable/Disable passive checks of a service        | Monitoring service detail page:                    |
+---------------------------------------------------+                                                    |
| Enable/Disable Notifications for a service        | .. image:: /_static/images/user/acl/acl_conf_12.png|
+---------------------------------------------------+                                                    |
| Enable/Disable Event Handler for a service        |                                                    |
+---------------------------------------------------+                                                    |
| Enable/Disable Flap Detection of a service        |                                                    |
+---------------------------------------------------+                                                    |
| Acknowledge/Disacknowledge a service              |                                                    |
+---------------------------------------------------+----------------------------------------------------+
| Re-schedule the next check for a service          |                                                    |
+---------------------------------------------------+                                                    |
| Re-schedule the next check for a service (Forced) | Monitoring service detail page:                    |
+---------------------------------------------------+                                                    |
| Schedule downtime for a service                   | .. image:: /_static/images/user/acl/acl_conf_13.png|
+---------------------------------------------------+                                                    |
| Add/Delete a comment for a service                |                                                    |
+---------------------------------------------------+                                                    |
| Submit result for a service                       |                                                    |
+---------------------------------------------------+----------------------------------------------------+

Actions on Hosts
----------------

+---------------------------------------------------+----------------------------------------------------+
| Action name                                       |  Elements on Centreon User Interface               |
+===================================================+====================================================+
| Enable/Disable Checks for a host                  |                                                    |
+---------------------------------------------------+                                                    |
| Enable/Disable passive checks of a host           | Monitoring host detail page:                       |
+---------------------------------------------------+                                                    |
| Enable/Disable Notifications for a host           | .. image:: /_static/images/user/acl/acl_conf_14.png|
+---------------------------------------------------+                                                    |
| Enable/Disable Event Handler for a host           |                                                    |
+---------------------------------------------------+                                                    |
| Enable/Disable Flap Detection for a host          |                                                    |
+---------------------------------------------------+                                                    |
| Acknowledge/Disacknowledge a host                 |                                                    |
+---------------------------------------------------+                                                    |
| Re-schedule the next check for a host             |                                                    |
+---------------------------------------------------+                                                    |
| Re-schedule the next check for a host (Forced)    |                                                    |
+---------------------------------------------------+                                                    |
| Schedule downtime for a host                      |                                                    |
+---------------------------------------------------+                                                    |
| Add/Delete a comment for a host                   |                                                    |
+---------------------------------------------------+                                                    |
| Submit result for a host                          |                                                    |
+---------------------------------------------------+----------------------------------------------------+

Resource Access Rules
=====================

At last, you can restrict access on the Hosts and Services.

.. image:: /_static/images/user/acl/acl_conf_15.png
   :align: center

======================  =====================================================
Field name              Description
======================  =====================================================
Access list name        Name of the Access Rule
Description             Short description of the Access Group
Linked Groups           ACL rule will be applied to these Access Groups
Status                  Whether or not the rule is enabled
Comments                Optional comments regarding the rule
======================  =====================================================


You can define the Access Rule in the next configuration tabs:

.. image:: /_static/images/user/acl/acl_conf_17.png
   :align: center

First of all, you need to put the list of allowed Hosts or Host Groups or Service Groups in the Access Rule. Then you may filter out these Hosts / Services for a more accurate end result of what the Access Group can view.

.. image:: /_static/images/user/acl/acl_2.png
   :align: center

.. warning::
   Resource ACL rules are not applied on the Configuration pages of Centreon.

