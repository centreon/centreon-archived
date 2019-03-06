****************************************
Configuring a new Centreon Remote Server
****************************************

As of Centreon version 18.10, a wizard is now available for defining a new Centreon Remote Server.

.. note::
    You can configure a new poller :ref:`manually<add_manual_poller>`,
    however Centreon recommends using the following procedure.

Go to the **Configuration > Pollers** menu and click **Add server with wizard** to
configure a new poller.

Select **Add a Centreon Remote Server** and click on **Next**:

.. image:: /images/poller/wizard_add_remote_1.png
    :align: center

If you define a new server, select the **Manual input** option and fill in the form:

.. image:: /images/poller/wizard_add_remote_2a.png
    :align: center

If you enabled the **Remote Server** option when installing your server,
select the option **Select a Remote Server**, then select your server and fill in
the form:

.. image:: /images/poller/wizard_add_remote_2b.png
    :align: center

.. note::
    The **Database username** and **Database password** are the credentials defined
    during the installation of the Remote Server.

Click on **Next**.

Select the poller(s) to link to this Remote Server, then click on **Apply**:

.. image:: /images/poller/wizard_add_remote_3.png
    :align: center

The wizard will configure your new server:

.. image:: /images/poller/wizard_add_remote_4.png
    :align: center

The Remote Server is now configured:

.. image:: /images/poller/wizard_add_remote_5.png
    :align: center

Go to the :ref:`Quick Start<quickstart>` chapter to configure your first monitoring.
chapter to configure your first monitoring.
