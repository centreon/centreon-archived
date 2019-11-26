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
