===============================
Deploy services from a template
===============================

In a previous quick start you :ref:`added a new host from template<add_host_template>` 
using the **OS-Linux-SNMP** template. This template of host deployed the following services:

* CPU
* Load
* Memory
* Swap

But some indicators aren't yet monitored because they depend of the server itself,
for example name of files system, name of network interfaces, etc.

First :ref:`connect<centreon_login>` to your Centreon web interface with an 
administrator account or an account which allow to manage monitored object.

Go to the **Configuration > Services > Services by host** menu and click on **Add** button:

.. image:: /_static/images/quick_start/add_service_menu.png
    :align: center

To add a service to a host you have to define only three fields:

* Select the host in **Linked with Hosts** field
* Define the name of the service in **Description** entry field, for example **Traffic-eth0** to monitor the traffic bandwidth usage of interface eth0
* Select a predefined template of service, for example **OS-Linux-Traffic-Generic-Name-SNMP**, in **Service Template** field

.. note::
    After selecting a template of service new field appear. This values describe arguments use to monitor your service.
    Most often it is the alert thresholds. You can use the default values or overwrite those.

Modify the value of macro **INTERFACENAME** to enter the name of network interface to monitor, for example **eth0**

.. image:: /_static/images/quick_start/add_svc_template_form.png
    :align: center

Save the modification by clicking on **Save** button.

.. image:: /_static/images/quick_start/add_svc_template_list.png
    :align: center

The service is now defined in Centreon web interface but the monitoring engine doesn't monitor it!

You have now to :ref:`generate the configuration, export it and send it to the monitoring engine<deployconfiguration>`.

You can see result in **Monitoring > Status Details > Services** menu:

.. image:: /_static/images/quick_start/add_svc_template_monitoring.png
    :align: center
