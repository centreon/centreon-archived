**********
Connectors
**********

Connectors are used to improve the global performance of commands execution.

Adding a connector
==================

.. image:: /_static/images/user/add_connector_1.png
   :align: center

.. image:: /_static/images/user/connector_form.png
   :align: center

========================  ==============================================================================
 Field name                Description
========================  ==============================================================================
 Connector name            Name which will be used for identifying the connector

 Connector description     A short description of the connector

 Command line              This will be executed by Centreon Engine, note that this line 
                           contains macros that will be replaced before execution. 
                           e.g: ``$USER3$/centreon_connector_perl``

 Connector status          Whether or not the connector is enabled.

========================  ==============================================================================

Using a connector
=================

Defined connectors are not used by default. Each command can be
configured to use a connector, see the :ref:`commands` chapter for
more information.
