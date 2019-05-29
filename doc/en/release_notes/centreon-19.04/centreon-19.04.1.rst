====================
Centreon Web 19.04.1
====================

Enhancements
------------

* [Graphs] Add more curves template for fresh installations (#5819, #7530)
* [Remote Server] Add possibility to use HTTPS or HTTP for communication and to define TCP port (PR/#7536)
* [Remote Server] Add possibility to verify or not peer SSL certificate (PR/#7536)
* [Remote Server] Add possibility to use or not configured proxy (PR/#7536)

Bug fixes
---------

* [ACL] Fix issue with monitoring pages (PR/#7554)
* [Administration] Correct the redirection after submitting the monitoring form (PR/#7545)
* [Packaging] Install systemd .service files with 644 permissions
* [Web] Fix date format for CSV export (PR/#7533)
* [Web] Correct the displayed saved researched value in the select2 components (PR/#7525)
* [Packaging] fix installation of conf.pm and centreontrapd.pm
* [Monitoring] Fix hard_state_duration column (#7506)
* [Graphs] No-unit series now trigger a second axis (Closes #7330 with #7341)
* [Graphs] "Split chart" mode do not show thresholds (Closes #7342,#7235 with #7343)
* [Monitoring] Macros not displayed in WUI for new services when you select your template (Fixes #7121 with #7515, #7535)
* [Monitoring] Filter issues on host monitoring page fixed (#7511)

Documentation
-------------

Security fixes
--------------

* [ACL] Fix ACL calculation when interfering with the GET request (PR/#7517)

Known issue
-----------
