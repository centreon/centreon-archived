********************************
Initialization of the monitoring
********************************

To start the monitoring engine:

1. From your web interface, go to **Configuration > Pollers**.
2. Keep the default options and click on **Export configuration**.
3. Select **Central** poller from the box input **Pollers**.
4. Uncheck **Generate Configuration Files** and **Run monitoring engine debug (-v)**.
5. Check **Move Export Files** and **Restart Monitoring Engine** with the **Restart** option selected.
6. Click on **Export** again.
7. Log on to the *root* user on your server.
8. Start Centreon Broker: ::

     # systemctl start cbd

9. Start Centreon Engine: ::

     # systemctl start centengine

10. Start centcore: :: 

    # systemctl start centcore

11. Start centreontrapd: ::

    # systemctl start centreontrapd

Monitoring is now working. You can begin monitoring your IT system.

To automatically start services at system bootup, run these commands
on the central server: ::

    # systemctl enable centcore
    # systemctl enable centreontrapd
    # systemctl enable cbd
    # systemctl enable centengine
    # systemctl enable centreon

************************************
Installation of available extensions
************************************

Go to **Administration > Extensions > Manager** menu and click on
**Install all**:

.. image:: /_static/images/installation/install_imp_2.png
   :align: center

***********
Quick start
***********

Go to the :ref:`quick start<quickstart>` chapter to configure your first
monitoring.
