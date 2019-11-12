==============
Centreon 2.6.6
==============

Released October 29, 2015

Notice
------

If you are upgrading from a version prior to 2.6.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Bug fixes
---------

- #3812: [2.6.3] Strange display of service group details page
- #3824: PHP Warning: array_map(): Argument #2 should be an array
- #3840: [2.6.4] Wrong reporting graph data with default user language fr_FR.UTF-8
- #3846: [2.6.5] CRSF Token critical: Impossible to upgrade a plugin
- #3847: [2.6.5] split component switch
- #3852: [2.6.5] CSRF error appears in user massive change form
- #3854: Cannot add new macro after deleting all macros already created
- #3855: Cannot add new host template to host after deleting all templates
- #3861: Comments shows only "A"
- #3864: [2.6.5] CSRF when trying to upload a SNMP MiB

==============
Centreon 2.6.5
==============

Released October 21, 2015

Notice
------

If you are upgrading from a version prior to 2.6.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Security fixes
--------------

- #3831: XSS injection in object lists (ZSL-2015-5266)
- #3835: CSRF Issues on Centreon (ZSL-2015-5263)

Bug fixes
---------

- #3821: Upgrade from 2.6.1 to 2.6.3 kill Centreon Frontend
- #3826: Split Component and zoom doesn't work
- #3827: Service Group Details page isn't displayed for non admin in Centreon 2.6.3
- #3837: Relation of passive service with SNMP traps problem with multihost link
- #3842: Full logs display on event logs page for a non admin user

==============
Centreon 2.6.4
==============


Notice
------

If you are upgrading from a version prior to 2.6.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Bug fixes
---------

- #3793: Problem when creating an empty hostgroup with non admin user
- #3795: Update Centreon Administration About page (forge -> GitHub)
- #3796: Problem when connect two time with same user in API
- #3797: Password in macro
- #3800: Current State Duration isn't displayed
- #3803: ACL : Manage multiple Resources group on the same ACL user group
- #3807: Unable to enable status option on main.cfg

==============
Centreon 2.6.3
==============


Notice
------

If you are upgrading from a version prior to 2.6.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Bug fixes
---------

- #564: Filter field does not work in service groups monitoring screen
- #1000: Services of service groups are dispatched on many pages
- #3782: SQL Keyswords
- #3783: index_data switch in option form
- #3788: Problem with static keywords

==============
Centreon 2.6.2
==============

Notice
------

If you are upgrading from a version prior to 2.6.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Features
--------

- Modules can extend actions after restart/reload pollers

Security fixes
--------------

- #2979 : Secure the type of media which file can be uploaded (ZSL-2015-5264)
- Fix some SQL injections (ZSL-2015-5265)

Bug fixes
---------

- #3559 : Fix query with MariaDB / MySQL configure in STRICT_TRANS_TABLES
- #3554 : Can send acknowledgment with multiline from monitoring page
- #3397 : Fix display graph with unicode characters in metric name
- #2362 : Correct value when use index_data inserted by Centreon Broker in configuration
- #1195 : Display correct number of pollers in status bar
- #196 : Display all columns when filter is applied on Monitoring services unhandled view

==============
Centreon 2.6.1
==============

Notice
------

If you are upgrading from a version prior to 2.6.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Bug fixes
---------

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
--------

- #6035: Removing Centreon Broker local module
- #6366: New option for Centreon Engine log
- #6392: Block choice of Nagios and NDO in installation process

==============
Centreon 2.6.0
==============

Notice
------

If you are upgrading from a version prior to 2.5.4, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

What's new?
-----------

Compatibility with PHP 5.4.x
############################

Centreon is now compatible with PHP in version 5.4.x. So, you do not need to downgrade to PHP 5.3.x version when you install it on Debian 6, Ubuntu 13.04, RedHat 7 and CentOS 7.

Centreon proprietary module (Centreon BAM, Centreon BI, Centreon MAP, Centreon KB) is not compatible as yet with this PHP version.

New options for Centreontrapd
#############################

It's now possible with Centreontrapd to :

- Filter services on same host ;
- Transform output (to remove pipe for example) ;
- Skip trap for hosts in downtime ;
- Add custom code execution ;
- Put unknown trap in another file. 

ACL and configuration modification with admin users
###################################################

ACL management has been improved to allow for a greater number of simultaneous sysadmin users to work on the same monitoring platform.

The synchronization is more efficient in configuration page between admin and normal users.

Partial rebuild of events information
#####################################

It's now possible to partially rebuild events information with eventsRebuild script. You can now use option '-s' when rebuilding and the rebuild will start from this date.

Before, you had to rebuild from the beginning of the related data. 

Criticality inheritance
#######################

Centreon 2.6 introduces a capability for the dependent services of a host to automatically inherit its configured criticality.  It’s also possible to define the levels of global critically of a particular host and dependent services cluster thanks to the use of templates.

Integration of Centreon new logo
################################

The new Centreon logo has been integrated into this new version.

Bug fixes
---------

- #5655: Changing Host Templates doesn't delete services 
- #5782: Warning daemon_dumps_core variable ignored
- #5795: ACL and configuration modification with admin users
- #5868: Generation of services groups isn't correct for poller
- #6052: Month_cycle option in recurring downtime is not properly set
- #6119: Filter doesn't work on many pages in Administration -> Log
- #6163: A template should not be able to inherit from itself
- #6336: Problem with schedule downtime when using different timezones

Features
--------

- #3239: PHP-5.4 Compatibility
- #5238: Criticality inheritance
- #5334, #6114, #6120 : Optimization and customization on Centreontrapd
- #5952: Add possibility to rebuild partially Events information
- #6160: New Centreon logo
