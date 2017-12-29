##############
Centreon 2.7.4
##############

Released April 14,2016  

The 2.7.4 release for Centreon Web is now available for `download <https://download.centreon.com>`_. The full release notes for 2.7.4 follow.

******
Notice
******
If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.

**************************
Fix of an encoding problem
**************************

Following a change of encoding tables in centreon database which occurred in the 2.7.0 version, bad encoded characters appear in the Centreon web interface. Indeed, the change charset "latin1" to "utf8" was not followed by an update of the content of tables in the database.

To restore a valid encoding of special and accented characters, it is necessary to manually run the script provided by Centreon.

Warning
=======

This script should be run once and only once.

If an operator has modified/corrected special characters or accented since the 2.7.0 update, processing performed by the script will truncate the string to turn on the first special or accented character. It will then be necessary to change the impacted objects to manually update them. (The script can unfortunately provide the list of impacted objects.

All contents of table type "varchar", "char" or "text" will be updated

Prerequisites
=============

Don't forget to backup your database before doing any operations.

Installation
============

Download and install the script in "/usr/share/centreon/bin/" with the command:

wget http://resources.centreon.com/upgrade-2.6-to-2.7/migrate_utf8.php -O /usr/share/centreon/bin/migrate_utf8.php

Execution
=========

From a shell terminal, perform the script:

php /usr/share/centreon/bin/migrate_utf8.php

Validation
==========

Connect to your web interface and check that there are no more bad encoded characters on it.

*********
CHANGELOG
*********

Features and Bug Fixes
======================

- Fix: Contacts in contactgroups were exported with a wrong ID
- Fix: Error when saving "Administration > Parameters > Monitoring" page
- Fix: Zoom in Performance graph
- Fix: Select contactgroups / contacts in services & hosts configuration was not working
- Fix: Display only catagories and not severities on form
- Fix: Scroll bar in "Configuration - Hosts - Host Groups"
- Fix: Category Relation on host and host template form
- Fix: Order in More Actions Menu
- Fix: generateSqlLite not install with source
- Fix: SSO connection with LDAP user
- Enh: Add possibility to set local to "browser" when adding a contact by CLAPI
