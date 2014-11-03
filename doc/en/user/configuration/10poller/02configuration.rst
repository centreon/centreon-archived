Configuration of the scheduler
==============================

Once the installation is completed, it is necessary to integrate this remote server into the Centreon configuration.

#. Go into the menu: **Configuration** ==> **Centreon**
#. Duplicate the configuration file of the central server and edit it
#. Change the following settings, and save:

*	Change the name of the **Poller Name**.
*	Enter the IP address of the collector in the **IP Address** field.
*	Enable the collector by clicking on **Enabled** in the **Status** field.

.. image:: /images/user/configuration/10advanced_configuration/07addpoller.png
   :align: center

Now, it is necessary to generate a configuration file for Centreon Engine:

1. Go into the menu: **Configuration** ==> **Monitoring Engines**
2. In the left menu, click on **main.cfg**
3. Duplicate the configuration file of the **Central** poller and change it
4. Change the following settings, and save:

*	Change the name of the **Configuration Name**.
*	Enable the configuration file of the scheduler by clicking on **Enabled** in the field **Status**.
*	Choose the new poller in the **Linked poller** field.

.. image:: /images/user/configuration/10advanced_configuration/07mainconffile.png
   :align: center 

*	In the **Data** tab - **Multiple broker module** field change the name of the of Centreon Broker configuration file **central-module.xml** to for example: poller1-module.xml.

.. image:: /images/user/configuration/10advanced_configuration/07mainconffilebrokerconf.png
   :align: center 

Centreon Broker configuration
=============================

It is necessary to generate a configuration file for Centreon Broker:

#.  Go into the menu: **Configuration** ==> **Centreon**
#.  In the left menu, click on **Configuration** (below Centreon Broker)
#.  Duplicate configuration files of the module of your central server and edit it
#.  Change the following settings, and save:

* Change the configuration **name**.
* Change the **Config file name** which should correspond to the name of the xml file defined in the configuration file of the poller.
* Enable the configuration file by clicking on **Enabled** in the **Status** field.
* Change the **Requester** field by selecting your new poller.

.. image:: /images/user/configuration/10advanced_configuration/07brokerconf.png
   :align: center 

* In the **Output** tab, change the **Host to connect to** field by entering the IP address of the server containing your MySQL base (in our case the central server).

.. image:: /images/user/configuration/10advanced_configuration/07brokerconfoutput.png
   :align: center 

Centreontrapd Configuration
===========================

It is necessary to change the configuration files of Centreontrapd so that the service can question the SQLite database (see the chapter: :ref:`configuration_advanced_snmptrapds`).

Plugins synchronisation
=======================

You can synchronise the plugins between your central server and your remote servers using **rsync** software.

.. warning::
   Don’t perform this action if your plugins depend on third party libraries that need to have been installed previously.

Exchanging SSH keys
===================

For the central server to be able to export the configuration files of the monitoring engine, it is necessary to make a SSH key exchange between the central server and the new remote server.

On the remote server:

#. Log in as a ‘root’
#. Change the Centreon user password::

	# passwd centreon

On the central server:

1. Log in as ‘Centreon’::

    # su - centreon

2. If you have not already generated a public / private key pair, enter the following command (leave the default options)::

    $ ssh-keygen

3. Then export your SSH key to the remote server::

    $ ssh-copy-id -i /var/spool/centreon/.ssh/id_rsa.pub centreon@[POLLER_IP]

4. Check that you can log in from the central server to the remote server as a Centreon user. You can you use the command::

    $ ssh centreon@[POLLER_IP]

Export the configuration
========================

It only remains to export the configuration to verify that the installation of the remote server has been executed correctly.

.. note::
   Refer to the documentation: :ref:`Export configuration<deployconfiguration>`
