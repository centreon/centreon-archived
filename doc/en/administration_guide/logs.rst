=============================
Logging configuration changes
=============================

*********
Principle
*********

By default, Centreon retains all user actions concerning changes to configuration in a log.
To access this data, go into the menu: **Administration > Logs**.

.. image:: /images/guide_exploitation/fsearchlogs.png
   :align: center

The grey search bar can be used to filter the information presented via filters:

* **Object** used to filter on object name (host, service, contact, SNMP trap definition, group, etc.)
* **User** used to filter by change author
* **Object Type** used to filter by object type

********
Practice
********

E.g.: To see all the actions effective by the user: **admin**, enter “admin” in the **User** field and click on **Search**.

The table below defines the columns in the results table:

+----------------------+------------------------------------------------------------------------------------------------------------+
|   Column Name        |  Description                                                                                               |
+======================+============================================================================================================+
| Time                 | Indicates the date of the event                                                                            |
+----------------------+------------------------------------------------------------------------------------------------------------+
| Modification type    | Contains the type of action effective. There are several types of action possible:                         |
|                      |                                                                                                            |
|                      | - Added: Indicates that the object has been added                                                          |
|                      | - Changed: Indicates that the object has been changed                                                      |
|                      | - Deleted: Indicates that the object has been deleted                                                      |
|                      | - Massive Change: Indicates a massive change of configuration on objects.                                  |
|                      | - Enabled: Indicates that the object has been enabled                                                      |
|                      | - Disabled: Indicates that the object has been disabled                                                    |
+----------------------+------------------------------------------------------------------------------------------------------------+
| Type                 | Indicates object type                                                                                      |
+----------------------+------------------------------------------------------------------------------------------------------------+
| Object               | Indicates object name                                                                                      |
+----------------------+------------------------------------------------------------------------------------------------------------+
| Author               | Indicates the user having effective this change                                                            |
+----------------------+------------------------------------------------------------------------------------------------------------+

By clicking on the name of an object, you can view the history of the changes effective on it.

.. image:: /images/guide_exploitation/fobjectmodif.png
   :align: center

The table below defines the columns of the changes table:

+----------------------+-----------------------------------------------------------+
|   Column Name        |  Description                                              |
+======================+===========================================================+
| Date                 | Date of the change                                        |
+----------------------+-----------------------------------------------------------+
| Contact Name         | Name of the person having effective the change            |
+----------------------+-----------------------------------------------------------+
| Type                 | Modification type                                         |
+----------------------+-----------------------------------------------------------+
|                      | The last column describes the change itself :             |
|                      |                                                           |
|                      | - Field name: Describes the field that has been changed   |
|                      | - Before: Indicates the previous value                    |
|                      | - After: Indicates the new value                          |
+----------------------+-----------------------------------------------------------+

*************
Configuration
*************

To enable user audit logs, go to **Administration > Parameters > Options** and
check the **Enable/Disable audit logs** option:

.. image:: /images/guide_exploitation/logs_audit_enable.png
    :align: center
