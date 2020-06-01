#####################
Centreon Web 19.04.16
#####################

Bug fixes
---------

* [Configuration] Wrongly linked service template in service group (PR #8589)
* [Clapi] Import failure (PR #8724)
* [Clapi] Fix/Improve RTDOWNTIME (PR #8275)
* [Auth] Authentication type does not fallback from LDAP to local automatically (PR #8713)
* [Monitoring] Service groups not displayed when no services found into it (non-admin users) (PR #8529)

Security fixes
--------------

* [CentCore] Fix RCE
* [Web] DoS issue in include/eventLogs/xml/data.php
* [Web] RCE using command line path's argument (CVE-2020-12688)
