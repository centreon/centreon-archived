.. _monitor_mysql:

###################################
Monitor a MySQL or MariaDB database
###################################

Go to the **Configuration > Plugin Packs** menu and install **MySQL/MariaDB**
Plugin Pack:

.. image:: /images/quick_start/quick_start_mysql_0.gif
    :align: center

Go to the **Configuration > Hosts > Hosts** menu and click on **Add**:

.. image:: /images/quick_start/quick_start_mysql_1a.png
    :align: center

Fill in the following information:

* The name of the server
* A description of the server
* The IP address

Click on **+ Add a new entry button** in **Templates** field, then select the
**App-DB-MySQL-custom** template in the list.

A list of macros corresponding to the model will then appear:

.. image:: /images/quick_start/quick_start_mysql_1b.png
    :align: center

Fill in the value of following macros:

* **MYSQLUSERNAME**: the username to connect to the database.
* **MYSQLPASSWORD**: the password of the user.
* **MYSQLPORT**: the TCP port to connect to the database, by default 3306.

Click on **Save**.

Your equipment has been added to the monitoring configuration:

.. image:: /images/quick_start/quick_start_mysql_2.png
    :align: center

Go to **Configuration > Services > Services by host** menu. A set of indicators
has been automatically deployed:

.. image:: /images/quick_start/quick_start_mysql_3.png
    :align: center

It is now time to deploy the supervision through the 
:ref:`dedicated menu<deployconfiguration>`.

Then go to the **Monitoring > Status Details > Services** menu and select **All**
value for the **Service Status** filter. After a few minutes, the first results
of the monitoring appear:

.. image:: /images/quick_start/quick_start_mysql_4.png
    :align: center

*************
To go further
*************

The **MySQL/MariaDB** Plugin Pack provides several monitoring templates. When
creating a service, it is possible to search the available models in the
selection list: 

.. image:: /images/quick_start/quick_start_mysql_5.png
    :align: center

It is also possible to access the **Configuration > Services > Templates**
menu to know the complete list:

.. image:: /images/quick_start/quick_start_mysql_6.png
    :align: center
