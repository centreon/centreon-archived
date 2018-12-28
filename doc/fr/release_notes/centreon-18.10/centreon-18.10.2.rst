====================
Centreon Web 18.10.2
====================

Enhancements
------------

* [Configuration] Prevent time period to call itself via templates - PR #7024
* [Configuration] Re-add the PID column in the poller list page - PR #6993
* [Documentation] Add clean yum cache command for 18.10 upgrade - PR #7030
* [Documentation] Correct typo in RS architecture FR chapter - PR #6965
* [Downtimes] Apply ACL on resources to configure recurring downtimes - PR #6962
* [Translate] Add all date picker libraries for new translation - PR #7040
* [UX] Improve full screen mode - PR #6976

Bug fixes
---------

* [Chart] Fix graph export when a curve is only displayed in legend - PR #7009
* [Documentation] Describe DBMS minimal version to prevent partitioning tables issue - PR #6974
* [Monitoring] Use all selected filter on refresh with "play" button - PR #6984
* [Extensions] Fix module upgrades using php scripts - PR #7073
* [Remote Server] Update default path of broker watchdog logs

Technical
---------

* Update select2 component - PR #7034
