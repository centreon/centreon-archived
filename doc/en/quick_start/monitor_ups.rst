.. _monitor_ups:

###############################
Monitor UPS equipment with SNMP
###############################

Go to the **Configuration > Plugin Packs** menu and install **UPS Standard**
Plugin Pack:

.. image:: /images/quick_start/quick_start_ups_0.gif
    :align: center

Go to the **Configuration > Hosts > Hosts** menu and click on **Add**:

.. image:: /images/quick_start/quick_start_ups_1.png
    :align: center

Fill in the following information:

* The name of the server
* A description of the server
* The IP address
* The SNMP version and community

Click on **+ Add a new entry** button in **Templates** field, then select the
**HW-UPS-Standard-Rfc1628-SNMP-custom** template in the list.

Click on **Save**.

Your equipment has been added to the monitoring configuration:

.. image:: /images/quick_start/quick_start_ups_2.png
    :align: center

Go to **Configuration > Services > Services by host** menu. A set of indicators
has been automatically deployed:

.. image:: /images/quick_start/quick_start_ups_3.png
    :align: center

It is now time to deploy the supervision through the 
:ref:`dedicated menu<deployconfiguration>`.

Then go to the **Monitoring > Status Details > Services** menu and select **All**
value for the **Service Status** filter. After a few minutes, the first results
of the monitoring appear:

.. image:: /images/quick_start/quick_start_ups_4.png
    :align: center
