###################
Centreon Web 2.8.25
###################

Introduction to a new banner to preparate the next releases. This feature must be
enabled for each user. After the update, users will be asked to activate or not this
feature. New banner will appear after refresh of the page. A rollback is still possible 
through the "My account" menu.

Enhancements
============

* [UX] New banner in feature flipping mode - PR #6294
* [API] Submit result for passif resources - PR #6209
* [API] Export is too long when lot of parentship - PR #6372

Bug Fixes
=========

* [API] Correct real time service filters - #6080 PR #6363
* [API] Restore broker configuration with clapi generate too much output and input - #5011 PR #6220
* [API] Partial / Filtered export does not work as expected for HC, SC, CG - #5294 PR #6355
* [API] Export uses resource macro name instead of id for setparam - #6221 PR #6222
* [API] HTML Entities cause REST API Serialization Errors - #6110 PR #6234
* [API] Fix acl group setcontact export - PR #6224
* [API] Avoid to order parentship several times - PR #6373
* [Configuration] View contact notification  service missing - #6073 PR #6340
* [Downtimes] Prevent permission denied centcore cmd for downtimemanager - PR #6289
* [LDAP] Remove contact password if ldap password storage is disabled - #5627 PR #6347
* [Monitoring] Sort by service name after status in service grid - PR #6290
* [Reporting] Avoid bug on partitioned tables - PR #6382

Security
========

* Fix SQL injection from metrics RPN's field - PR #6356

Others
======

* Avoid PHP notice Undefined index: centreon in notifications.php - PR #6266
* Delete "Ping" and "Tracert" entries (no more used) - PR #6277
* Fix typo in FR documentation - PR #6375
* Fix "how to write a stream connector" chapter - PR #6296 #6295
* Add some missing developers in Centreon About - PR #6410 #6253
* Several fixes and improvements in documentation
