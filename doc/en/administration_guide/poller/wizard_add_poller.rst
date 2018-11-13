********************************
Configure new poller in Centreon
********************************

Since Centreon 18.10, a new wizard is available to define a new poller to a
Centreon platform.

.. note::
    It is possible to configure a new Poller :ref:`manually<add_manual_poller>`,
    however Centreon recommends using the following procedure.

Go to the **Configuration > Pollers** menu and click **Add server with wizard**
to configure a new poller.

Select **Add a Centreon Poller** and click **Next**:

.. image:: /images/poller/wizard_add_poller_1.png
    :align: center

Set the name, the IP address of the poller and the IP address of the Centreon
Central server and click **Next**:

.. image:: /images/poller/wizard_add_poller_2.png
    :align: center

.. note::
    The IP address of the poller is the IP address or the FQDN to access to this
    poller since Centreon Central server.
    
    The IP address of the Centreon Central server is the IP address or the FQDN
    to access to the Centreon Central server since the poller.

If you want to link this poller to the Centreon Server, click **Apply**:

.. image:: /images/poller/wizard_add_poller_3.png
    :align: center

Else, if you want to link this poller to an existing Remote Server, select the
Remote Server in the list. Then click **Apply**:

.. note::
    If you want to change the sense of the flow between the Centreon Server (or
    the Remote Server and the Poller, check the **Advanced: reverse Centreon
    Broker communication flow** checkbox.

Wait a few seconds, the wizard will configure your new server.

The Poller is now configured:

.. image:: /images/poller/wizard_add_poller_5.png
    :align: center

Go to the :ref:`Simplified configuration of Centreon with IMP<impconfiguration>`
chapter to configure your first monitoring.
