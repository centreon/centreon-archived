.. _categoriesandgroups:

==============================
Managing groups and categories
==============================

In Centreon, it is possible to group together one or more objects within different groups:

* :ref:`Host Groups <hostgroups>`
* :ref:`Service Groups <servicegroups>`
* :ref:`Contact Groups <contactgroups>`

It is also possible to create categories of :ref:`hosts <hostcategory>` or :ref:`services <servicecategory>`.

******
Groups
******

Generally speaking, the groups are containers in which sets of objects having a common property can be grouped together:

* Same material identity (Dell, HP, IBM, etc., servers), logical identity (network equipment) or geographical identity (Europe, Asia, Africa, North America, etc.)
* Belonging to the same application (CMS application, etc.) or to a same sector of activity (Salary management, etc.)
* Etc.

Service Groups and Host Groups
==============================

Host groups and service groups are used to group together objects by logical entities. They are used to:

* Configure ACLs to link a set of resources to a type of profile
* Allow viewing of availability reports per group. Generate a “Paris Agency” availability report for resources.
* Enable viewing the status of a set of objects by selecting in the search filters of a group of objects
* Search several performance graphs quickly by browsing the object tree structure by group and then by resource

Generally speaking, we try to group together hosts by functional level. E.g.: DELL and HP hosts or Linux, Windows, etc., hosts. 
We also try to group services by application jobs. E.g.: Salary management application, ERP Application, etc.

.. note::
   For the hosts belonging to a host group, the retention of RRD files can be defined in the host group. This definition overrides the global definition. In the event that the same host belongs to several groups each possessing a retention definition, the highest value will be selected for the host.

Contact Groups
==============

Contact Groups are used to notify contacts:

* On definition of a host or of a service
* On definition of an escalation of notifications

In addition, the groups of contacts are also used during the definition of an access group.

Consequently, it is necessary to group together contacts in a logical way. Most of the time, they are grouped together according to their roles in the information systems. E.g.: DSI, Windows Administrators, Linux Administrators, Person in charge of the application of Salary Management, etc.

.. _categoriesexplanation:

**********
Categories
**********

Generally speaking, the categories serve either to define a criticality level for a host or a service, or to group together technically a set of objects (services linked to the execution of a request on a MariaDB DBMS, etc.).
Good practice requires that we group hosts or services together into categories to facilitate the filtration of these objects in ACL.
The categories are also used to define types of objects in the Centreon MAP module or to classify the objects within sub-groups in the Centreon BI module.
