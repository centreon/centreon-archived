==============
Centreon 2.6.1
==============


******
Notice
******
If you are upgrading from a version prior to 2.6.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.


*********
CHANGELOG
*********

Bug fixes
=========

- #5655: Changing Host Templates doesn't delete services 
- #5925: Popup Dialogs (Acknowledge, Downtimes etc.) not working with Internet Explorer
- #6224: Special characters in LDAP are replaced by underscore
- #6358: It's possible to bypass ACLs on Event Logs page
- #6375: servicegroups empty into servicegroups.cfg but ok in DB
- #6377: PHP logs are too much verbose with PHP 5.4
- #6378: PHP logs are too much verbose with PHP 5.3
- #6383: Random severity on services
- #6390: Escalations with contact groups containing space
- #6391: Some traps are skipped
- #6396: Warning and critical threshold display in centreon graph
- #6399: Wrong condition in centreonLDAP.class.php
- #6410: Do not limit to 20 the number of trap rules or macro in host and services config pages

Features
========

- #6035: Removing Centreon Broker local module
- #6366: New option for Centreon Engine log
- #6392: Block choice of Nagios and NDO in installation processus
