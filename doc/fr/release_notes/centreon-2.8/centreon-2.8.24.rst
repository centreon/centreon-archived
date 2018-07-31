###################
Centreon Web 2.8.24
###################

Bug Fixes
=========

* Remove duplicate entries in centreon_acl table - PR #6366

Security
========

* Fix execution command by rrdtool command line - PR #6263
* Fix XSS on command form - PR #6260
* Fix XSS security on menu username - PR #6259
* Fix SQL injection on graphs - PR #6251
* Fix SQL Injection in administration logs - PR #6255
* Fix SQL injection in dashboard - PR #6250
* Fix SQL injection in Curve template - PR #6256
* Fix SQL Injection in Virtual Metrics - PR #6257
