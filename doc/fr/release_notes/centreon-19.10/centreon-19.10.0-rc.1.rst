#########################
Centreon Web 19.10.0-rc.1
#########################

Enhancements
============

* [authentication] Add okta LDAP template (PR/#7825)
* [Configuration] Add display locked checkbox for objects listing (#7444)
* [Install] Add possibility to install widget during last step (PR/#7890)
* [Remote Server] Poller attached to multiple remote servers (PR/#7849)
* [UI] Do not display round values in detailed top counter (PR/#7547)

Documentation
=============

* Doc correct migration using nagios reader (PR/#7781)
* Update mysql prerequisites for master (PR/#7904)

Bug Fixes
=========

* [Centcore] Create centcore file by action (PR/#6985)
* [Configuration] Correct issue in wizard with PR #7849 (commit 2b8a728478)
* [Configuration] Fix style of broker modules options checkboxes (PR/#7899)
* [Install] Change the bash interpreter for the native sh (commit (PR/#7911))
* [Install] Update wording about cache in install/upgrade process (PR/#7895)
* [Install] Fix syntax error in step5 of upgrade process (PR/#7900)
* [Monitoring] Save properly monitoring service status filter (PR/#7908)
* [Remote-Server] Allow remote server config to be loaded with mysql strict mode enabled (PR/#7887)
* [Remote Server] Change grant option for remote server database centreon user (PR/#7888)
* [Remote Server] set remote_id/remote_server_centcore_ssh_proxy to NULL/0 (PR/#7878)
* [UI] Fix style of frozen checkboxes (PR/#7882)

Security fixes
==============

* Hide password in command line for status details page (#7414, PR/#7859)
* Escape script and input tags by default (PR/#7811)
* Add php mandatory params info in source installation (PR/#7897)
* Escape persistent and reflected XSS in my account (PR/#7877)
* Remove xss injection of service output in host form (PR/#7865)
* Sanitize host_id and service_id in makeXMLForOneService.php (PR/#7862)
* Session fixation using regenerate_session_id (PR/#7892)
* Remove command test execution - CVE 2019-16405 (PR/#7864)
* the ini_set session duration param has been moved in php.ini (PR/7896)

Technical
=========

* [Core] Improve the centreon user service definition in ServiceProvider (PR/#7891)
* [Test] Fix acceptance test of locked elements (PR/#7910)

Known issue
-----------

* [logs] Fix the limitation of max value for the primary key of the centreon_storage.logs table (:ref:`update_centreon_storage_logs`)
