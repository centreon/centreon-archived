###################
Centreon Web 2.8.26
###################

Enhancements
============

* [Authentication] Set LDAP version 3 as default in LDAP configuration form - PR #6452
* [Notification] Standardize mail notifications PR #6570 (ex PR #6530)

Bug Fixes
=========

* [ACL] Do not get severity of parents if present on actual object - PR #6484
* [ACL] ACLs calculation is too slow with lot of acl resources - #6461, PR #6495
* [Chart] Fix metrics error message - PR #6474
* [Configuration] Trap generation reindexing pollers id - #6205 PR #6416
* [Configuration] Fix disable option in Centreon Engine configuration - #6518, PR 6520
* [Monitoring] In Status Details pages, display true contacts/contactgroups inheritance relation - #6177, #6176, #6467, PR #6513
* [Monitoring] Add topology url option when loading default page # 6528, PR #6551
* [Monitoring] Sort hosts by name ASC in serviceGridByHGXML - #6529, PR #6547

Documentation
=============

* Fix doc architecture - PR #6430
* Fix images for db replication - PR #6432
* Correction of typography - #6447, PR #6453
* Improve Centreon IMP chapter - PR #6485
* Correct link references in IMP chapter - PR #6541
* Increase Centreon web version number for PDF generation - PR #6540
* Correct build errors - PR #6567
* Global review documentation content - #6560, PR #6510

Others
======

* Remove dead code from escalation page - PR #6393
* Remove old and unused file in order to avoid problems with ACL - PR #6210

Notice
======

The Standardize mail notifications enhancement is only available for new instalaltion (PR #6570)
