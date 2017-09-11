###################
Centreon Web 2.8.13
###################

Enhancements
============

* [Doc] Improve centreon documentation #5611 PR #5612
* [Doc] clarify documentation of centreon clapi authentication #5625 PR #5628
* [Performance] Correct svc top counter with meta and merge SQL requests PR #5616

Bugfix
======

* [Top Counter] Metaservices not counted properly in statuses filter #5458 PR #5616
* [Configuration] Properly export interval length in storage endpoints #5461
* [Documentation] Time Range exceptions invalid format #5578
* [Chart] No graphics with backslash #5554 #5342 PR #5565
* [LDAP] Problem with LDAP autoimport and groupmapping with comma in CN #4867
* [Monitoring] No inheritance in query of notified contacts (Monitoring view) #4981

