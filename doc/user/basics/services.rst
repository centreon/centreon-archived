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
