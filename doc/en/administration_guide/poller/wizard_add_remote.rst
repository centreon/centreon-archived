***************************************
Configure new Remote Server in Centreon
***************************************

Since Centreon 18.10, a new wizard is available to define a new Remote Server
to a Centreon platform

.. note::
    It is possible to configure a new Poller :ref:`manually<add_manual_poller>`,
    however Centreon recommends using the following procedure.

Go to the **Configuration > Pollers** menu and click **Add server with wizard** to
configure a new poller.

Select **Add a Centreon Remote Server** and click **Next**:

.. image:: /images/poller/wizard_add_remote_1.png
    :align: center

If you define a new Server, select  **Manual input** option and fill the form:

.. image:: /images/poller/wizard_add_remote_2a.png
    :align: center

If you enabled the **Remote Server** option during the installation of your server,
select the option **Select a Remote Server**, then select your server and fill
the form:

.. image:: /images/poller/wizard_add_remote_2b.png
    :align: center

.. note::
    The **Database user** and **Database password** are the credentials defined
    during the installation of the Remote Server

Click on **Next**

Select the poller(s) to link to this Remote Server , then click on **Apply**:

.. image:: /images/poller/wizard_add_remote_3.png
    :align: center

The wizard will configure your new server:

.. image:: /images/poller/wizard_add_remote_4.png
    :align: center

The Remote Server is now configured:

.. image:: /images/poller/wizard_add_remote_5.png
    :align: center

Go to the :ref:`Simplified configuration of Centreon with IMP<impconfiguration>`
chapter to configure your first monitoring.
