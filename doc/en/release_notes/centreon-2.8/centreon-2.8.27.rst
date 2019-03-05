###################
Centreon Web 2.8.27
###################

Enhancements
============

* [ACL] Improve ACL access on downtime and hostgroup form - PR #6962
* [API] API for commands arguments descriptions - PR #7196
* [API] Add showinstance CLAPI command to Host #7199
* [LDAP] manage multiple ldap group with same dn - PR #6714

Bug Fixes
=========

* [ACL] Host calculation with ACL is not correct - PR #6436
* [API] Broker configuration accept accept id 0
* [API] Unset service/contact relations if set option - PR #7115
* [API] Use "Reach API *" to validate access to API - PR #7117
* [Authentication] add sync with ldap groups upon login - PR #7057
* [Backup] Fix scp export of configuration files backup - PR #7112
* [Chart] fix graph export when a curve is only displayed in legend - PR #7009
* [Centcore] Allow to set illegal characters for centcore (#7206)
* [Configuration] fix export of cbd watchdog logs path - #6794, PR #6919
* [Configuration] fix broken hostgroup form and massive change on host - PR #7105
* [Downtimes] Pagination & filters corrections in recurrent Downtimes form - #6501, #6504, #6506, PR #6509
* [Global] fix pagination when new header is enabled - PR #6687
* [LDAP] fix ldap import due to var typo
* [LDAP] Fix LDAP search when the 'user group attribute' field of ldap configuration is empty - PR #7057
* [Monitoring] Fix columns on the list page - PR #6984
* [UI] Fix a Javascript bug when the new header is selected - PR #6590
* [UI] backport memory leak - PR #7003
* [Visual notification] exclude services started by BA from BAM UI notification style - PR #6782

Documentation
=============

* Correct menu access to add/edit recurrent downtime - #6698
* Correct the upgrape chapter - #6916
* Improve prerequisite MySQL version to correct bug on partitioned tables - PR #6974
* Quick Start improvements 

Security
========

* Add SQL and XSS protection of Administration Logs page - PR #7038
* Avoid password macro to appear in cleartext - PR #7020
* Clean dead code about escalation - PR #7200
* Fix XSS vulnerability on hosts and services comments - PR #6953
* Fix SQL injection and duplicate action on the host list page - PR #6961
* Fix the XSS vulnerability on poller resource - PR #6982
* Fix XSS vulnerability in the ACL group search field - PR #7032
* Fix SQL injection for virtual metrics - PR #7061
* Fix SQL injection and duplicate feature - PR #7069
* Fix XSS vulnerability in media - PR 7089
* Protect hostname resolver from XSS - PR #7043
* Rce vulnerability fixed when using command's testing feature (#7245)

Others
======

* Change copyright calculation code and replace mailto link by a direct link to our website
* Fix compatibility with PHP 5.3
