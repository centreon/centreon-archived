###################
Centreon Web 2.8.28
###################

Bug Fixes
=========

* [Monitoring] Remove double encoding of host name
* [Monitoring] Fix hide password in command line - PR/#7079
* [Charts] missing french translations in the graph page - PR/#7429

Documentation
=============

* Improve extension page

Security
========

* Fix SQL injection for GET parameter - PR/#7229
* Type juggling can lead to authentication bypass in (very) rare cases - PR/#7084
* Fix vulnerability for file loading - PR/#7226
* SQL injections in the service by hostgroups and servicegroups pages - PR/#7275
