###################
Centreon Web 2.8.30
###################

Documentation
=============

* Correct migration using nagios reader (PR/#7781)

Security
========

* Avoid SQL injections in multiple monitoring pages - CVE-2019-17647 (PR/#8029, PR/#8094)
* Contact list using escapeSecure method (PR/#7947)
* Control directory indexes with an htaccess (PR/#8115)
* Do not allow to get all services using downtime ajax file - CVE-2019-17643 (PR/#8022)
* Escape myAccount special characters - CVE-2019-16195 (PR/#7876)
* Escape persistent and reflected XSS in my account (PR/#7865)
* Escape script and input tags by default (PR/#7811)
* Fix default contact_autologin_key value
* Fix security on LDAP page - CVE-2019-15300 (PR/#8009)
* Hide password in command line (#7414, PR/#7883)
* RCE on mib import from manufacturer input - CVE-2019-15298 (PR/#8023)
* Remove command test execution - CVE-2019-16405 (PR/#7884)
* Sanitize host_id and service_id (PR/#7880)
* Session fixation using regenerate_session_id (PR/#7893)