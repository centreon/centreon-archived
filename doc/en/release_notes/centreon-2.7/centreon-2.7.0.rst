##############
Centreon 2.7.0
##############

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
* Review og pages dedicated hosts and services pages in monitoring to include to include more informations.
* Redesign of the reporting page
* Recasting bar searches and filters in each page of Centreon
* Redesign Event Logs page (removing treeview + Added search system + Improved performances)
* Redesign view page (removing treeview + Added search system + Improved performances)
* Merging downtimes pages for hosts and services
* Merging comments pages for hosts and services
* Integration of a graphics module to replace a non-performing component QuickForm (Improved forms on multi element selection)
* Simplifying the configuration of Centreon Broker (Temporary and Failover are automatically configured + enhanced best practices)
* Ergonomic improvement of the congigurations objects:
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

* Important web design changes can make interface not compatible with older modules. A refactoring work will be needed to ensure optimal operation.
* Changing the timezone system : DST management (may need to check the timezones of each host and contact after the update)
* Changing databases schemes for hostgroups and servicegroups in the real state database (centreon_storage) : added id and deletion of alias, url, url note, icon.
* Changing the path for generating the configuration of Centreon Engine instances : no more specific page to generate the configuration. The action is now available from the pollers list.
* Switching to InnoDB all Centreon tables (except logs and data_bin too big for an automatic update).
* PHP 5.1 no longer supported
* Browser compatibility : IE 11, FF 5 et Chrome 39 at least
* Shared views in custom views are not atomaticaly loaded in views of others users. Now views are able to be public and user can load them during the creation step.@

Secutiry fixes
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

Known Bugs
----------
* ACL of pages is not fully updated during the upgrade process. So please check all your ACL pages after the migration. You may have problems with the followings pages:
 * Monitoring > Hosts
 * Monitoring > Services
 * Monitoring > Performances (new page)
 * Monitoring > Downtimes
 * Monitoring > Comments
 * Monitoring > Eventlogs > System logs


