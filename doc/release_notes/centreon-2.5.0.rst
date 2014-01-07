============
Centreon 2.5
============

*******
WARNING
*******

If you are upgrading from Centreon 2.4.x make sure to read the following. As usual, database backups
are to be made before going any further. Then, follow these procedures in order to ensure the integrity
of the RRD graphs. Not following this may cause your graphs to malfunction!

If you are using Centreon Broker
================================

* Stop all the centreon-engine services
* Stop the centreon-broker daemon
* Upgrade Centreon-Broker on all the pollers
* Restart all the engines
* Upgrade Centreon (web install)
* Execute /path/to/centreon/www/install/tools/migration/changeRrdDsName.pl
* Check that your graphs are showing properly on the web interface
* Start the centreon-broker daemon


If you are using NDO
====================

* Stop centstorage
* Upgrade Centreon (web install)
* Execute /path/to/centreon/www/install/tools/migration/changeRrdDsName.pl
* Start centstorage


**********
What's new
**********

ACL on configuration objects
============================

todo


UI and sound notifications
==========================

todo


New system with SNMP traps
==========================

todo


***************
Important notes
***************

Centcore is now mandatory
=========================

todo
