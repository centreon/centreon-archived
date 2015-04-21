==============
Centreon 2.6.0
==============


******
Notice
******
If you are upgrading from a version prior to 2.5.4, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

***********
What's new?
***********

Compatibility with PHP 5.4.x
============================

Centreon is now compatible with PHP in version 5.4.x. So, you do not need to downgrade to PHP 5.3.x version when you install it on Debian 6.

Centreon proprietary module (Centreon BAM, Centreon BI, Centreon MAP, Centreon KB) are for now not compatible with this PHP version.


New options for Centreontrapd
=============================

It's now possible with centreontrapd to :

- Filter services on same host ;
- Transform output (to remove pipe for example) ;
- Skip trap for hosts in downtime ;
- Add custom code execution ;
- Put unknown trap in another file. 


ACL and configuration modification with admin users
===================================================

ACL management has been improved to allow for a greater number of simultaneous sysadmin users to work on the same monitoring platform.

The synchronisation is more efficient in configuration page between admin and normal users.


Partial rebuild of events information
=====================================

It's now possible to partially rebuild events information with eventsRebuild script. You can now use option '-s' when rebuild and the the rebuild will start from this date.

Before, you had to rebuild from the begiging.


Integration of Centreon new logo
================================

The centreon new logo has been integrated to this new version.


*********
CHANGELOG
*********

Bug fixes
=========
- #5655: Changing Host Templates deosn't delete services 
- #5782: Warning daemon_dumps_core variable ignored
- #5795: ACL and configuration modification with admin users
- #5868: Generation of services groups isn't correct for poller
- #6052: recurring downtime form month_cycle option is not properly set
- #6119: Filter doesn't work on many page in Administration -> Log
- #6163: A template should not be able to inherit from itself
- #6336: Problem with schedule downtime when using different timezone

Features
========

- #3239: PHP-5.4 Compatibility
- #5334, #6114, #6120 : Optimisation and customization on centreontrapd
- #5952: Add possibility to rebuild partially Events informations
- #6160: New Centreon logo
