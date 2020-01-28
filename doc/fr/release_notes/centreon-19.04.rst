====================
Centreon Web 19.04.9
====================

Bugfix
------

* [Status Details] Services shown as CRITICAL while OK (PR #8253)
* [Status Details] Cannot empty "Hostgroup" drop-down list in "Services by Hostgroup" (PR #8257)
* [Autologin] Access to URI with arguments (PR #8262)
* [Configuration] Check command --help display won't work (PR #8255 and #8268)
* [Event Logs] Filter on disabled objects (PR #8238)
* [Services Grid] Filters not used correctly (PR #8260)

====================
Centreon Web 19.04.8
====================

Bugfix
------

* [Clapi] fix overlapping in clapi export (PR/#8191 fixes #7562)
* [Custom View] fix display for user with no widget preferences (PR #8158 fixes #7875)
* [Web] Issue with random blank pages (PR/#8187,#8193)


Security
--------

* [Service Discovery] cron should be run by centreon user (PR #8062 fixes #7921)
* [Web] bump terser-webpack-plugin to 1.4.2

====================
Centreon Web 19.04.7
====================

Documentation
-------------

* Clearly indicate that dependencies between pollers are not possible

Bug Fixes
---------

* Define new custom view error file template (PR/#8141)
* Fix double quote in widget title (PR/#8161)
* Remove ACL notice on lvl3 calculation (PR/#8120)

====================
Centreon Web 19.04.6
====================

Bug Fixes
---------

* [ACL] Fix calculation of acls on services (PR/#8146)
* [LDAP] Correct double slashes in the saved DN (PR/#8121)
* [LDAP] Move LDAP fix upgrade script to next minor (PR/#8153)

Security Fixes
--------------

* Fix call of service macros list without authentication - CVE-2019-17645 (PR/#8035)
* Fix call of host macros list without authentication - CVE-2019-17644 (PR/#8037)

====================
Centreon Web 19.04.5
====================

Enhancements
------------

* [API] Return curve metric name (PR/#8055) 
* [Configuration] Rename contact template titles properly (PR/#7929)

Bug Fixes
---------

* [API] Add macro password option for service template using CLAPI (PR/#8012)
* [API] Unable to set host notification to None through the API (PR/#8077)
* [ACL] Renaming bound variable name (PR/#7984)
* [Configuration] Fix stream connector update (PR/#7813)
* [Configuration] Remove unused radio button in meta service configuration (PR/#7992)
* [Downtimes] Apply downtime on resources linked to a poller (PR/#7955)
* [Install] Check mariaDB version before using ALTER USER (PR/#8068)
* [LDAP] ldap users using the auto-import cannot login (PR/#8113)
* [Monitoring] Fix double host name display in host detail (PR/#7737)
* [Monitoring] fix recurrent downtimes filter (PR/#7989, #7987)
* [UI] Redirect to login page when user is unauthorized (PR/#7687)
* [UI] Do not display autologin shortcut when disabled (PR/#7340)
* [UI] Correctly toggle edit load and header of widgets (PR/#8114)

Documentation
-------------

* Correct migration using nagios reader (PR/#7781)
* Correct release number for 19.04 migration (commit bfcedd15c0)
* Improve migration procedure (commit 359cb6f6fc)
* Improve prerequisites (commit 9a39911486)
* Remove install poller via VM (commit 98624e7cb5)
* Update mysql prerequisites (PR/#7903)
* Update FAQ to install RRDCacheD on el7 (PR/#8052)

Security Fixes
--------------

* Avoid SQL injections in multiple monitoring pages - CVE-2019-17647 (PR/#8063, PR/#8094)
* Add php mandatory param info for source installation (PR/#7898)
* Add rule for max session duration (PR/#7913)
* Contact list using escapeSecure method (PR/#7947)
* Cross-site scripting (reflected) - Dont' return js (PR/#8095)
* Do not allow to get all services using downtime ajax file - CVE-2019-17643 (PR/#8022)
* Escape persistent and reflected XSS in my account  - CVE-2019-16195 (PR/#7877)
* Escape script and input tags by default (PR/#7811)
* Filter access to api using external entry point - CVE-2019-17646 (PR/#8021)
* Fix default contact_autologin_key value
* Fix security on LDAP page - CVE-2019-15300 - (PR/#8008)
* Hide password in command line (#7414, PR/#7859)
* RCE on mib import from manufacturer input - CVE-2019-15298 (PR/#8023)
* Remove command test execution - CVE-2019-16405 (PR/#7864)
* Remove xss injection of service output in host form (PR/#7865) # TODO
* Sanitize host_id and service_id (PR/#7862)
* Session fixation using regenerate_session_id (PR/#7892)
* The ini_set session duration param has been moved in php.ini (PR/#7896)

Performance
-----------

* Set LDAP contactgroup synchronization every hour (PR/#8070)

Technical
---------

* Backport fix of menu memory leak (PR/#7988)
* Better handling PNG export failure (PR/#7823)
* Correct the call of static method (PR/#8025)
* Fix compatibility with IE11 (external modules) (PR/#7923)
* Improve coding style checks (PR/#7843)
* Improve centreonworker logging (PR/#7712)
* Move alter table statement in a php script (PR/#7838)
* Optimize select all in select2 component (#7926)
* Retrieve menu entries as link (#7847)

====================
Centreon Web 19.04.4
====================

Enhancements
------------

* [Administration] Add the possibility to define the refresh frequency for LDAP settings for users (PR/#7627)
* [API] Update output of getparam command on host object (PR/#7678)
* [Configuration] Close tooltip when user clicks somewhere else (PR/#7729)

Bug fixes
---------

* [ACL] Add ACL to select meta-services for service performance (#6534, PR/#7736)
* [Backup] Change backup path of httpd24-httpd (PR/#7577)
* [Configuration/Administration] Fix filters save with pagination (PR/#7732)
* [Configuration] Fix meta service generation with special char (#7608, PR/#7705)
* [Configuration] Trap generation reindexing pollers id (#6205, PR/#6416)
* [Clapi] Delete services when host template is detached from host (#4371, PR/#7784)
* [Clapi] Fix import of contactgroup when linked to ldap (PR/#7797)
* [Centcore] Use correct ssh port (PR/#7677)
* [Graphs] Issue with export of splitted graphs fixed (PR/#7822)
* [Menu] translate properly menu entries
* [Monitoring] Fix pagination display in service monitoring (PR/#7755)
* [Remote-Server] Check bam installation on remote server is http only (#7626, PR/#7640)
* [Remote-Server] Fix enableremote parameters parsing and setting (PR/#7711)
* [System] Compatibility with MySQL v8
* [UI] Remove chrome password autocomplete in several form (#6283, PR/#7697)
* [UI] Custom view page is no longer broken with spanish language (PR/#7778)

Documentation
-------------

* Correct CLAPI Host parameters (PR/#7658)
* Correct SSH exchange notice (#7620, PR/#7639)

Technical
---------

* [Lib] update composer

====================
Centreon Web 19.04.3
====================

Enhancements
------------

* [Traps] Increase trap special command database field (#7610)
* [Traps] Make @HOSTID@ macro available for trap configuration (#7592)
* [Traps] You can create a trap with matching mode regexp (#7679)
* [UI] Enhance helper (tooltip) for mail configuration (#7584)
* [UI] Translate notification delay parameters (#7696)

Bug fixes
---------

* [Centcore] Issue fixed with commands that were overwritten (#7650)
* [Configuration] Correctly save service_interleave_factor value in Engine configuration form (#7591)
* [Configuration] Correctly search services by "disabled" state (#7612)
* [Downtime] Correctly compute downtime duration & end date (#7601)
* [Event Logs] Several issues fixed on CSV export (group arrows, host filter)
* [Installation] Missing template directory in tar.gz package
* [Monitoring] Correctly display services with special character "+" (#7624)
* [Remote Server] Update only properties of selected poller (#7633)
* [Remote Server] Do not compare bugfix version on task import (#7638)
* [Remote Server] Increase size of database field to store large FQDN (#7637 closes #7615)
* [Remote Server] Set task in failed if an error appears during import/export (#7634)
* [Remote Server] Filter output to master on NEB category only (#7695)
* [Reporting] Correctly apply ACL on reporting dashboard (#7604)
* [UI] Add scrollbar to remote server configuration wizard (#7600)
* [UI] Change icon cursor when exporting graphs to PNG (#7613)
* [Upgrade] Issue with upgrade from 18.10.x to 19.04.x (#7602 closes #7596)

Documentation
-------------

* [Onboarding] Improve actual content for Quick Start and add more (#7609)

Security fixes
--------------

* [UI] add escapeshellarg to  nagios_bin binary passed to shell_exec (#7694 closes CVE-2019-13024)

====================
Centreon Web 19.04.2
====================

Bug fixes
---------

* [LDAP] optimizing the data sent when importing contact (PR/#7559)
* [Web] expose properly react router dom (PR/#7582)
* [Web] retrieve loading animation (PR/#7587)
* [Web] retrieve scrollbar on internal react pages

====================
Centreon Web 19.04.1
====================

Enhancements
------------

* [Graphs] Add more curves template for fresh installations (#5819, #7530)
* [Remote Server] Add possibility to use HTTPS or HTTP for communication and to define TCP port (PR/#7536)
* [Remote Server] Add possibility to verify or not peer SSL certificate (PR/#7536)
* [Remote Server] Add possibility to use or not configured proxy (PR/#7536)

Bug fixes
---------

* [ACL] Fix issue with monitoring pages (PR/#7554)
* [Administration] Correct the redirection after submitting the monitoring form (PR/#7545)
* [Packaging] Install systemd .service files with 644 permissions
* [Web] Fix date format for CSV export (PR/#7533)
* [Web] Correct the displayed saved researched value in the select2 components (PR/#7525)
* [Packaging] fix installation of conf.pm and centreontrapd.pm
* [Monitoring] Fix hard_state_duration column (#7506)
* [Graphs] No-unit series now trigger a second axis (Closes #7330 with #7341)
* [Graphs] "Split chart" mode do not show thresholds (Closes #7342,#7235 with #7343)
* [Monitoring] Macros not displayed in WUI for new services when you select your template (Fixes #7121 with #7515, #7535)
* [Monitoring] Filter issues on host monitoring page fixed (#7511)

Security fixes
--------------

* [ACL] Fix ACL calculation when interfering with the GET request (PR/#7517)

====================
Centreon Web 19.04.0
====================

New features
------------

* The extension management page has been unified. The installation, update and removal of modules and widgets are available via the "Administration> Extensions> Manager" menu. It is now possible to install all extensions at one time or to update all extensions in one click. Moreover a detail page provides access to the description of the extensions.
* Improved navigation within the menu. It can be used both open (by clicking on Centreon logo) and closed to navigate within the Centreon web interface. Closed, only one click is required to access the desired page. Open, it is possible to navigate a menu by opening and closing the submenus or to access another menu in a click.

Enhancements
------------

* [CEIP] Add additional statistics including modules if present (PR/#7328)
* [Configuration] improve filters and pagination in the configuration menus (PR/#7348)
* [Debug] centreon_health script to gather various data (PR/#7418)
* [Install] New upgrade process that can start only from *2.4.0* and later
* [LDAP] Optimize ldap sync at config generation (#6949 PR/#7130)
* [Menu] Remove unnecessary menu level 
* [Menu] Color the open level 2 and 3 menus (PR/#7295)
* [Remote-server] allow usage of domain names (PR/#7250)
* [UI] Fix wording of messages related to recurring downtimes (PR/#7261)
* Standardize how to display menus access
* Reduce reduce number of title levels displayed in index
* Create dedicated UI access administration chapter
* Improve custom uri chapter
* Move SSO chapter to administration/ldap

Bug fixes
---------

* [API] Use the web service or initialize it (PR/#7265)
* [API] Fix init parameters (PR/#7277)
* [Backup] partial backup didn't backup the right partitions
* [Broker] change default value for centreonbroker_logs_path
* [Broker] Broker configuration doesn't generate rrdcached external information in a new install
* [CEIP] Improve ceip install update (PR/#7374)
* [Centcore] Don't generate blank line in centcore.cmd
* [Centcore] Enhance centcore log
* [Centcore] Fix getinfos information
* [Configuration] change size (6 => 30) of input geo coordinates on host form (PR/#7405)
* [Install] Remove non-existing topology_JS entries
* [Install] Remove obsolete rrdtool configuration and sources (PR/#7195)
* [Install] use /etc/sysconfig/cent* files to get options for Centcore and Centreontrapd process (PR/#7380)
* [LDAP] Fix sql errors in the log on authentication (PR/#7278)
* [LDAP] Optimize ldap sync at config generation (Fix #6949 PR/#7130)
* [Logs] removing warning in the logs (PR/#7395)
* [Menu] Fixing an issue with the menu when loaded by mobile browsers (PR/#7256)
* [Monitoring] Fix hide password in command line (PR/#7079)
* [Translation] fix translation for broker logs path
* [Translation] missing French translations in the graph page (PR/#7429)
* [logAnalyser] Code refactor
* [perl scripts] enhance logger lib to handle utf8

Documentation
-------------

* Restart php-fpm instead of Apache for changes in php.ini (PR/#7332)
* Add EN & FR chapters for data retention (PR/#7269)
* Describe how to enable user audit log in doc (PR/#7276)
* Improve partitioning chapter (PR/#7274)
* Correct installation chapters - enable systemctl for centreon (PR/#7284)
* Add FAQ for known issues about Remote Server (PR/#7266)

Security fixes
--------------

* Authenticated RCE in minPlayCommand.php (PR/#7232)
* SQL injections in the service by hostgroups and servicegroups pages (PR/#7267)
* Allow to set illegal characters for centcore (PR/#7206 PR/#7287)
* Token generation uses predictable generator
* Authenticated SQL injection in makeXML_ListServices.php
* SQL Injection in serviceGridByHGXML.php

Technical
---------

* Add mechanism to manage external pages (PR/#7382)
* Add mechanism to manage notification mechanism of modules (PR/#7378)

Known issue
-----------

Depending on the size of your screen and which level 3 menu is opened, you may have difficulty to access to another menu. Just close the opened level 3 menu before navigating to another menu.
