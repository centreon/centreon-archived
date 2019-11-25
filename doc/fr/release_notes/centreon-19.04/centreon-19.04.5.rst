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