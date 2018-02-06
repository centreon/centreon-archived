.. _centreon_broker_wizards:

========================================
Centreon Broker configuration via wizard
========================================

You can create configurations of Centreon Broker via the wizard, to do this:
#. Go to the menu **Configuration ==> Pollers ==> Centreon-Broker ==> Configuration**
#. Click on Add with wizard


Two choices are available:

.. image:: /images/user/configuration/10poller/centreon_broker_wizard.png
   :align: center

*******************************
Configuration of central server
*******************************

.. image:: /images/user/configuration/10poller/centreon_broker_wizard_02_schema.png
   :align: center
   :alt: Distributed monitoring schema

.. note::
   Distributed monitoring schema

#. Choose **Central**
#. Enter a name for the configuration

.. image:: /images/user/configuration/10poller/centreon_broker_wizard_02_step02.png
   :align: center

********************************************************
Configuration of a poller for a distributed architecture 
********************************************************

.. warning::
   For this configuration you must have previously installed a poller.

.. image:: /images/user/configuration/10poller/centreon_broker_wizard_03_schema.png
   :align: center
   :alt: Schema distributed monitoring

#. Choose **Simple poller**
#. Enter a name for the configuration
#. Select  a poller
#. Enter the IP address or the **FQDN** DNS name of the central server

.. image:: /images/user/configuration/10poller/centreon_broker_wizard_03_step02.png
   :align: center
