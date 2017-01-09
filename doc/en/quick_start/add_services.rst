=============
Add a service
=============

You already :ref:`added a host<add_host>` and you want to monitor some indicators.

.. note::
    An indicator is named **service** in Centreon.

Go to the **Configuration  >  Services  >  Services by host** menu and click on **Add** button:

.. image:: /_static/images/quick_start/add_service_menu.png
    :align: center

To add a service to a host you have to define only three fields:

* Select the host in **Linked with Hosts** field
* Define the name of the service in **Description** entry field
* Select a predefined template of service, for example Base-Ping-LAN, in **Service Template** field

.. note::
    After selecting a template of service new field appear. This values describe arguments use to monitor your service.
    Most often it is the alert thresholds. You can use the default values or overwrite those.

.. image:: /_static/images/quick_start/add_service_form.png
    :align: center

Save the modification by clicking on **Save** button.

.. image:: /_static/images/quick_start/add_service_list.png
    :align: center

The service is now defined in Centreon web interface but the monitoring engine doesn't monitor it!

You have now to :ref:`generate the configuration, export it and send it to the monitoring engine<deployconfiguration>`.

You can see result in **Monitoring > Status Details > Services** menu:

.. image:: /_static/images/quick_start/add_service_monitoring.png
    :align: center
