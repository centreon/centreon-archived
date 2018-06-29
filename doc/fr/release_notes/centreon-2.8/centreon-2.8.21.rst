###################
Centreon Web 2.8.21
###################

Enhancements
============

* [Documentation] Add chapter about how to write a stream connector - PR #6189
* [API] Separate REST API configuration and REST API realtime access - PR #6188

Bug Fixes
=========

* [ACL] Manage filters (poller, host, service) on servicegroup - PR #6163
* [Configuration] Fix output stream connector name for fresh install - PR #6159 #6182
* [Configuration] No "Conf changed" flag set to "yes" when deploying services to selected hosts - #6160 PR #6191

Other
=====

* Fix php warning in realtime host API - PR #6174