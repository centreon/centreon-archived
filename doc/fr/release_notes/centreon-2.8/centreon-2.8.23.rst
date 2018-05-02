###################
Centreon Web 2.8.23
###################

Enhancements
============

* [API] Add default poller - PR #6098
* [API] Link host with default poller if unknown poller - PR #6099
* [ACL] Improve performance - #6056 PR #6107
* [Documentation] Improve Centreon CLAPI usage - PR #6090 #6091
* [Documentation] Improve documentation to add a new poller - #6075 PR  #6086
* [Documentation] Add notice for 64 bits support only - PR #6101
* [Monitoring] Display links in output and comments  - #5943 PR #6113

Bug Fixes
=========

* [ACL] Allow nested groups filter in ldap configuration - #6127 PR #6128
* [API] Export specific service, add host before service in CLAPI - PR #6100
* [API] CLAPI add resource export filter - PR #6125
* [API] CLAPI Export contact with contact group - PR #6131
* [API] CLAPI Export service categories - PR #6134
* [Configuration] SNMP trap poller generation uses ACL - #6043 PR #6069
* [Custom Views] Fix share custom view - PR #6109
* [Poller Stats] Poller Statistics Graphs are displayed in first column only - #6003 PR #6122

Others
======

* Update copyright date on the login page - PR #6076
* Remove multiple debug in Centreon - PR #6138
