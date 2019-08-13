===========================
Centreon Web 19.10.0-beta.2
===========================

New features
------------

Enhancements
------------

* [Administration] [Audit logs] Add purge function for audit logs (PR/#7710) [beta.1]
* [Charts] Centreon-Web Graph Display and png export is coherent (PR/#7676) [beta.1]
* [Charts] Better management of virtual metrics: you can display or not a virtual metric (PR/#7676) [beta.1]
* [Charts] Only one color by curve: users see the same color curve (PR/#7676) [beta.1]
* [Configuration] Add contactgroups filter in list of contacts (PR/#7744) [beta.2]
* [Configuration] Add status and vendor filters in list of SNMP traps (PR/#7758) [beta.2]
* [Configuration] Fix SNMP traps generation by poller (PR/#6416) [beta.2]
* [Install] Allow people to use another user that has root privileges when installing centreon (PR/#7445) [beta.1]

Performance
-----------

* Increase performance on server side when we get data from rrd files to display charts: between 70% and 90% (PR/#7676) [beta.1]

Bug fixes
---------

* [ACL] add ACL to select meta-services for list of services in performance menu (PR/#7736) [beta.2]
* [Charts] Fix export png for splitted graph (PR/#7676) [beta.1]
* [Charts] Graph is smoothed to much (PR/#7676, #4898) [beta.1]
* [Charts] Unit curves not displayed when only 1 metric (PR/#7676, #5533) [beta.1]
* [Charts] strange char & missing dates in exports (PR/#7676, #7310) [beta.1]
* [Charts] HTML code instead of accented characters in graphs (PR/#7676, #6318) [beta.1]
* [Charts] Graphs Period Showing Different Times (PR/#7676, #5939) [beta.1]
* [Monitoring] Fix pagination display in service monitoring by servicegroups (PR/#7755) [beta.2]
* [Widget] set GMT to default if null (PR/#7766) [beta.2]

Technical
---------

* Compatibility with rrdtool >= 1.7.x (PR/#7676) [beta.1]
* Compatibility with MariaDB/MySQL STRICT mode (PR/#7544) [beta.1]
* [Database] Remove useless primary keys on multiple tables (PR/#7542) [beta.1]
* [Database] Change type of column widget_models.description to TEXT (PR/#7542) [beta.1]
* [Database] Add default value to acl_groups.acl_group_changed table (PR/#7542) [beta.1]
* [Lib] Upgrade front libraries & improve dynamic import (PR/#7724) [beta.2]
* Remove wizard graph tour in performance view (PR/#7676) [beta.1]
* Update to rh-php72 (PR/#7542) [beta.1]
