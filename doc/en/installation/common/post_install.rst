To start the monitoring engine :

1. On your web interface, go to **Configuration** ==> **Pollers**.
2. Keep the default options and click on **Export configuration**.
3. Select **Central** poller from the box input **Pollers**.
4. Uncheck **Generate Configuration Files** and **Run monitoring engine debug (-v)**.
5. Check **Move Export Files** and **Restart Monitoring Engine** with option **Restart** selected.
6. Click on **Export** again.
7. Log in to the ‘root’ user on your server.
8. Start Centreon Broker ::

     # systemctl start cbd

9. Start Centreon Engine ::

     # systemctl start centengine

10. Start centcore :: 

    # systemctl start centcore

11. Start centreontrapd ::

    # systemctl start centreontrapd

Monitoring is now working. You can begin monitoring your IT system!

To make services automatically start during system bootup run these commands
on the central server: ::

    # systemctl enable centcore
    # systemctl enable centreontrapd
    # systemctl enable cbd
    # systemctl enable centengine

The Centreon web interface contains several menus, each with a specific function:

.. image :: /images/user/amenu.png
   :align: center

* **Home** lets you access the first home screen after logging in. It provides a summary of overall monitoring status.
* **Monitoring** provides a combined view of the status of all monitored items in real and delayed time using logs and performance graphics.
* **Reporting** provides an intuitive view (using diagrams) of the evolution of monitoring over a given period.
* **Configuration** allows you to configure all monitored items and the monitoring infrastructure.
* **Administration** allows you to configure the Centreon web interface and view the overall status of the servers.

***************************************
Quick and easy monitoring configuration
***************************************

Centreon is a highly versatile monitoring solution that can be configured to
meet the specific needs of your IT infrastructure. To quickly configure Centreon and help you get started, you
may want to use Centreon IMP. This tool provides you with Plugin Packs, which are bundled configuration
templates that will dramatically reduce the time needed to implement the Centreon platform for monitoring
the services in your network.

Centreon IMP requires the Centreon License Manager and Centreon Plugin Pack Manager in order to function.

If you haven't installed any modules during the installation process, go to the
**Administration > Extensions > Modules** menu.

Click on **Install/Upgrade all** and validate.

.. image:: /_static/images/installation/install_imp_1.png
   :align: center

Once the installation is complete, click on **Back**.
The modules are now installed.

.. image:: /_static/images/installation/install_imp_2.png
   :align: center

Now proceed to Configuration -> Plugin packs -> Manager.
10 free Plugin Packs are provided to get you started. Five additional Packs are
available once you register and over 150 more if you subscribe to the IMP
offer (for more information: `our website <https://www.centreon.com>`_).

.. image:: /_static/images/installation/install_imp_3.png
   :align: center

You can continue to configure your monitoring system with Centreon IMP by
following the instructions in :ref:`this guide <impconfiguration>`.
