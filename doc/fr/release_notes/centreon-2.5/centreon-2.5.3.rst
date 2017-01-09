==============
Centreon 2.5.3
==============


*******
Warning
*******

This version include a couple of security fixes. Please proceed to the update of your platform if your centreon is not in version 2.5.3 at least.
If you're using Debian or Suse before doing the update, you need to install php5-sqlite package.

The update can take some times due to the update to UTF-8 format (#5609)

******
Notice
******
If you are upgrading from a version prior to 2.5.2, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

*********
CHANGELOG
*********

- #5895: Security Issues : CVE-2014-3828 & CVE-2014-3829
- #5888: Differences between update and fresh install for "Insert in index data" field
- #5829: Add config file in parameters for all crons of Centreon in order to install centreon on different directories
- #5852: Fix problem with massive change for "Inherit contacts from host" in service form
- #5841: Empty dependences are now remove automaticaly
- #5840: Fix problem with host duplication when this host has a "'" in the alias 
- #5790 & #5813 & #5750: Fix problems on Tactical Overview
- #5786: Fix problem when generating correlation config file.
- #5756: Fix problem with centstorage => Table log is growing to much
- #5609: Push Centreon Broker table to UTF-8
- #5589: Fix problem with Contact inheritance between service and its template who doesn't work
- #4865: Fix problem with search in Eventlog
