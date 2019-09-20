###################
Centreon Web 2.8.29
###################

Bug Fixes
=========

* [ACL] Add ACL to select meta-services for service performance (#6534, PR/#7736)
* [Configuration] Add possibility to save service_interleave_factor in Centreon Engine form (PR/#7591)
* [Widget] Fix preferences #7641 (#6988, PR/#7641)

Security
========

* [UI] Add escapeshellarg to nagios_bin binary passed to shell_exec (PR/#7694 closes CVE-2019-13024)

Others
======

* [SQL] Use pearDb (PR/#7668)
* [Generation] Fix requirement (PR/#7703)
