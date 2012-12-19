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
