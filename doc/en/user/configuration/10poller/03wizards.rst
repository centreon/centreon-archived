.. _centreon_broker_wizards:

========================================
Centreon Broker configuration via wizard
========================================

You can create configurations of Centreon Broker via the wizard:

.. image:: /images/user/configuration/10poller/centreon_broker_add_wizard.png
   :align: center

Three choices are available:

.. image:: /images/user/configuration/10poller/centreon_broker_wizard.png
   :align: center

***********************************************
Configuration with an standalone central server
***********************************************

.. image:: /images/user/configuration/10poller/centreon_broker_wizard_01_schema.png
   :align: center
   :alt: Central only schema

.. note::
    Standalone Centreon server

.. image:: /images/user/configuration/10poller/centreon_broker_wizard_01_step01.png
   :align: center

#. Enter a name for the configuration

.. image:: /images/user/configuration/10poller/centreon_broker_wizard_02_step02.png
   :align: center

**************************************************************
Configuration of central server for a distributed architecture
**************************************************************

.. image:: /images/user/configuration/10poller/centreon_broker_wizard_02_schema.png
   :align: center
   :alt: Distributed monitoring schema

.. note::
   Distributed monitoring schema

.. image:: /images/user/configuration/10poller/centreon_broker_wizard_02_step01.png
   :align: center

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

.. note::
   :alt: Schema distributed monitoring

.. image:: /images/user/configuration/10poller/centreon_broker_wizard_03_step01.png
   :align: center

#. Enter a name for the configuration
#. Select  a poller
#. Enter the IP address or the **FQDN** DNS name of the central server

.. image:: /images/user/configuration/10poller/centreon_broker_wizard_03_step02.png
   :align: center
