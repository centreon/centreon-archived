************************************
Configuring a new poller in Centreon
************************************

As of Centreon version 18.10, a new wizard has been added for defining a new poller on the
Centreon platform.

.. note::
    You also have the option of adding a new poller manually :ref:`manually<add_manual_poller>`,
    but we recommend using the following procedure.

Go to the **Configuration > Pollers** menu and click on **Add server with wizard**
to configure a new poller.

Select **Add a Centreon Poller** and click on **Next**:

.. image:: /images/poller/wizard_add_poller_1.png
    :align: center

Type in the name, the IP address of the poller and IP address of the
Central Server. Click on **Next**:

.. image:: /images/poller/wizard_add_poller_2.png
    :align: center

.. note::
    The IP address of the poller is the IP address or the FQDN used to access this
    poller from the Central Server.
    
    The IP address of the Central Server is the IP address or the FQDN
    used to access the Central Server from the poller.

If you want to link the poller to the Centreon Server, click on **Apply**:

.. image:: /images/poller/wizard_add_poller_3.png
    :align: center

Otherwise, if you want to link the poller to an existing Centreon Remote Server, select one from the list. Then click **Apply**:

.. note::
    If you want to change the direction of the flow between the Centreon Central Server and the poller, check the **Advanced: reverse Centreon
    Broker communication flow** checkbox.

In a few seconds the wizard will configure your new server.


.. image:: /images/poller/wizard_add_poller_5.png
    :align: center

Go to the :ref:`Quick Start<quickstart>` chapter to configure your first monitoring.
