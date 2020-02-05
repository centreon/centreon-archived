.. _add_manual_poller:

=============================
Manually configuring a poller
=============================

Configuring the server
======================

Once the installation is complete, you must integrate this remote poller into the Centreon configuration.

#. Go into the menu: **Configuration > Pollers**.
#. Duplicate the central server and edit it.
#. Change the following settings, and save:

*	Change the name of **Poller Name**.
*	Enter the IP address of the poller in the **IP Address** field.
*	Enable the poller by clicking on **Enabled** in the **Status** field.

.. image:: /images/user/configuration/10advanced_configuration/07addpoller.png
   :align: center

.. note::
    If your poller is attached to a Remote Server, please choose this one in the **Attach to Remote Server** list.

.. note::
    If your poller is attached to a Remote Server, but you prefer to send externals commands and configuration
    directly from the Central server to this poller, please disable the **Use the Remote Server as a proxy for SSH**
    option.

#. Go to the **Configuration > Pollers > Engine configuration** menu.
#. Select your last added configuration.
#. Change the following settings, and save:

* In the **Files** tab:

  * Modify **Configuration Name**.
  * Check that **Linked poller** is the previously created poller.
  * Change if necessary **Timezone / Location**.

.. image:: /images/user/configuration/10advanced_configuration/07addengine.png
   :align: center

* In the **Data** tab - **Multiple Broker Module** fields check / add the following entries::

   /usr/lib64/centreon-engine/externalcmd.so

   /usr/lib64/nagios/cbmod.so /etc/centreon-broker/poller-module.json

.. image:: /images/user/configuration/10advanced_configuration/07addpoller_neb.png
   :align: center

Centreon Broker configuration
=============================

You must generate a configuration file for Centreon Broker:

#. Go into the menu: **Configuration > Pollers > Broker configuration**.
#. click on **Add**.

* In the **General** tab:

  * Select the **Requester**.
  * Set **Name** of the configuration.
  * Set **Config file name** that must be exactly the same as the one defined in Centreon Engine configuration (for example, **poller-module.json**).
  * Check the value **No** for the **Link to cbd service** option.

.. image:: /images/user/configuration/10advanced_configuration/07_Addbroker.png
   :align: center

* In the **Output** tab:

  * Add a new **TCP - IPv4** output.
  * Set the **Name**.
  * Set the distant TCP port, by default to **5669**.
  * Set the IP address of the Central server (**Host to connect to**).

.. image:: /images/user/configuration/10advanced_configuration/07_Addbroker_output.png
   :align: center

* Save the configuration.
