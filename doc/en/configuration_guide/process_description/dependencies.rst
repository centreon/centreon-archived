=============================
Managing logical dependencies
=============================

We have seen in the :ref:`dependencies <dependancy>` configuration chapter how to configure dependencies between objects (hosts, services, host groups, etc.).
This sub-chapter illustrates the use of these dependencies via a few actual cases.

.. note::
   The dependencies are based on failure criteria that is “do not do if”. Do not notify if the service is in a Critical state. Do not perform the check if the service is in a Critical, Alert, Unknown, ... state.

*********************
Services dependencies
*********************

A service is checked using a Selenium scenario.
This scenario connects to a web interface with an identifier and a password. This connection information is stored in a MySQL database.

Consequently, if the database server does not reply, the Selenium scenario cannot complete.
It seems obvious that it is necessary to create a logical dependency link between the service which uses the Selenium scenario and the service that is responsible for checking the status of the MySQL server.

Moreover, considering that the Selenium scenario cannot perform properly, no performance data can be stored in the database. So it is necessary not only to stop the notification for the service using the Selenium scenario but also the check.

To create this dependency:

#. Go into the menu: **Configuration > Notifications**
#. In the left menu under **Dependencies**, click on **Services**
#. Click on **Add**
#. Enter the name and the description of the dependency
#. For the **Execution Failure Criteria** and **Notification Failure Criteria** fields, check Warning, Critical, Unknown and Pending
#. In the **Services** list select the service that is responsible for checking the status of the MySQL server
#. In the **Dependent Services** list, select the service that uses the Selenium scenario
#. Save

From now on, if the service responsible for checking the status of the MySQL server has “Warning”, “Critical”, “Unknown” or “Pending” status, the service responsible for executing the Selenium scenario will not be executed until the master recovers its OK status.

******************
Hosts dependencies
******************

Let us take the case of two hosts which operate as a cluster. Three hosts are created to be able to monitor this cluster: a host A, a host B (both members of the cluster) and a host C (which centralizes the information from the cluster).

If host A or host B has a Not-OK status the services of host C will automatically be considered to be Not-OK. So it is necessary to add a dependency which prevents the sending of notifications if host A or host B become faulty. However, performance data feed-back must always be operational; this is why it is necessary to continue the monitoring of host C.

To create this dependency:

#. Go into the menu: **Configuration > Notifications**
#. In the left menu under **Dependencies**, click on **Hosts**
#. Click on **Add**
#. Enter the name and the description of the dependency
#. For the **Notification Failure Criteria** field, check Warning, Critical, Unknown and Pending
#. In the **Host Names** list, select host A
#. In the **Dependent Host Names** list select host C
#. Save

Repeat this operation for host B.

***************************
Service Groups dependencies
***************************

Let us take the example of a set of Oracle services on which the ERP application bases itself. Two service groups are needed:

* The Oracle Application group
* The ERP Application group

If the Oracle services become critical, the services of the ERP application are automatically critical.
It is necessary to create a dependency link to prevent the check and notification of the services of the application ERP if the Oracle application is Not-OK.

To create this dependency:

#. Go into the menu: **Configuration > Notifications**
#. In the left menu under **Dependencies**, click on **Service Groups**
#. Click on **Add**
#. Enter the name and the description of the dependency
#. For the **Execution Failure Criteria** and **Notification Failure Criteria** fields, check Critical and Pending
#. In the **Service Group Names** list select the service group Oracle Application
#. In the **Dependent Service Group Names** list, select the service group ERP Application
#. Save
