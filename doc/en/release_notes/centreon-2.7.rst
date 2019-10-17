===============
Centreon 2.7.12
===============

The 2.7.12 release for Centreon Web is now available for `download <https://download.centreon.com>`_.
The full release notes for 2.7.12 follow.

Notice
------

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Bug Fixes
---------

* [CLAPI] Several bugs on HG / CG when export is filtered #5297 PR #5320
* [CLAPI] fix clapi ldap contact import
* Unable to load public custom view - No Layout... #5449
* Impossible to acknowledge several object from custom views #5420
* Security: avoid external command shell injection in comment

===============
Centreon 2.7.11
===============

The 2.7.11 release for Centreon Web is now available for `download <https://download.centreon.com>`_.
The full release notes for 2.7.11 follow.

Notice
------

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Bug Fixes
---------

- Fix ldap authentication #5216
- Fix CLAPI export using filters #5084
- Fix CLAPI poller generate (generate, test, move, restart/reload/ applycfg) #5224 #5221
- Fix Incorrect style for "Scheduled downtime" in dashboard #5240
- Fix Contact - import LDAP apply new CSS style #5235
- Fix HTML export with filters  #4868
- Fix brokercfg export with filter
- Fix get command list query #5229
- Apply sso fixes from 2.8.x
- Improve performances #5157
- Convert string in UTF-8 #5118 #5244

===============
Centreon 2.7.10
===============

The 2.7.10 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.10 follow.

Notice
------

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Bug Fixes
---------

- Fix various security issues
- Fix ldap configuration form
- Fix downtime popup in listing pages
- Fix object listing pages which are empty after some actions

==============
Centreon 2.7.9
==============

Released March, 21th 2017.

The 2.7.9 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.9 follow.

Notice
------

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Features and Bug Fixes
----------------------

- Fix: allow full configuration export for Centreon Poller Display
- All graphs linked to a host aren't displayed in performance page - #4731
- Documentation - correct example to use TP instead of TIMEPERIOD - PR #4915, Pr #4916
- Force CENGINE key in centreon database options to use Centreon Engine - #4922

==============
Centreon 2.7.8
==============

Released November 09,2016  

The 2.7.8 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.8 follow.

Notice
------

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Features and Bug Fixes
----------------------

- Fix: Improve graph rest API
- Fix: Two "update mode" lines for service groups in Massive change causing annoying behavior

==============
Centreon 2.7.7
==============

Released September 13,2016  

The 2.7.7 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.7 follow.

Notice
------

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Features and Bug Fixes
----------------------

- Fix: Non initialized value in Centreon ACL page
- Fix : Security issue with autologin when user has no password
- Enh: [Centreon Clapi] Add export filters

==============
Centreon 2.7.6
==============

Released July 21,2016  

The 2.7.6 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.6 follow.

Notice
------

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Features and Bug Fixes
----------------------

- Fix: Hard PATHs in some folders
- Fix: Correction of some typos
- Fix: contact_location default value incorrect
- Fix: Security fix linked to the configuration export
- Fix: Problem with custom view style when user was not able to edit the view then old style was used
- Fix: Centreontrapd issue if number of downtimes is greater than 1
- Fix: Service comments wrong request
- Enh: SQL Optimisation in handling service templates

==============
Centreon 2.7.5
==============

Released July 06,2016  

The 2.7.5 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.5 follow.

Notice
------

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Features and Bug Fixes
----------------------

- Fix: Flapping configuration was not exported to Centreon Engine configuration files
- Fix: Option "test the plugin" didn't working with special characters
- Fix: It was possible to select Meta Service or BA in performance page filters
- Fix: With non admin users, it was impossible to select services in Performances page
- Fix: Non admin users could not seen services in Reporting page
- Fix: Number of hosts in Hostgroups was not good for non admin users
- Fix: Max and Min was not correct for inverted curves
- Fix: It was impossible to create Virtual metrics with web UI in french language
- Fix: Exclude Deactivate poller in configuration generation page filter
- Enh: Add an error message when no pollers are selected in configuration generation page

==============
Centreon 2.7.4
==============

Released April 14,2016

The 2.7.4 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.4 follow.

Notice
------

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Fix of an encoding problem
##########################

Following a change of encoding tables in centreon database which occurred in the 2.7.0 version, bad encoded characters appear in the Centreon web interface. Indeed, the change charset "latin1" to "utf8" was not followed by an update of the content of tables in the database.

To restore a valid encoding of special and accented characters, it is necessary to manually run the script provided by Centreon.

Warning
#######

This script should be run once and only once.

If an operator has modified/corrected special characters or accented since the 2.7.0 update, processing performed by the script will truncate the string to turn on the first special or accented character. It will then be necessary to change the impacted objects to manually update them. (The script can unfortunately provide the list of impacted objects.

All contents of table type "varchar", "char" or "text" will be updated

Prerequisites
#############

Don't forget to backup your database before doing any operations.

Installation
############

Download and install the script in "/usr/share/centreon/bin/" with the command:

wget http://resources.centreon.com/upgrade-2.6-to-2.7/migrate_utf8.php -O /usr/share/centreon/bin/migrate_utf8.php

Execution
#########

From a shell terminal, perform the script:

php /usr/share/centreon/bin/migrate_utf8.php

Validation
##########

Connect to your web interface and check that there are no more bad encoded characters on it.

Features and Bug Fixes
----------------------

- Fix: Contacts in contactgroups were exported with a wrong ID
- Fix: Error when saving "Administration > Parameters > Monitoring" page
- Fix: Zoom in Performance graph
- Fix: Select contactgroups / contacts in services & hosts configuration was not working
- Fix: Display only categories and not severities on form
- Fix: Scroll bar in "Configuration - Hosts - Host Groups"
- Fix: Category Relation on host and host template form
- Fix: Order in More Actions Menu
- Fix: generateSqlLite not install with source
- Fix: SSO connection with LDAP user
- Enh: Add possibility to set local to "browser" when adding a contact by CLAPI

==============
Centreon 2.7.3
==============

Released March 15,2016  

The 2.7.3 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.3 follow.

Notice
------

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Features and Bug Fixes
----------------------

- Fix Recurrent downtimes starting at 00:00
- Fix search in Poller configuration page
- Fix problems when sharing custom views
- Fix description problem with custom macros containing dash
- Fix time Interval change isn't being reflected in the polling Engine config 
- Fix Missing GMT and UTC timezone
- Fix No performance graph for host group service
- Fix ACL were showing too much objects
- Fix Impossibility to delete custom macros on service
- Fix Split on multi graph
- Fix Design on Monitoring Performances page
- Fix CLAPI handled all broker parameters
- Fix Custom macros can contain dash
- Fix Time Interval change isn't being reflected in the polling Engine config
- Fix UI doesn't display the good limit of pagination
- Fix Some French translations were missing
- Enh Improve listing possibilities in Widget configuration (Pollers and categories)
- Enh Usability of select2
- Enh Possibility to reload several pollers in one time
- Enh Add an API to send External Commands

==============
Centreon 2.7.2
==============

Released February 24, 2016

The 2.7.2 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.2 follow:

Notice
------

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Features and Bug Fixes
----------------------

- Fix eventlogs pages for performances and right for non admin users
- Fix Recurent Downtimes behavior with timezones
- Fix some broken relations in web interface
- Fix Reporting pages for non admin users
- Fix some elements with the generation of the configuration
- Fix encoding problems 
- Fix filters in configuration pages
- Fix Poller duplication
- Fix various ACL problems
- Fix some SQL queries
- Fix export of Meta Services
- Improve ACL on Custom Views 

Known Bugs
----------

- Recurrent downtimes during for more than a day are not working
- It's impossible to remove relations between usergroup and custom views
- With the update some widgets have to be deleted and recreated

==============
Centreon 2.7.1
==============

Released January 07, 2016

The 2.7.1 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.1 follow:

Notice
------

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

Features and Bug Fixes
----------------------

- Improved ergonomics of the select2 component
- Improved performances of monitoring pages
- Improved performances of the event logs page
- Improved performances of downtimes configuration on host page
- Improved documentation
- Fixed problem when sharing views in Custom views page
- Fixed a right problem in CLAPI generation of the configuration
- Fixed problem in services per hostgroups pages
- Fixed problems in configuration generation when mysql is not using 3306 port

==============
Centreon 2.7.0
==============

Released December 17, 2015

The 2.7.0 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.0 follow:

Features and Bug Fixes
----------------------

* Changing the graphic charter to be consistent with the new logo Centreon
* Flat design (CSS and icons)
* Custom view improvement

 * Adding an editing or visualization mode
 * Graphic widgets relief to be able to put more on a page

* Adding a fullscreen mode
* Menu Review for improved navigation and simplified user actions
* Review og pages dedicated hosts and services pages in monitoring to include more informations.
* Redesign of the reporting page
* Recasting bar searches and filters in each page of Centreon
* Redesign Event Logs page (removing treeview + Added search system + Improved performances)
* Redesign view page (removing treeview + Added search system + Improved performances)
* Merging downtimes pages for hosts and services
* Merging comments pages for hosts and services
* Integration of a graphics module to replace a non-performing component QuickForm (Improved forms on multi element selection)
* Simplifying the configuration of Centreon Broker (Temporary and Failover are automatically configured + enhanced best practices)
* Ergonomic improvement of the configurations objects:

 * Improved hosts form
 * Improved services form
 * Improved management macros: dynamic form system that provides the necessary inherited macros templates for proper operation of the configuration
 * Added ability to set a description of each macro used in commands
 * Review of the pathway for the generation of the configuration
 * Automatic creation of a configuration file for the poller when it is created

* Deleting configuration options in the Administration section, now automatically configured. This simplifies the handling of Centreon
* Improved ACL system (Improved performances)
* Native integration of Centreon CLAPI
* Improved documentation

 * Redesign Configuration part
 * Redesign Exploitation part
 * Integration of the API part

Changes
-------

* Important web design changes can make interface not compatible with older modules. A re-factoring work will be needed to ensure optimal operation.
* Changing the timezone system : DST management (may need to check the timezones of each host and contact after the update)
* Changing databases schemes for hostgroups and servicegroups in the real state database (centreon_storage) : added id and deletion of alias, url, url note, icon.
* Changing the path for generating the configuration of Centreon Engine instances : no more specific page to generate the configuration. The action is now available from the pollers list.
* Switching to InnoDB all Centreon tables (except logs and data_bin too big for an automatic update).
* PHP 5.1 no longer supported
* Browser compatibility : IE 11, FF 5 et Chrome 39 at least
* Shared views in custom views are not automatically loaded in views of others users. Now views are able to be public and user can load them during the creation step.

Security fixes
--------------

* Removing PHP session ID in the URL of the Ajax flow of certain pages.
* Integration of a CSRF token in all forms to prevent "Man in the middle" effect.

Removed Features
-----------------

* Nagios and NDOutils are no longer compatible with Centreon web. Only Centreon Engine and Centreon Broker are compatible from version 2.7.0
* Removing centstorage and logAnalyser executables.
* Removing the Nagios configurations load module.
* Removing the ability to configure the colors of graphics templates
* Removing color choices for menus
* Removing choosing colors for monitoring status
* Removing the ability to configure Nagios CGI
* Transformation of the tactical overview in widget
* Transformation of the Monitoring Engine statistics Page in widget
* Deleting the Server Status page (phpsysinfo) become incompatible with the PHP version recommended for Centreon
* Remove timeperiod exclusions in the UI. This function don't work very fine whether with Centreon Engine 1.x or Nagios. We prefer removing the function in order to avoid problems.

Known Bugs
----------
* ACL of pages is not fully updated during the upgrade process. So please check all your ACL pages after the migration. You may have problems with the followings pages:

 * Monitoring > Hosts
 * Monitoring > Services
 * Monitoring > Performances (new page)
 * Monitoring > Downtimes
 * Monitoring > Comments
 * Monitoring > Eventlogs > System logs

* Graph slip not working
* Pagination is broker when you go on the last page, change the number of line to the Max. Page become empty.
* If you have timeperiods used in exception or inclusion of timeperiod and now deleted, their ids stays in the database in relation table. During the sql update process, this blocks an addition of constraint on this relation table. To fix it, you have to remove old timeperiod id.::

    mysql> DELETE FROM timeperiod_exclude_relations WHERE timeperiod_id NOT IN (SELECT tp_id FROM timeperiod) OR timeperiod_exclude_id NOT IN (SELECT tp_id FROM timeperiod);
    mysql> DELETE FROM timeperiod_include_relations WHERE timeperiod_id NOT IN (SELECT tp_id FROM timeperiod) OR timeperiod_exclude_id NOT IN (SELECT tp_id FROM timeperiod);

How to Install ?
----------------

Now that you are aware about all specificities of this version, you can install it. If you install from zero your system, please follow the :ref:`installation guide <install>`. Else you can refer to the :ref:`upgrade guide <upgrade>`. Take care about prerequisites and all upgrade steps in order to avoid data loss.
