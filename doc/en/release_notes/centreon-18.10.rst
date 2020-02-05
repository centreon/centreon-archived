====================
Centreon Web 18.10.9
====================

Documentation
-------------

* Clearly indicate that dependencies between pollers are not possible

Bugfix
------

* Define new custom view error file template (PR/#8141)
* Fix double quote in widget title (PR/#8161)

Security
--------

* Host macro list without authentication - CVE-2019-17644 (PR/#8037)
* Service macro list without authentication - CVE-2019-17645 (PR/#8035)

====================
Centreon Web 18.10.8
====================

Bug fixes
---------

* Missing Centengine configuration options
* Unable to add downtime to service groups
* The option to hide auto login has no effect
* Macro passwords are not hidden
* Broker form might be lost when saving configuration
* LDAP contact groups are not exported properly
* Better error handling when PNG generation fails
* Double host name display in host detail
* Metric name are not properly ordered on CSV export
* Incorrect CSV export of Event Logs
* Recurrent downtimes search bug
* Unable to hide service template macro with Clapi
* Purge old user actions
* Remove unused radio button in meta-service configuration

Security
--------

* No check for authentication
* SQL injection
* Cross-site request forgery
* Session fixation
* RCE flaws
* Authentication flaws
* XSS
* SQL injections

Documentation
-------------

* Update performance FAQ for rrdcached

====================
Centreon Web 18.10.7
====================

Enhancements
------------

* [Configuration] Close tooltip when user clicks somewhere else (PR/#7729)

Bug fixes
---------

* [ACL] Add ACL to select meta-services for service performance (#6534, PR/#7736)
* [Configuration/Administration] Fix filters save with pagination (PR/#7732)
* [Configuration] Fix meta service generation with special char (#7608, PR/#7705)
* [Configuration] Trap generation reindexing pollers id (#6205, PR/#6416)
* [Centcore] Use correct ssh port (PR/#7677)
* [Clapi] Delete services when host template is detached from host (#4371, PR/#7784)
* [Graphs] Issue with export of splitted graphs fixed (PR/#7822)
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

====================
Centreon Web 18.10.5
====================

Enhancements
------------

* [Centcore] Enhance centcore process logs (PR/#7243)
* [Core] Enhance logger lib to handle utf8 (PR/#7404)
* [Graphs] Add more curves template for fresh installations (#5819, #7530)
* [Remote Server] Add possibility to use HTTPS or HTTP for communication and to define TCP port (PR/#7536)
* [Remote Server] Add possibility to verify or not peer SSL certificate (PR/#7536)
* [Remote Server] Add possibility to use or not configured proxy (PR/#7536)
* [LDAP] default contactgroup ldap import (PR/#7220)
* [UI] Better menu delimitation (PR/#7257)
* [UI] Color menu level 2&3  (PR/#7295)

Bug fixes
---------

* [Backup] partial backup didn't backup the right partition for data_bin and logs (PR/#7242)
* [Broker] broker config generate external values (PR/#7401)
* [Broker] Default log path in configuration form (PR/#7367)
* [Export] Fix date format for CSV export (PR/#7533)
* [Graphs] No-unit series now trigger a second axis (Closes #7330 with #7341)
* [Graphs] "Split chart" mode do not show thresholds (Closes #7342,#7235 with #7343)
* [Install] Get the ip address of an existing connection to set the permission correctly (PR/#7347)
* [LDAP] Fix SQL error on LDAP authentication (Closes #7134 with PR/#7278)
* [LDAP] Optimize ldap sync at config generation (Closes #6949 with #7130)
* [LDAP] LDAP Groups ACLs are not working (Closes #7189 with #7308)
* [Monitoring] Macros not displayed in WUI for new services when you select your template (Closes #7121 with #7515, #7535)
* [Packaging] Install systemd .service files with 644 permissions
* [Packaging] fix installation of conf.pm and centreontrapd.pm
* [Systemd] use /etc/sysconfig/cent* files to get options (PR/#7380)
* [UI] Correct the displayed saved researched value in the select2 components (PR/#7525)
* [UI] Correct the redirection after submitting the monitoring form (PR/#7545)
* [UI] Filters persistence on monitoring and configuration (PR/#7327,#7355,#7348,#7369,#7345
* [UI] Filters and pagination MediaWiki (PR/#7397)
* [Widget] Widget parameters displayed in public views (PR/#7408)

Documentation
-------------

Security fixes
--------------

* Fix ACL calculation when interfering with the GET request (PR/#7517)
* Fix vulnerability on file loading #7227
* Remove obsolete rrdtool configuration and sources (PR/#7195)
* Fix SQL injection on Service grid by hostgroup page (PR/#7275)

====================
Centreon Web 18.10.4
====================

Enhancements
------------

* [API] API for commands arguments descriptions (PR/#7196)
* [API] Add showinstance CLAPI command to Host (PR/#7199)
* [API] Acknowledge resources using the API (Issue/#6068 - PR/#7187)
* [Centcore] Allow to set illegal characters for centcore (PR/#7206)
* [Installation] Update source installer regarding 18.10 version (PR/#7160)
* [UI] Improve host template selection by remplacing simple select with multi-select (PR/#7208)
* [UI] Indent third level menu (PR/#7251)

Bug Fixes
---------

* [UI] Fix issue with comments date in host and service detail pages (Issue/#7180 - PR/#7194)
* [UI] Fix issue with session expiration and avoid login "inception" (PR/#7202)
* [UI] Fix issue with event logs export CSV/XML (Issue/#6929 - PR/#7167)
* [UI] Fix search filter for recurrent downtimes (PR/#7201)

Documentation
-------------

* Improve prerequisities (PR/#7244)
* Improve poller configuration (PR/#7116)
* Enable services after remote server installation (PR/#7027)
* Update upgrade to Centreon 18.10 documentation section (PR/#6934)
* Describe directory of XML files for partitioning (PR/#7203)
* Correct documentation link (Issue/#6997 - PR/#7016)
* Add daemon-reload command added when installing DB on dedicated server (Issue/#7137 - PR/#7139)

Security
--------

* Fix security issue by removing dead code related to escalation (PR/#7200)
* Fix rce vulnerability when using command's testing feature (PR/#7245)
* Fix SQL injection for GET parameter (PR/#7229)
* Fix unauthorized file upload (PR/#7171)

====================
Centreon Web 18.10.3
====================

Enhancements
------------

* [Configuration] Avoid huge memory consumption when generating configuration (PR/#7072)
* [Remote Server] Add one-peer retention (Issues/#6910,#6978,#6987 - PR/#6959)
* [UI] Menus of banner can be opened/closed by clicking on icon (PR/#7127)
* [UI] Improve tooltip positionning in monitoring listing (PR/#7140)

Bug fixes
---------

* [Backup] Configuration backup correctly done using scp (PR/#7112)
* [Configuration] Unset service/contact relations if SETCONTACT clapi method used (PR/#7115)
* [Configuration] Include check_centreon_dummy during installation process (Issue/#7019)
* [UI] Date picker failed when no language selected (PR/#7046)
* [UI] Manage pagination in all custom select components (PR/#7102)
* [UI] Avoid duplicated en_US language selection in user settings (PR/#7094)
* [UI] Fix issue with shared views and multi widgets (PR/#7126)
* [UI] Display configuration has changed for all pollers (PR/#7107)
* [Remote Server] Replace special characters when setting up a remote server (Issue/#6979 - PR/#7133)
* [Remote Server] Prevent access to ressources configuration not defined on remote (PR/#7136)
* [Widget/host-monitoring] Issue with sorting options fixed (PR/#59)

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

====================
Centreon Web 18.10.1
====================

Enhancements
------------

* [Install] Optimize db partitioning during fresh install - PR #6937
* [Documentation] Improve FAQ chapter - PR #6900
* [Documentation] Improve prerequisites chapter - PR #6922
* [Documentation] Improve installation chapter - PR #6942 #6973
* [Documentation] Improve architecture chapter - PR #6966
* [Documentation] Add chapter to manage custom centreon uri - PR #6903
* [Documentation] Improve upgrade chapter - PR #6905 #6907 #6908
* [Documentation] Global documentation improvement - PR #6896 #6906 #6931 #6933

Bug fixes
---------

* [API] Fix PHP warning - PR #6917
* [API] Fix export of hostgroup services - PR #6948
* [Configuration] Fix host categories creation and update form - PR #6901
* [Configuration] Remove old wizard button - PR #6902
* [Configuration] Fix export of cbd watchdog logs path - PR #6919
* [Configuration/Widget] Fix widget upgrade if directory has changed - PR #6975
* [Remote Server] Fix incorrect variable name - PR #6915] 
* [Translation] Update strings - PR #6899
* [Global] Remove duplicate() method in children classes - PR #6918
* [Global] Update topology extract where clause from db - PR #6898

====================
Centreon Web 18.10.0
====================

New features
------------

Centreon Remote Server is a new building-block in the Centreon distributed monitoring architecture. It comes in addition to the existing Centreon Central Server and Centreon Pollers.

Centreon Remote Server allows remote IT operations team to benefit from the full Centreon user experience, albeit on a subset of Centreon Pollers. Monitoring configuration takes place on the Central Server and is automatically synchronized with all Remote Servers. Monitoring Operations (Acknowledge, Downtime...) may take place both on a Remote Server or the Central Server.

In case of network link failure between a Remote Server and the Central Server, data retention takes place and the two Servers are synchronized as soon as the connection is up again.

Centreon Remote Server is integrated in Centreon Core. It fully replaces the Poller Display module.

UI & UX Design
--------------

* Add new banner system and UX
* Add new menus system and UX
* Unique format of dates displayed according to user language settings
* Thanks to the community, Centreon is now available in Spanish and Portuguese (Portugal & Brazil)

Notice: The "Home > Poller Statistics" menu moved to "Administration > Server Status".
Moreover, this one is now named "Platform Status".

Enhancements
------------

* [Stats] Add a Centreon Experience Improvement Program
* [API] Possibility to cancel flexible RTDOWNTIME - #6062
* [Install] Add possibility to install/update all modules in one time
* [Configuration] Add a new wizard to configure in one time a complete poller or Remote Server
* [Configuration] Add possibility to install/update all modules in one time
* [Configuration] Add possibility to install/update all widgets in one time
* [LDAP] Manage multiple LDAP group with same dn - PR #6714
* [LDAP] If user account is disabled in AD, user will be still able to connect in Centreon - #6240
* [LDAP] Update LDAP Attributes on authentication - #3402
* [LDAP] Problem with LDAP contact groups with name members with accent - #5368
* [LDAP] Improve group synchronization - #6203 #6239 #6241
* [Packages] New centreon-database package, helpful for standalone Centreon databases;

Bug fixes
---------

* [Install] Fix several PHP notices
* [Backup] Fix PHP paths in backup script - PR #6787
* [Chart] Fix graph search with ACL in performances page - PR #6798
* [Configuration] Meta Service using quotes in output format string - PR #6216
* [Configuration] Fix duplicate advanced matching SNMP traps rules - PR #6738
* [Configuration] Avoid duplicate entry in ACL table after host creation - PR #6810
* [Configuration] Fix host categories form - PR #6785
* [Configuration] fix regexp for trap argument ending by backslash - PR #6699
* [Downtime] Add a downtime for user linked to ACL - PR #5988
* [Downtime] Fix recurrent downtime form (period loading) - PR #6645
* [Monitoring] Display cancel button in comments page using ACL rights - PR #6857
* [Monitoring] Display cancel button in downtimes page using ACL rights - PR #6856
* [Monitoring] Persist search filters - #5109 #6161
* [Monitoring] Persist selected results limit & pagination - #6325 #6161 #6367
* [Monitoring] Invalid accentuated chars transcription in timeperiod exception models - #6359
* [Monitoring] Add missing style for button in service acknowledge form  - PR #6805
* [Monitoring] Host number calculation with ACL is not correct in HG summary - PR #6855
* [Monitoring] Fix service by servicegroup page when using ACL #6863
* [Notification] Exclude services started by BA from BAM UI notification style - PR #6782

Security fixes
--------------

* [ACL] Fix XSS issue on the ACL list page - PR #6634
* [Administration] Fix XSS issue  - PR #6635
* [Administration] Fix XSS security - PR #6633
* [Configuration: Adding security filters on the host list page - PR #6625
* [Configuration] Fix XSS security issue on adding poller macros - PR #6626
* [Downtime/comments] Fix XSS issue for host, service & downtime comments - PR #6637
* [General] Create new escape method to fix XSS issue (commit 5820a04)
* [General] Fix XSS issue - PR #6636
* [Monitoring] Fix XSS security issue - PR #6632
* [SNNP trap] Fix SQL injection on editing trap SNMP - PR #6627
* [Virtual metric] Fix SQL injection - PR #6628
* [ACL access groups] Fix XSS vulnerability - PR #6710

Technical architecture changes
------------------------------

* Upgrade from PHP 5.x to PHP 7.x compatibility (7.1/7.2)
* Upgrade jQuery libraries
* Add ReactJS technology for new interfaces
* Prevent memory leaks - #4764
* Upgrade from DB.php connector to PDO

Known bugs or issues
--------------------

* Meta-services management with ACL (add/duplicate)
* Centreon AWIE issues when trying to export large configuration
* Got bogus version XX in httpd error logs #6851
