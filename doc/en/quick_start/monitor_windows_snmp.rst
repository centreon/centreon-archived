.. _monitor_windows:

##################################
Monitor a Windows server with SNMP
##################################

Go to the **Configuration > Plugin Packs** menu and install **Windows SNMP**
Plugin Pack:

.. image:: /images/quick_start/quick_start_windows_0.gif
    :align: center

Go to the **Configuration > Hosts > Hosts** menu and click on **Add**:

.. image:: /images/quick_start/quick_start_windows_1.png
    :align: center

Fill in the following information:

* The name of the server
* A description of the server
* The IP address
* The SNMP version and community

Click on **+ Add a new entry** button in **Templates** field, then select the
**OS-Windows-SNMP-custom** template in the list.

Click on **Save**.

Your equipment has been added to the monitoring configuration:

.. image:: /images/quick_start/quick_start_windows_2.png
    :align: center

Go to **Configuration > Services > Services by host** menu. A set of indicators
has been automatically deployed:

.. image:: /images/quick_start/quick_start_windows_3.png
    :align: center

Other indicators can be monitored. Click on **Add** button to add a new service
as file system usage for example:

.. image:: /images/quick_start/quick_start_windows_4a.png
    :align: center

In the **Description** field, enter the name of the service to create, then
select the host to link this service. In the **Template** filed, select the
**OS-Windows-Disk-Generic-Name-SNMP-custom** template.

A list of macros corresponding to the model will then appear:

.. image:: /images/quick_start/quick_start_windows_4b.png
    :align: center

Enter the name of the file system for the **DISKNAME** macro value and add 
**--regexp** for the value of **EXTRAOPTIONS** macro, then and click on **Save**
to add this indicator into the monitoring configuration.

Do the same to network bandwidth usage monitoring:

.. image:: /images/quick_start/quick_start_windows_5.png
    :align: center

It is now time to deploy the supervision through the 
:ref:`dedicated menu<deployconfiguration>`.

Then go to the **Monitoring > Status Details > Services** menu and select **All**
value for the **Service Status** filter. After a few minutes, the first results
of the monitoring appear:

.. image:: /images/quick_start/quick_start_windows_6.png
    :align: center

*************
To go further
*************

The **Windows SNMP** Plugin Pack provides several monitoring templates. When
creating a service, it is possible to search the available models in the
selection list: 

.. image:: /images/quick_start/quick_start_windows_7.png
    :align: center

It is also possible to access the **Configuration > Services > Templates**
menu to know the complete list:

.. image:: /images/quick_start/quick_start_windows_8.png
    :align: center

To know the name of the available files system you can execute the plugin in
command line: ::

    $ /usr/lib/centreon/plugins/centreon_windows_snmp.pl --plugin=os::windows::snmp::plugin --hostname=10.24.11.66 --snmp-version='2c' --snmp-community='public' --mode=list-storages
    List storage:
    'C:\ Label:  Serial Number 2cb607df' [size = 53317988352B] [id = 1]
    Skipping storage 'Virtual Memory': no type or no matching filter type
    Skipping storage 'Physical Memory': no type or no matching filter type

It is the same to know the name of the available network interfaces: ::

    $ /usr/lib/centreon/plugins/centreon_windows_snmp.pl --plugin=os::windows::snmp::plugin --hostname=10.24.11.66 --snmp-version='2c' --snmp-community='public' --mode=list-interfaces
    List interfaces:
    'loopback_0' [speed = 1073, status = up, id = 1]
    'ethernet_3' [speed = , status = notPresent, id = 10]
    'ppp_1' [speed = , status = notPresent, id = 11]
    'ethernet_10' [speed = 1000, status = up, id = 12]
    'tunnel_4' [speed = 0.1, status = down, id = 13]
    'ethernet_4' [speed = , status = up, id = 14]
    'ethernet_5' [speed = , status = up, id = 15]
    'ethernet_6' [speed = , status = up, id = 16]
    'ethernet_7' [speed = , status = up, id = 17]
    'ethernet_8' [speed = , status = up, id = 18]
    'ethernet_9' [speed = , status = up, id = 19]
    'tunnel_0' [speed = , status = down, id = 2]
    'ethernet_11' [speed = 1000, status = up, id = 20]
    'ethernet_12' [speed = 1000, status = up, id = 21]
    'ethernet_13' [speed = 1000, status = up, id = 22]
    'tunnel_1' [speed = , status = down, id = 3]
    'tunnel_2' [speed = , status = down, id = 4]
    'tunnel_3' [speed = , status = down, id = 5]
    'ppp_0' [speed = , status = down, id = 6]
    'ethernet_0' [speed = , status = up, id = 7]
    'ethernet_1' [speed = , status = up, id = 8]
    'ethernet_2' [speed = , status = up, id = 9]
