==============
Centreon 2.6.2
==============


******
Notice
******
If you are upgrading from a version prior to 2.6.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.


*********
CHANGELOG
*********

Features
========

- Modules can extend actions after restart/reload pollers

Security fixes
==============

- #2979 : Secure the type of media which file can be uploaded (ZSL-2015-5264)
- Fix some SQL injections (ZSL-2015-5265)

Bug fixes
=========

- #3559 : Fix query with MariaDB / MySQL configure in STRICT_TRANS_TABLES
- #3554 : Can send acknowledgement with multiline from monitoring page
- #3397 : Fix display graph with unicode characters in metric name
- #2362 : Correct value when use index_data inserted by Centreon Broker in configuration
- #1195 : Display correct number of pollers in status bar
- #196 : Display all columns when filter is applied on Monitoring services unhandled view
