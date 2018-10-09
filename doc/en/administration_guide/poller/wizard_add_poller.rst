.. _wizard_add_poller:

================================
Configure new server in Centreon
================================

Since Centreon 18.10, a new wizard is available to define a new server (poller
or Remote Server) to a Centreon platform

Chose the correct chapter regarding the type of server.

.. note::
    It is possible to configure a new Poller :ref:`manually<add_manual_poller>`,
    however Centreon recommends using the following procedure.

------------------
Configure a Poller
------------------

Go to the **Configuration > Pollers** menu an click **Add server with wizard**
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
    If you want to change the sens of the flow between the Centreon Server (or
    the Remote Server and the Poller, check the **Advanced: reverse Centreon
    Broker communication flow** chexkbox.

Wait few seconds, the wizard will configure your new server.

The Poller is now configured:

.. image:: /images/poller/wizard_add_poller_5.png
    :align: center

Go to the :ref:`Simplified configuration of Centreon with IMP<impconfiguration>`
chapter to configure your first monitoring.

-------------------------
Configure a Remote Server
-------------------------

Go to the **Configuration > Pollers** menu an click **Add server with wizard** to
configure a new poller.

Select **Add a Centreon Remote Server** and click **Next**:

.. image:: /images/poller/wizard_add_remote_1.png
    :align: center

If you define a new Server, select  **Manual input** option and fill the form:

.. image:: /images/poller/wizard_add_remote_2a.png
    :align: center

If you enabled the **Remote Server** option during the installaion of your server,
select the option **Select a Remote Server**, then select your server and fill
the form:

.. image:: /images/poller/wizard_add_remote_2b.png
    :align: center

.. note::
    The **Database user** and **Database password** are the credentials defined
    during the instalaltion of the Remote Server

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
