###################
Centreon Web 2.8.31
###################

Documentation
=============

* Clearly indicate that dependencies between pollers are not possible

Bugfix
======

* [Custom View] Define new custom view error file template (PR/#8141)
* [Custom View] fix display for user with no widget preferences (PR #8158 fixes #7875)

Security
========

* Host macro list without authentication - CVE-2019-17644 (PR/#8032)
* Service macro list without authentication - CVE-2019-17645 (PR/#8036)
* Service Discovery cron should be ran by centreon user (PR #8062 fixes #7921)
