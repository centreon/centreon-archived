====================
Centreon Web 19.04.0
====================

New features
------------

* The extension management page has been unified. The installation, update and removal of modules and widgets is available via the "Administration> Extensions> Manager" menu. It is now possible to install all extensions at one time or to update all extensions in one click. Moreover a detail page provides access to the description of the extensions.
* Improved navigation within the menu. It can be used both open and closed to navigate within the Centreon web interface. Closed, only one click is required to access the desired page. Open, it is possible to navigate a menu by opening and closing the submenus or to access another menu in a click.

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
