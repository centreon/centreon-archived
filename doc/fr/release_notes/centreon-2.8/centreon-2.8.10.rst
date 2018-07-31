###################
Centreon Web 2.8.10
###################

Enhancements
============

* Proposal break Ajax #5256
* Do not export empty Centreon Broker parameters with API #5284
* Remove duplicate $_GET["autologin"] in test #5344
* Documentation improvement #5063
* Update engine reserved macros ($HOSTID$, $SERVICEID$, $HOSTTIMEZONE$) #5246
* Config generation is too long #5388
* Rename Centreon Broker Daemon option #5276

Bugfix
======

* Failure with special character in password for mysqldump #5173
* Unable to select all services in escalation form #5326 #PR5325
* Contacts/contactgroups inheritance #5396 PR #5400
* Check if wiki is configured and extend error message #5278 PR #5269
* Select All don't work on service categories PR #5389
* Autologin + fullscreen options #5338 PR #5338
* Directory "/var/spool/centreon" not created by Centreon-common.rpm #5405
* "Fill in" option in graph doesn't work with "VDEF" DEF type #5354
* Delete SNMP Traps #5282
* Can't duplicate trap definition #5272 PR #5280
* Virtual Metric problems with French language package #5355
* Impossible to set manually a service to a meta service for non admin users #5358 PR #5391
* Graph period displayed does not match selected zoom period #5334
* Host configuration can not be saved or modified #5348
