####################
Centreon Web 18.10.5
####################

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

Technical
---------

Known issue
-----------

