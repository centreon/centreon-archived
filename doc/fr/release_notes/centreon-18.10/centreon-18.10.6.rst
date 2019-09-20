====================
Centreon Web 18.10.6
====================

Enhancements
------------

* [LDAP] Optimizing data sent when importing contact (#7559)
* [Traps] Increase trap special command database field (#7610)
* [Traps] Make @HOSTID@ macro available for trap configuration (#7592)
* [UI] Enhance helper (tooltip) for mail configuration (#7584)
* [UI] Translate notification delay parameters (#7696)
* [Traps] You can create a trap with matching mode regexp (#7679)

Bug fixes
---------

* [Installation] Missing template directory in tar.gz package
* [Centcore] Issue fixed with commands that were overwritten (#7650)
* [Remote Server] Do not compare bugfix version on task import (#7638)
* [Remote Server] Set task in failed if an error appears during import/export (#7634)
* [Remote Server] Increase size of database field to store large FQDN (#7637 closes #7615)
* [Remote Server] Update only properties of selected poller (#7633)
* [Remote Server] Filter output to master on NEB category only (#7695)
* [Monitoring] Correctly display services with special character "+" (#7624)
* [Configuration] Correctly search services by "disabled" state (#7612)
* [Downtime] Correctly compute downtime duration & end date (#7601)
* [Event Logs] Several issues fixed on CSV export (group arrows, host filter)
* [Configuration] Correctly save service_interleave_factor value in Engine configuration form (#7591)
* [Reporting] Correctly apply ACL on reporting dashboard (#7604)
* [UI] Add scrollbar to remote server configuration wizard (#7600)
* [UI] Change icon cursor when exporting graphs to PNG (#7613)
* [Upgrade] Execute again missing PHP update from 2.8.27 (#7434)
* [Upgrade] add missing upgrade script for 2.8.28

Documentation
-------------

* [Onboarding] Improve actual content for Quick Start and add more (#7609)

Security fixes
--------------

* [UI] add escapeshellarg to nagios_bin binary passed to shell_exec (#7694 closes CVE-2019-13024)

Known issue
-----------