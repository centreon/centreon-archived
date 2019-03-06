####################
Centreon Web 18.10.4
####################

Enhancements
============

* [API] API for commands arguments descriptions (PR/#7196)
* [API] Add showinstance CLAPI command to Host (PR/#7199)
* [API] Acknowledge resources using the API (Issue/#6068 - PR/#7187)
* [Centcore] Allow to set illegal characters for centcore (PR/#7206)
* [Installation] Update source installer regarding 18.10 version (PR/#7160)
* [UI] Improve host template selection by remplacing simple select with multi-select (PR/#7208)
* [UI] Indent third level menu (PR/#7251)

Bug Fixes
=========

* [UI] Fix issue with comments date in host and service detail pages (Issue/#7180 - PR/#7194)
* [UI] Fix issue with session expiration and avoid login "inception" (PR/#7202)
* [UI] Fix issue with event logs export CSV/XML (Issue/#6929 - PR/#7167)
* [UI] Fix search filter for recurrent downtimes (PR/#7201)

Documentation
=============

* Improve prerequisities (PR/#7244)
* Improve poller configuration (PR/#7116)
* Enable services after remote server installation (PR/#7027)
* Update upgrade to Centreon 18.10 documentation section (PR/#6934)
* Describe directory of XML files for partitioning (PR/#7203)
* Correct documentation link (Issue/#6997 - PR/#7016)
* Add daemon-reload command added when installing DB on dedicated server (Issue/#7137 - PR/#7139)

Security
========

* Fix security issue by removing dead code related to escalation (PR#7200)
* Fix rce vulnerability when using command's testing feature (PR/#7245)
* Fix SQL injection for GET parameter (PR/#7229)
* Fix unauthorized file upload (PR/#7171)
