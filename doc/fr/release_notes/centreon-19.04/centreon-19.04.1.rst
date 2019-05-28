====================
Centreon Web 19.04.1
====================

Enhancements
------------

* [Graphs] Add more curves template for fresh installations (#5819, #7530) + (18.10.5)

Bug fixes
---------

* [ACL] Fix issue with monitoring pages (PR/#7554)
* [Administration] Correct the redirection after submitting the monitoring form (PR/#7545) (+18.10.5)
* [Packaging] Install systemd .service files with 644 permissions (+18.10.5)
* [Web] Fix date format for CSV export (PR/#7533) (+18.10.5)
* [Web] Correct the displayed saved researched value in the select2 components (PR/#7525) (+ 18.10.5)
* [Packaging] fix installation of conf.pm and centreontrapd.pm (+ 18.10.5)
* [Monitoring] Fix hard_state_duration column (#7506)
* [Graphs] No-unit series now trigger a second axis (Closes #7330 with #7341) (+ 18.10.5)
* [Graphs] "Split chart" mode do not show thresholds (Closes #7342,#7235 with #7343) (+ 18.10.5)
* [Monitoring] Macros not displayed in WUI for new services when you select your template (Fixes #7121 with #7515, #7535) (+ 18.10.5)
* [Monitoring] Filter issues on host monitoring page fixed (#7511)

Documentation
-------------

Security fixes
--------------

* [ACL] Fix ACL calculation when interfering with the GET request (PR/#7517) (+18.10.5 + 2.8.28 = #7518)
Technical
---------

Known issue
-----------
