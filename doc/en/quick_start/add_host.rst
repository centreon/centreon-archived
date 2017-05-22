.. _add_host:

==========
Add a host
==========

Your platform is now ready to monitor your first servers or network equipment but you don’t know how to. Don't worry! It is simple to start monitoring.

First :ref:`connect<centreon_login>` to your Centreon web interface with an administrator account or an account which allow to manage monitored object.

Go to the **Configuration > Hosts > Hosts** menu and click on **Add** button:

.. image:: /_static/images/quick_start/add_host_menu.png
    :align: center

You access to a form to define your equipment to monitor but don't worry all fields are not necessary!

To start to monitor your equipment set:

* The name of object in **Host Name** entry field
* Describe your object in **Alias** entry field
* Set the IP address of DNS in **IP Address / DNS** entry field
* Click on **+ Add a new entry** button and select **generic-host**
* Click on **Yes** button for **Create Services linked to the Template too** field

.. image:: /_static/images/quick_start/add_host_form.png
    :align: center

Save the modification by clicking on **Save** button.

.. image:: /_static/images/quick_start/add_host_list.png
    :align: center

The host is now defined in Centreon web interface but the monitoring engine doesn't monitor it!

You have now to :ref:`generate the configuration, export it and send it to the monitoring engine<deployconfiguration>`.

You can see result in **Monitoring > Status Details > Hosts** menu:

.. image:: /_static/images/quick_start/add_host_monitoring.png
    :align: center
