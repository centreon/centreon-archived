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
