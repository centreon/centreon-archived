##############
Centreon 2.7.5
##############

Released July 06,2016  

The 2.7.5 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.5 follow.

******
Notice
******
If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

*********
CHANGELOG
*********

Features and Bug Fixes
======================

- Fix: Flapping configuration was not exported to Centreon Engine configuration files
- Fix: Option "test the plugin" didn't working with special characters
- Fix: It was possible to select Meta Service or BA in performance page filters
- Fix: With non admin users, it was impossible to select services in Performances page
- Fix: Non admin users could not seen services in Reporting page
- Fix: Number of hosts in Hostgroups was not good for non admin users
- Fix: Max and Min was not correct for inverted curves
- Fix: It was impossible to create Virtual metrics with web UI in french language
- Fix: Exclude Desactivate poller in configuration generation page filter
- Enh: Add an error message when no pollers are selected in configuration genration page
