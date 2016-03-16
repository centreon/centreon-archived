##############
Centreon 2.7.2
##############

Released February 24, 2016

The 2.7.2 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.2 follow:

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

- Fix eventlogs pages for performances and right for non admin users
- Fix Recurent Downtimes behaviour with timezones
- Fix some broken relations in web interface
- Fix Reporting pages for non admin users
- Fix some elements with the generation of the configuration
- Fix encoding problems 
- Fix filters in configuration pages
- Fix Poller duplication
- Fix various ACL problems
- Fix some SQL queries
- Fix export of Meta Services
- Improve ACL on Custom Views 

Known Bugs
==========

- Recurrent downtimes during for more than a day are not working
- It's impossible to remove relations between usergroup and custom views
- With the update some widgets have to be deleted and recreated
