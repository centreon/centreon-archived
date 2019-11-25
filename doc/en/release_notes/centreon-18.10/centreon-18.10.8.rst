====================
Centreon Web 18.10.8
====================

Enhancements
------------

* [Configuration] Rename contact template titles properly (PR/#7929)

Bug Fixes
---------

* [API] Add macro password option for service template using CLAPI (PR/#8012)
* [Charts] Match metric name with metric value (#5959, #7477, PR/#7764)
* [Configuration] Add missing column for engine configuration (PR/#8125)
* [Configuration] Fix stream connector update (PR/#7813)
* [Configuration] Remove unused radio button in meta service configuration (PR/#7992)
* [Downtimes] Apply downtime on resources linked to a poller (PR/#7955)
* [Export] Better handling PNG export failure (PR/#7823)
* [Monitoring] Fix double host name display in host detail (PR/#7737)
* [monitoring] fix recurrent downtimes filter (PR/#7989, #7987)
* [UI] Redirect to login page when user is unauthorized (PR/#7687)
* [UI] Do not display autologin shortcut when disabled (PR/#7340)

Documentation
-------------

* Correct migration using nagios reader (PR/#7781)
* Improve prerequisites (commit 7955e7a7f6)
* Remove install poller via VM (commit 472623f124)
* Update mysql prerequisites (PR/#7903)
* Update FAQ to install RRDCacheD on el7 (PR/#8052)

Security Fixes
--------------

* Avoid SQL injections in multiple monitoring pages - CVE-2019-17647 (PR/#8063, PR/#8094)
* Add php mandatory param info for source installation (PR/#7898)
* Add rule for max session duration (PR/#7919)
* Cross-site scripting (reflected) - Dont' return js (PR/#8095)
* Contact list using escapeSecure method (PR/#7947)
* Do not allow to get all services using downtime ajax file - CVE-2019-17643 (PR/#8022)
* Escape persistent and reflected XSS in my account - CVE-2019-16195 (PR/#7877)
* Escape script and input tags by default (PR/#7811)
* Fix default contact_autologin_key value
* Filter access to api using external entry point - CVE-2019-17646 (PR/#8021)
* Fix security on LDAP page - CVE-2019-15300 - (PR/#8008)
* Hide password in command line (#7414, PR/#7859)
* RCE on mib import from manufacturer input - CVE-2019-15298 (PR/#8023)
* Remove command test execution - CVE-2019-16405 (PR/#7864)
* Remove xss injection of service output in host form (PR/#7865)
* Sanitize host_id and service_id (PR/#7862)
* Session fixation using regenerate_session_id (PR/#7892)
* The ini_set session duration param has been moved in php.ini (PR/#7896)