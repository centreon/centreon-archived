===========================
Centreon Web 19.10.0-beta.1
===========================

New features
------------

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
* Start compatibility with MariaDB/MySQL STRICT mode - in progres (PR/#7544)
* [Database] Remove useless primary keys on multiple tables (PR/#7542)
* [Database] Change type of column widget_models.description to TEXT (PR/#7542)
* [Database] Add default value to acl_groups.acl_group_changed table (PR/#7542)
* Remove wizard graph tour in performance view (PR/#7676)
* Update to rh-php72 (PR/#7542)
