.. _add_host_template:

=============================
Deploy a host from a template
=============================

In a previous quick start you :ref:`added a new host<add_host>` using the **generic-host** template.
This template provides a predefined minimum configuration to define a host.

But the templates of host in Centreon web offer more than just a pre definition of values.
In Centreon web you can :ref:`link templates of service to template of host<hosttemplates>`.
With this process you can deploy easily a new host and their service in one time.

In this example we will use a template of host provided by **Centreon plugin packs** to monitor a Linux server.
This template of host allows to deploy the following services:

* CPU
* Load
* Memory
* Swap

You need to install these plugins, using the :ref:`**Centreon plugin packs**<basic_plugins>`.

First :ref:`connect<centreon_login>` to your Centreon web interface with an administrator account or an account which allow to manage monitored object.

Go to the **Configuration > Hosts > Hosts** menu and click on **Add** button:

.. image:: /_static/images/quick_start/add_host_menu.png
    :align: center

You access to a form to define your equipment to monitor. To start to monitor your equipment set:

* The name of object in **Host Name** entry field
* Describe your object in **Alias** entry field
* Set the IP address of DNS in **IP Address / DNS** entry field
* Click on **+ Add a new entry** button and select **OS-Linux-SNMP**
* Click on **Yes** button for **Create Services linked to the Template too** field

.. image:: /_static/images/quick_start/add_template_form.png
    :align: center

Save the modification by clicking on **Save** button.

.. image:: /_static/images/quick_start/add_template_list.png
    :align: center

The host is now defined in Centreon web interface but the monitoring engine doesn't monitor it!

You have now to :ref:`generate the configuration, export it and send it to the monitoring engine<deployconfiguration>`.

You can see result in **Monitoring > Status Details > Services** menu:

.. image:: /_static/images/quick_start/add_template_monitoring.png
    :align: center
