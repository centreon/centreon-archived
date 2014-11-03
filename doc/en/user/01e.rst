=========
Reporting
=========

*********
Dashboard
*********

Description
===========

The availability reports of monitoring objects from Centreon web interface allows 
to disaply the availability rate about hosts, hostgroup or servicegroup on a selected period.

Visualisation
=============

To access to availability reports:

#. Go into the menu: **Reporting** ==> **Dashboard**
#. In the left menu, click on **Host**
#. Select defined host in **Host** list

.. image:: /images/user/ehostdashboard.png
   :align: center

* The **Reporting Period** allows to select a predefined period or to define it manually using **From** to **to** fields.
* The **Host state** table displays the availability rates of object.
* The **State Breakdowns For Host Services** table displays the availability of linked objects.
* The timeline allows you to see intuitively the status of the object in short time.

.. image:: /images/user/ehistoricalstatus.png
   :align: center

Moreover, clicking on a day in the timeline, you get the report of the day:

.. image:: /images/user/edayavailability.png
   :align: center

It is also possible to view web reports:

* The groups of hosts: Click on **Host Groups** in the left menu

.. image:: /images/user/ehostgroupdashboard.png
   :align: center

* The groups of services: Click on **Service Groups** in the left menu

.. image:: /images/user/eservicegroupdashboard.png
   :align: center

The |export| allows to export data into CSV file.

.. note::
    It is also possible to access to availability of a service by clicking on the service name in the host or servicegroup report.
	
.. image:: /images/user/eservicedashboard.png
   :align: center

.. |export|    image:: /images/export.png
