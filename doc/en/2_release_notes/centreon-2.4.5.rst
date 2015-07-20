==============
Centreon 2.4.5
==============

***************
Important notes
***************

Connector
=========

You can now linked a command to a connector from the connector form in `Configuration` > `Commands` > `Connectors`.


Centreon Broker
===================

Centreon 2.4.x branch is now compatible with Centreon Broker 2.5.x branch.
Also several options have been added in Centreon Broker configuration form accessible in `Configuration` > `Centreon` > `Configuration` (Below Centreon-Broker label in the left panel).
Here the new options:

    * "Write timestamp" in `General` tab: To enable or disable timestamp logging in each log line (disable this option is useful with when Centreon-Broker is used with Nagios)
    * "Write thread id" in `General` tab: To enable or disable thread id logging in each log line
    * "Write metrics" in `Output` tab with `RRD - RRD file generator`: To enable or disable the update of the performance graph
    * "Write status" in `Output` tab with `RRD - RRD file generator`: To enable or disable the update of the status graph
    * "Store performance data in data_bin" in `Output` tab with `Storage - Perfdata Generator (Centreon Storage)`: To enable or disable insertion of performance data in data_bin table
    * "Insert in index data" in `Output` tab with `Storage - Perfdata Generator (Centreon Storage)`: Allow Centreon-Broker to create entries in index_data table (use with caution)


