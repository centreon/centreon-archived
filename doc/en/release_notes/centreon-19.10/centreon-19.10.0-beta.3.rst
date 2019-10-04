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
