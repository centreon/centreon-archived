==============
Centreon 2.5.4
==============

Notice
------

If you are upgrading from a version prior to 2.5.3, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Bug fixes
---------

- #5458: Display problem with host groups
- #5924: Generation of service configuration files does not work when "service_inherit_contacts_from_host" is not enabled
- #5926: Centreon-Broker-2.7.x compatibility
- #5929: Fix problem in import service groups by cfg file
- #5942: Fix compatibility with IE
- #5946: Problem in reporting due to acknowledgment
- #5986: Session's Id does not change after logout

Features
--------

- #5433: Argument column larger in service configuration
- #5944: Services inherit criticality from hosts

==============
Centreon 2.5.3
==============

Warning
-------

This version include a couple of security fixes. Please proceed to the update of your platform if your centreon is not in version 2.5.3 at least.
If you're using Debian or Suse before doing the update, you need to install php5-sqlite package.

The update can take some times due to the update to UTF-8 format (#5609)

Notice
------

If you are upgrading from a version prior to 2.5.2, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

CHANGELOG
---------

- #5895: Security Issues : CVE-2014-3828 & CVE-2014-3829
- #5888: Differences between update and fresh install for "Insert in index data" field
- #5829: Add config file in parameters for all crons of Centreon in order to install centreon on different directories
- #5852: Fix problem with massive change for "Inherit contacts from host" in service form
- #5841: Empty dependencies are now remove automatically
- #5840: Fix problem with host duplication when this host has a "'" in the alias
- #5790 & #5813 & #5750: Fix problems on Tactical Overview
- #5786: Fix problem when generating correlation config file.
- #5756: Fix problem with centstorage => Table log is growing to much
- #5609: Push Centreon Broker table to UTF-8
- #5589: Fix problem with Contact inheritance between service and its template who doesn't work
- #4865: Fix problem with search in Eventlog

==============
Centreon 2.5.2
==============

Notice
------

If you are upgrading from a version prior to 2.5.1, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

CHANGELOG
---------

- #5593: Fixes a bug where trap advanced matching rules were not working
- #5600: Fixes a bug where it was impossible to add or modify a poller
- #5533: Fixes a bug where it was impossible to update the severity level of a service
- #5307: Tooltips messages were not translated in the Broker configuration form
- #5664: Enhances loading time of the service detail page
- #5439: Enhances loading time of the meta service page

==============
Centreon 2.5.1
==============

WARNING
-------

If you are upgrading from Centreon 2.5.0 make sure to read the following. 

.. WARNING::
    If you are upgrading from a version prior to 2.5.0, just skip this notice and follow this procedure instead:
    `https://blog.centreon.com/centreon-2-5-0-release/ <https://blog.centreon.com/centreon-2-5-0-release/>`_.

As usual, database backups are to be made before going any further.

It does not matter whether you run the commands below before or after the web upgrade; do note that those scripts may take some execution time depending on
the size of your log tables.

You are using NDOUtils
######################

If you are using NDOUtils, chances are that you have plenty of duplicate entries in your log table. Follow the procedure in order to re insert the logs:

Copy all the log files from the remote pollers to the local poller in /var/lib/centreon/log/POLLERID/. To know the POLLERID of each of your pollers, 
execute the following request against the MySQL server (centreon database)::
  
  mysql> SELECT id, name FROM nagios_server;

Then, execute the following script::

  /path/to/centreon/cron/logAnalyser -a


You are upgrading from Centreon 2.5.0
#####################################

There was a bug in Centreon 2.5.0 that probably messed up your reporting data, you will have to recover by running these commands::

  /path/to/centreon/cron/eventReportBuilder -r

  /path/to/centreon/cron/dashboardBuilder -r -s <start_date> -e <end_date>

``start_date`` and ``end_date`` must be formatted like this ``yyyy-mm-dd``; they refer to the time period you wish to rebuild your dashboard on.

============
Centreon 2.5
============

WARNING
-------

If you are upgrading from Centreon 2.4.x make sure to read the following. As usual, database backups
are to be made before going any further. Then, follow these procedures in order to ensure the integrity
of the RRD graphs. Not following this may cause your graphs to malfunction!

If you are using Centreon Broker
################################

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
####################

* Stop centstorage
* Upgrade Centreon (web install)
* Execute /path/to/centreon/bin/changeRrdDsName.pl
* Start centstorage


What's new?
-----------

ACL on configuration objects
############################

ACL rules are now applied to configuration objects. For more information regarding this feature, be sure to checkout our blog post: `<http://blog.centreon.com/configuration-acl-with-centreon-2-5-2/>`_

UI and sound notifications
##########################

It is now possible to get UI and sound notifications on Centreon, you can set your preferences in your profile page. A quick overview there: `<http://blog.centreon.com/centreon-ui-notification-system/>`_

Only available if you use Centreon Broker.

New system with SNMP traps
##########################

Centreon has evolved with an easiest way to handle SNMP traps. Some advantages of the new system:

* No more ‘snmptt’
* More advanced configuration in SQL Database
* Local database (SQLite) on Pollers

You have to look on the centreon documentation in order to configure Centreon using this new system. Go in section: User guide > Advanced > SNMP TRAPS 

Important notes
---------------

Centcore is now mandatory
#########################

External commands are now sent to centcore regardless of whether the poller is local or not. So be sure to have it running all the time from now on.
