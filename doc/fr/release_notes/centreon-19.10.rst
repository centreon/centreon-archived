====================
Centreon Web 19.10.4
====================

Documentation
-------------

* Clearly indicate that dependencies between pollers are not possible

Improvements
------------

* [Downtimes] Manage downtimes for host and service (PR/#8110)

Bug Fixes
---------

* [Custom Views] Define new custom view error file template (PR/#8141)
* [Custom Views] Fix double quote in widget title (PR/#8161)
* [ACL] Remove ACL notice on lvl3 calculation (PR/#8120)
* [Configuration] Fix performance regression in notification system (PR/#8143)
* [Remote] Host and service templates are not properly imported (PR/#8147)
* [Topology] Correct URL options for service pages (PR/#8164)

====================
Centreon Web 19.10.3
====================

Bug Fixes
---------

* [LDAP] Correct double slashes in the saved DN (PR/#8121)

Security Fixes
--------------

* Fix call of service macros list without authentication - CVE-2019-17645 (PR/#8035)
* Fix call of host macros list without authentication - CVE-2019-17644 (PR/#8037)

====================
Centreon Web 19.10.2
====================

Bug Fixes
---------

* LDAP users using DN with special chars cannot login
* LDAP connection issue
* Select all elements in select2 freeze the screen
* Non synchronized curves when using rrdcached
* Missing selection of Okta template for LDAP
* Trap matches and hostgroups break export in Remote Server
* Trap export on Remote Server fails
* Recurrent downtimes search bug
* Unable to hide service template macro with Clapi
* Macro passwords can be visible
* Calculation of contact groups too frequent
* Additional Remote Server config fails
* Unable to set host notification to None through API
* Remove unused radio button in meta-service configuration
* Contact template notification parameters are not inherited
* Filter "Name" is emptied out when searching in host configuration
* Incorrect CSV export in Event Logs
* Add/List/Cancel downtimes on resources
* Correctly toggle edit when widgets load
* Using rrdcached provides non synchronized curves
* Add curve label in API
* Poller statistics charts are missing
* Several trap definitions with same OID will not work

Security
--------

* No check for authentication
* SQL injections
* RCE flaws
* XSS
* Authentication flaw

Documentation
-------------

* Display release notes per section in upgrade process
* Update performance FAQ for rrdcached

====================
Centreon Web 19.10.1
====================

Bug Fixes
---------

* [Install/update] correct loop issue on installation/update (PR/#7997)

====================
Centreon Web 19.10.0
====================

Features
--------

* [Authentication] Add Keycloak SSO authentication in Centreon (PR/#7700)
* [API v2] New real time monitoring JSON REST API v2 for services and hosts - currently in beta version (PR/#7821)
* [API v2] Manage acknowledgements (PR/#7907)
* [Notification] Add new options for Contacts & Contact groups method calculation (PR/#7917, PR/#7960, PR/#7963, PR/#7965, PR/#7971):

  * *Vertical Inheritance Only*: get contacts and contactgroups of resources and linked templates, using additive inheritance enabled option (Legacy method, keep for upgrade)
  * *Closest Value*: get most closed contacts and contactgroups of resources including templates
  * *Cumulative inheritance*: Cumulate all contacts and contactgroups of resources and linked templates (method used for new installation)

Enhancements
------------

* [Administration] [Audit logs] Add purge function for audit logs (PR/#7710)
* [Authentication] Add Okta LDAP template (PR/#7825)
* [Charts] Centreon-Web Graph Display and png export is coherent (PR/#7676)
* [Charts] Better management of virtual metrics: you can display or not a virtual metric (PR/#7676)
* [Charts] Only one color by curve: users see the same color curve (PR/#7676)
* [Configuration] Add display locked checkbox for objects listing (#7444)
* [Configuration] Add contactgroups filter in list of contacts (PR/#7744)
* [Configuration] Add status and vendor filters in list of SNMP traps (PR/#7758)
* [Configuration] Move global rrdcached option to Centreon Broker form for each broker (PR/#7791)
* [Configuration] Allow to redifine action command for Centeron Engine & Centreon Broker (PR/#7810)
* [Install] Allow people to use another user that has root privileges when installing centreon (PR/#7445)
* [Install] Add possibility to install widget during last step (PR/#7890)
* [Install] New script that aims at automating all manual steps that are required when installing Centreon from packages (PR/#7853)
* [Remote Server] Poller attached to multiple remote servers (PR/#7849)
* [Remote-Server] Allow to use direct ssh connection to poller from central (PR/#7680)
* [Remote-Server] Optimize execution time of export/import (PR/#7749)
* [Remote-Server] Improve centreonworker logging (PR/#7712)
* [UI] Do not display round values in detailed top counter (PR/#7547)
* [UI] Style default select to be as much like select2 as possible (PR/#7819)
* [UI] Update style of checkbox, radio, tabs (PR/#7845)
* [UI] Adding cursor pointer to icons (PR/#7613)
* [Widgets] Add multiselect on severity preference (PR/#7752)
* [Widgets] Upgrade poller preference of engine-status widget (PR/#7820)
* [Widgets] Add connectors for servicegroups and severities (PR/#7753)

Performance
-----------

* [ACL] centAcl optimize memory and time execution (PR/#7751)
* [API] Improve performance of clapi call through REST API (PR/#7842)
* [Chart] Increase performance on server side when we get data from rrd files to display charts: between 70% and 90% (PR/#7676)

Documentation
-------------

* Doc correct migration using Nagios reader (PR/#7781)
* Update MySQL prerequisites for master (PR/#7904)
* Improve documentation for MySQL/MariaB strict mode (PR/#7806)
* Improve migration procedure (commit 47be1c3)
* Improve prerequisites (commit 7200461)
* Fix typo Centreon word (and one variable) (PR/#7796, PR/#7806)
* Add link to Centreon API JSON REST v2 (commit bfac416)
* Add OS update (commit 04e9942)

Bug Fixes
---------

* [ACL] Redirect to login page when user is unauthorized (PR/#7687)
* [ACL] Add ACL to select meta-services for list of services in performance menu (PR/#7736)
* [ACL] Fix cron renaming bound variable name (PR/#7984)
* [API] Delete services when host template is detached from host (PR/#7784)
* [API] Fix import of contactgroup when linked to ldap (PR/#7797)
* [API v2] Fix bad verification when an admin has access group (PR/#7972)
* [Charts] Fix export png for splited graph (PR/#7676)
* [Charts] Graph is smoothed to much (PR/#7676, #4898)
* [Charts] Unit curves not displayed when only 1 metric (PR/#7676, #5533)
* [Charts] strange char & missing dates in exports (PR/#7676, #7310)
* [Charts] HTML code instead of accented characters in graphs (PR/#7676, #6318)
* [Charts] Graphs Period Showing Different Times (PR/#7676, #5939)
* [Charts] Match metric name with metric value in export (#5959, #7477, PR/#7764)
* [Centcore] Correct typo in scp command (#7849, PR/#7946)
* [Centcore] Create centcore file by action (PR/#6985)
* [Configuration] Correct issue in wizard with PR #7849 (commit 2b8a728478)
* [Configuration] Fix style of broker modules options checkboxes (PR/#7899)
* [Configuration] Select also pollers attached to additional RS for generation (PR/#7922)
* [Configuration] Fix the manual activation/disactivation of a contact (PR/#7930)
* [Configuration] List contact using escapeSecure method (PR/#7947)
* [Configuration] Fix SNMP traps generation by poller (PR/#6416)
* [Configuration] Fix stream connector configuration update in Centreon Broker form (PR/#7813)
* [Custom-Views] Correction on custom view using spanish (PR/#7778)
* [Dashboard] Remove useless columns which break sql strict mode (PR/#7937)
* [i18n] Fix issue with translation when several modules are installed (PR/#7916)
* [Install] Change the bash interpreter for the native sh (commit (PR/#7911))
* [Install] Update wording about cache in install/upgrade process (PR/#7895)
* [Install] Fix syntax error in step5 of upgrade process (PR/#7900)
* [Install] Disable button when installing modules last step (PR/#7873)
* [Menu] Retrieve menu entries as link (PR/#7826)
* [Monitoring] Apply downtimes on resources linked to a poller (PR/#7955)
* [Monitoring] Save properly monitoring service status filter (PR/#7908)
* [Monitoring] Fix pagination display in service monitoring by servicegroups (PR/#7755)
* [Monitoring] Fix labels in graph alignment for service details page (PR/#7805)
* [Monitoring] Fix double host name display in host details page (PR/#7737)
* [Remote-Server] Allow remote server config to be loaded with mysql strict mode enabled (PR/#7887)
* [Remote Server] Change grant option for remote server database centreon user (PR/#7888)
* [Remote Server] set remote_id/remote_server_centcore_ssh_proxy to NULL/0 (PR/#7878)
* [Remote Server] Fix simple remote server creation (PR/#7936)
* [Remote Server] Add missing host poller relation in export (PR/#7928)
* [Remote-Server] Adapt nagios_server export columns (PR/#7871)
* [UI] Do not display autologin shortcut when disabled (PR/#7340)
* [UI] Avoid host icon to be flattened (PR/#7870)
* [UI] Retrieve space before alias in user menu (PR/#7869)
* [UI] Fix compatibility with IE11 (external modules) (PR/#7923)
* [UI] Rename contact template titles properly (PR/#7929)
* [UI] Fix style of frozen checkboxes (PR/#7882)
* [Widgets] Undefined pagination variable when editing custom view (PR/#7935)
* [Widgets] set GMT to default if null (PR/#7766)

Security fixes
--------------

* Add rule for max session duration (PR/#7918)
* Hide password in command line for status details page (#7414, PR/#7859)
* Escape script and input tags by default (PR/#7811)
* Add php mandatory params info in source installation (PR/#7897)
* Escape persistent and reflected XSS in my account (PR/#7877)
* Remove xss injection of service output in host form (PR/#7865)
* Sanitize host_id and service_id in makeXMLForOneService.php (PR/#7862)
* Session fixation using regenerate_session_id (PR/#7892)
* Remove command test execution - CVE 2019-16405 (PR/#7864)
* the ini_set session duration param has been moved in php.ini (PR/7896)

Technical
---------

* [API] Update type of returned activate property (PR/#7851)
* [CEIP] Telemetry ceip improvements (PR/#7931)
* [Component] Compatibility with RRDtool >= 1.7.x (PR/#7676)
* [Component] Update to rh-php72 (PR/#7542)
* [Composer] Reduce size of centreon package on packagist (PR/#7818)
* [Composer] Add missing translation dependency in composer.json (PR/#7879)
* [Configuration] Move filesGeneration directory to /var/cache/centreon (PR/#7735)
* [Core] Improve the centreon user service definition in ServiceProvider (PR/#7891)
* [CSS] Clean cache at each new centreon version (PR/#7959)
* [Database] Start compatibility with MariaDB/MySQL STRICT mode - in progress (PR/#7544)
* [Database] Remove useless primary keys on multiple tables (PR/#7542)
* [Database] Change type of column widget_models.description to TEXT (PR/#7542)
* [Database] Add default value to acl_groups.acl_group_changed table (PR/#7542)
* [Database] Update column types of downtimes table (PR/#793)
* [Database] Compatibility with MySQL v8.x version (PR/#7801)
* [Install] Do not require conf.php files to exist in module upgrade directories (PR/#7914)
* [Lib] Upgrade front libraries & improve dynamic import (PR/#7724)
* [Select2] Fix default select2 getter on severity (PR/#7814)
* [Select2] Allow to display disabled status in select2 options (PR/#7531)
* [Test] Fix acceptance test of locked elements (PR/#7910)
* [Update] Move alter table statement in a php script for MySQL compatibility (PR/#7838)
* [Upgrade] Take into account the removal of older conf.php (PR/#7952)
* [Update] Remove upgrade of bigint columns (PR/#7953)
* [UI] Remove wizard graph tour in performance view (PR/#7676)
* [Update] Finish module update with upgrade to last version (PR/#7956)

Known issue
-----------

* [logs] Fix the limitation of max value for the primary key of the centreon_storage.logs table (:ref:`update_centreon_storage_logs`)

=========================
Centreon Web 19.10.0-rc.1
=========================

Enhancements
------------

* [authentication] Add okta LDAP template (PR/#7825)
* [Configuration] Add display locked checkbox for objects listing (#7444)
* [Install] Add possibility to install widget during last step (PR/#7890)
* [Remote Server] Poller attached to multiple remote servers (PR/#7849)
* [UI] Do not display round values in detailed top counter (PR/#7547)

Documentation
-------------

* Doc correct migration using nagios reader (PR/#7781)
* Update mysql prerequisites for master (PR/#7904)

Bug Fixes
---------

* [Centcore] Create centcore file by action (PR/#6985)
* [Configuration] Correct issue in wizard with PR #7849 (commit 2b8a728478)
* [Configuration] Fix style of broker modules options checkboxes (PR/#7899)
* [Install] Change the bash interpreter for the native sh (commit (PR/#7911))
* [Install] Update wording about cache in install/upgrade process (PR/#7895)
* [Install] Fix syntax error in step5 of upgrade process (PR/#7900)
* [Monitoring] Save properly monitoring service status filter (PR/#7908)
* [Remote-Server] Allow remote server config to be loaded with mysql strict mode enabled (PR/#7887)
* [Remote Server] Change grant option for remote server database centreon user (PR/#7888)
* [Remote Server] set remote_id/remote_server_centcore_ssh_proxy to NULL/0 (PR/#7878)
* [UI] Fix style of frozen checkboxes (PR/#7882)

Security fixes
--------------

* Hide password in command line for status details page (#7414, PR/#7859)
* Escape script and input tags by default (PR/#7811)
* Add php mandatory params info in source installation (PR/#7897)
* Escape persistent and reflected XSS in my account (PR/#7877)
* Remove xss injection of service output in host form (PR/#7865)
* Sanitize host_id and service_id in makeXMLForOneService.php (PR/#7862)
* Session fixation using regenerate_session_id (PR/#7892)
* Remove command test execution - CVE 2019-16405 (PR/#7864)
* the ini_set session duration param has been moved in php.ini (PR/7896)

Technical
---------

* [Core] Improve the centreon user service definition in ServiceProvider (PR/#7891)
* [Test] Fix acceptance test of locked elements (PR/#7910)

Known issue
-----------

* [logs] Fix the limitation of max value for the primary key of the centreon_storage.logs table (:ref:`update_centreon_storage_logs`)

===========================
Centreon Web 19.10.0-beta.3
===========================

New features
------------

* [Authentication] Add Keycloak SSO authentication in Centreon (PR/#7700)
* [API] New real time monitoring API for services and hosts (PR/#7821)

Enhancements
------------

* [Configuration] Move global rrdcached option to Centreon Broker form for each broker (PR/#7791)
* [Configuration] Allow to redifine action command for Centeron Engine & Centreon Broker (PR/#7810)
* [Install] New script that aims at automating all manual steps that are required when installing Centreon from packages (PR/#7853)
* [Remote-Server] Allow to use direct ssh connection to poller from central (PR/#7680)
* [Remote-Server] Optimize execution time of export/import (PR/#7749)
* [Remote-Server] Improve centreonworker logging (PR/#7712)
* [UI] Style default select to be as much like select2 as possible (PR/#7819)
* [UI] Update style of checkbox, radio, tabs (PR/#7845)
* [UI] Adding cursor pointer to icons (PR/#7613)
* [Widgets] Add multiselect on severity preference (PR/#7752)
* [Widgets] Upgrade poller preference of engine-status widget (PR/#7820)
* [Widgets] Add connectors for servicegroups and severities (PR/#7753)

Documentation
-------------

* Improve documentation for MySQL/MariaB stric mode (PR/#7806)
* Improve migration procedure (commit 47be1c3)
* Improve prerequisites (commit 7200461)
* Fix typo Centreon word (and one variable) (PR/#7796, PR/#7806)

Performance
-----------

* [ACL] centAcl optimize memory and time execution (PR/#7751)
* [API] Improve performance of clapi call through REST API (PR/#7842)

Bug fixes
---------

* [ACL] Redirect to login page when user is unauthorized (PR/#7687)
* [API] Delete services when host template is detached from host (PR/#7784)
* [API] Fix import of contactgroup when linked to ldap (PR/#7797)
* [Charts] Match metric name with metric value in export (#5959, #7477, PR/#7764)
* [Configuration] Fix stream connector configuration update in Centreon Broker form (PR/#7813)
* [Custom-Views] Correction on custom view using spanish (PR/#7778)
* [Install] Disable button when installing modules last step (PR/#7873)
* [Menu] Retrieve menu entries as link (PR/#7826)
* [Monitoring] Fix labels in graph alignment for service details page (PR/#7805)
* [Monitoring] Fix double host name display in host details page (PR/#7737)
* [Remote-Server] Adapt nagios_server export columns (PR/#7871)
* [UI] Do not display autologin shortcut when disabled (PR/#7340)
* [UI] Avoid host icon to be flattened (PR/#7870)
* [UI] Retrieve space before alias in user menu (PR/#7869)

Technical
---------

* Compatibility with MySQL v8.x version (PR/#7801)
* [API] Update type of returned activate property (PR/#7851)
* [Composer] Reduce size of centreon package on packagist (PR/#7818)
* [Composer] Add missing translation dependency in composer.json (PR/#7879)
* [Configuration] Move filesGeneration directory to /var/cache/centreon (PR/#7735)
* [Select2] Fix default select2 getter on severity (PR/#7814)
* [Select2] Allow to display disabled status in select2 options (PR/#7531)
* [Update] Move alter table statement in a php script for MySQL compatibility (PR/#7838)

===========================
Centreon Web 19.10.0-beta.2
===========================

Enhancements
------------

* [Configuration] Add contactgroups filter in list of contacts (PR/#7744)
* [Configuration] Add status and vendor filters in list of SNMP traps (PR/#7758)
* [Configuration] Fix SNMP traps generation by poller (PR/#6416)

Bug fixes
---------

* [ACL] add ACL to select meta-services for list of services in performance menu (PR/#7736)
* [Monitoring] Fix pagination display in service monitoring by servicegroups (PR/#7755)
* [Widget] set GMT to default if null (PR/#7766)

Technical
---------

* [Lib] Upgrade front libraries & improve dynamic import (PR/#7724)

===========================
Centreon Web 19.10.0-beta.1
===========================

Enhancements
------------

* [Charts] Centreon-Web Graph Display and png export is coherent (PR/#7676)
* [Charts] Better management of virtual metrics: you can display or not a virtual metric (PR/#7676)
* [Charts] Only one color by curve: users see the same color curve (PR/#7676)
* [Install] Allow people to use another user that has root privileges when installing centreon (PR/#7445)
* [Administration] [Audit logs] Add purge function for audit logs (PR/#7710)

Performance
-----------

* Increase performance on server side when we get data from rrd files to display charts: between 70% and 90% (PR/#7676)

Bug fixes
---------

* [Charts] Fix export png for splitted graph (PR/#7676)
* [Charts] Graph is smoothed to much (PR/#7676, #4898)
* [Charts] Unit curves not displayed when only 1 metric (PR/#7676, #5533)
* [Charts] strange char & missing dates in exports (PR/#7676, #7310)
* [Charts] HTML code instead of accented characters in graphs (PR/#7676, #6318)
* [Charts] Graphs Period Showing Different Times (PR/#7676, #5939)

Technical
---------

* Compatibility with rrdtool >= 1.7.x (PR/#7676)
* Start compatibility with MariaDB/MySQL STRICT mode - in progress (PR/#7544)
* [Database] Remove useless primary keys on multiple tables (PR/#7542)
* [Database] Change type of column widget_models.description to TEXT (PR/#7542)
* [Database] Add default value to acl_groups.acl_group_changed table (PR/#7542)
* Remove wizard graph tour in performance view (PR/#7676)
* Update to rh-php72 (PR/#7542)
