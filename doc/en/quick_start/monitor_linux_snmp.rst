.. _monitor_linux:

################################
Monitor a Linux server with SNMP
################################

Go to the **Configuration > Plugin Packs** menu and install **Linux SNMP** Plugin
Pack:

.. image:: /images/quick_start/quick_start_linux_0.gif
    :align: center

Go to the **Configuration > Hosts > Hosts** menu and click on **Add**:

.. image:: /images/quick_start/quick_start_linux_1.png
    :align: center

Fill in the following information:

* The name of the server
* A description of the server
* The IP address
* The SNMP version and community

Click on **+ Add a new entry** button in **Templates** field, then select the
**OS-Linux-SNMP-custom** template in the list.

Click on **Save**.

Your equipment has been added to the monitoring configuration:

.. image:: /images/quick_start/quick_start_linux_2.png
    :align: center

Go to **Configuration > Services > Services by host** menu. A set of indicators
has been automatically deployed:

.. image:: /images/quick_start/quick_start_linux_3.png
    :align: center

Other indicators can be monitored. Click on **Add** button to add a new service
as bandwidth usage for example:

.. image:: /images/quick_start/quick_start_linux_4a.png
    :align: center

In the **Description** field, enter the name of the service to create, then
select the host to link this service. In the **Template** filed, select the
**OS-Linux-Traffic-Generic-Name-SNMP-custom** template.

A list of macros corresponding to the model will then appear:

.. image:: /images/quick_start/quick_start_linux_4b.png
    :align: center

Enter the name of the network interface for the **INTERFACENAME** macro value
and click on **Save** to add this indicator into the monitoring configuration.

Do the same to add packet error monitoring:

.. image:: /images/quick_start/quick_start_linux_5.png
    :align: center

Or for file system:

.. image:: /images/quick_start/quick_start_linux_6.png
    :align: center

It is now time to deploy the supervision through the 
:ref:`dedicated menu<deployconfiguration>`.

Then go to the **Monitoring > Status Details > Services** menu and select **All**
value for the **Service Status** filter. After a few minutes, the first results
of the monitoring appear:

.. image:: /images/quick_start/quick_start_linux_7.png
    :align: center

*************
To go further
*************

The **Linux SNMP** Plugin Pack provides several monitoring templates. When
creating a service, it is possible to search the available models in the
selection list: 

.. image:: /images/quick_start/quick_start_linux_8.png
    :align: center

It is also possible to access the **Configuration > Services > Templates**
menu to know the complete list:

.. image:: /images/quick_start/quick_start_linux_9.png
    :align: center

To know the name of the available files system you can execute the plugin in
command line: ::

    $  /usr/lib/centreon/plugins/centreon_linux_snmp.pl --plugin=os::linux::snmp::plugin --hostname=10.40.1.169 --snmp-community=public --snmp-version=2c --mode=list-storages
    List storage:
    Skipping storage 'Physical memory': no type or no matching filter type
    Skipping storage 'Swap space': no type or no matching filter type
    Skipping storage 'Virtual memory': no type or no matching filter type
    '/' [size = 21003583488B] [id = 31]
    '/dev/shm' [size = 1986875392B] [id = 36]
    '/run' [size = 1986875392B] [id = 38]
    '/sys/fs/cgroup' [size = 1986875392B] [id = 39]
    '/boot' [size = 1015308288B] [id = 57]
    '/var/cache/centreon/backup' [size = 5150212096B] [id = 58]
    '/var/lib/centreon-broker' [size = 5150212096B] [id = 59]
    Skipping storage 'Memory buffers': no type or no matching filter type
    '/var/lib/centreon' [size = 7264002048B] [id = 60]
    '/var/log' [size = 10434662400B] [id = 61]
    '/var/lib/mysql' [size = 16776032256B] [id = 62]
    '/run/user/0' [size = 397377536B] [id = 63]
    Skipping storage 'Cached memory': no type or no matching filter type
    Skipping storage 'Shared memory': no type or no matching filter type

It is the same to know the name of the available network interfaces: ::

    $  /usr/lib/centreon/plugins/centreon_linux_snmp.pl --plugin=os::linux::snmp::plugin --hostname=10.40.1.169 --snmp-community=public --snmp-version=2c --mode=list-interfaces
    List interfaces:
    'lo' [speed = 10, status = up, id = 1]
    'enp0s3' [speed = 1000, status = up, id = 2]
