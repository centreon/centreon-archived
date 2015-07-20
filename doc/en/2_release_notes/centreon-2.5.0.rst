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

* Check right of conf.pm file. Apache must have the right to modify conf.pm file
* Stop all the centreon-engine services
* Stop the centreon-broker daemon
* Upgrade Centreon-Broker on all the pollers
* Restart all the engines
* Upgrade Centreon (web install)
* Execute /path/to/centreon/bin/changeRrdDsName.pl
* Check that your graphs are showing properly on the web interface
* Start the centreon-broker daemon


If you are using NDO
====================

* Stop centstorage
* Upgrade Centreon (web install)
* Execute /path/to/centreon/bin/changeRrdDsName.pl
* Start centstorage


***********
What's new?
***********

ACL on configuration objects
============================

ACL rules are now applied to configuration objects. For more information regarding this feature, be sure to checkout our blog post: `<http://blog.centreon.com/configuration-acl-with-centreon-2-5-2/>`_


UI and sound notifications
==========================

It is now possible to get UI and sound notifications on Centreon, you can set your preferences in your profile page. A quick overview there: `<http://blog.centreon.com/centreon-ui-notification-system/>`_

Only available if you use Centreon Broker.


New system with SNMP traps
==========================

Centreon has evolved with an easiest way to handle SNMP traps. Some advantages of the new system:

* No more ‘snmptt’
* More advanced configuration in SQL Database
* Local database (sqlite) on Pollers

You have to look on the centreon documentation in order to configure Centreon using this new system. Go in section: User guide > Advanced > SNMP TRAPS 


***************
Important notes
***************

Centcore is now mandatory
=========================

External commands are now sent to centcore regardless of whether the poller is local or not. So be sure to have it running all the time from now on.
