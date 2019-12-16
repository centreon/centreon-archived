===================
Centreon Web 2.8.31
===================

Documentation
-------------

* Clearly indicate that dependencies between pollers are not possible

Bugfix
------

* Define new custom view error file template (PR/#8141)

Security
--------

* Host macro list without authentication - CVE-2019-17644 (PR/#8032)
* Service macro list without authentication - CVE-2019-17645 (PR/#80

===================
Centreon Web 2.8.30
===================

Bug Fixes
=========

* Macro passwords are not hidden

Security
========

* No check for authentication
* Cross-site request forgery
* Session fixation
* SQL injections
* RCE flaws
* Authentication flaw
* XSS
* SQL injection vulnerability in multiple pages
* Discovery of directories

===================
Centreon Web 2.8.29
===================

Bug Fixes
---------

* [ACL] Add ACL to select meta-services for service performance (#6534, PR/#7736)
* [Configuration] Add possibility to save service_interleave_factor in Centreon Engine form (PR/#7591)
* [Widget] Fix preferences #7641 (#6988, PR/#7641)

Security
--------

* [UI] Add escapeshellarg to nagios_bin binary passed to shell_exec (PR/#7694 closes CVE-2019-13024)

Others
------

* [SQL] Use pearDb (PR/#7668)
* [Generation] Fix requirement (PR/#7703)

===================
Centreon Web 2.8.28
===================

Enhancements
------------

Bug Fixes
---------

* [Actions] Downtime and acknowledgement not working (PR/#7233)

Security
--------

* Adding security filters on the host list page (PR/#6625)
* Fix ACL calculation when interfering with the GET request (PR/#7518)
* Fix vulnerability for file loading (PR/#7226)
* Type juggling can lead to authentication bypass (PR/#7084)
* Fix SQL injection on Service grid by hostgroup page (PR/#7275)

===================
Centreon Web 2.8.27
===================

Enhancements
------------

* [ACL] Improve ACL access on downtime and hostgroup form - PR #6962
* [API] API for commands arguments descriptions - PR #7196
* [API] Add showinstance CLAPI command to Host #7199
* [LDAP] manage multiple ldap group with same dn - PR #6714

Bug Fixes
---------

* [ACL] Host calculation with ACL is not correct - PR #6436
* [API] Broker configuration accept accept id 0
* [API] Unset service/contact relations if set option - PR #7115
* [API] Use "Reach API" to validate access to API - PR #7117
* [Authentication] add sync with ldap groups upon login - PR #7057
* [Backup] Fix scp export of configuration files backup - PR #7112
* [Chart] fix graph export when a curve is only displayed in legend - PR #7009
* [Centcore] Allow to set illegal characters for centcore (#7206)
* [Configuration] fix export of cbd watchdog logs path - #6794, PR #6919
* [Configuration] fix broken hostgroup form and massive change on host - PR #7105
* [Downtimes] Pagination & filters corrections in recurrent Downtimes form - #6501, #6504, #6506, PR #6509
* [Global] fix pagination when new header is enabled - PR #6687
* [LDAP] fix ldap import due to var typo
* [LDAP] Fix LDAP search when the 'user group attribute' field of ldap configuration is empty - PR #7057
* [Monitoring] Fix columns on the list page - PR #6984
* [UI] Fix a Javascript bug when the new header is selected - PR #6590
* [UI] backport memory leak - PR #7003
* [Visual notification] exclude services started by BA from BAM UI notification style - PR #6782

Documentation
-------------

* Correct menu access to add/edit recurrent downtime - #6698
* Correct the upgrape chapter - #6916
* Improve prerequisite MySQL version to correct bug on partitioned tables - PR #6974
* Quick Start improvements 

Security
--------

* Add SQL and XSS protection of Administration Logs page - PR #7038
* Avoid password macro to appear in cleartext - PR #7020
* Clean dead code about escalation - PR #7200
* Fix XSS vulnerability on hosts and services comments - PR #6953
* Fix SQL injection and duplicate action on the host list page - PR #6961
* Fix the XSS vulnerability on poller resource - PR #6982
* Fix XSS vulnerability in the ACL group search field - PR #7032
* Fix SQL injection for virtual metrics - PR #7061
* Fix SQL injection and duplicate feature - PR #7069
* Fix XSS vulnerability in media - PR 7089
* Protect hostname resolver from XSS - PR #7043
* Rce vulnerability fixed when using command's testing feature (#7245)

Others
------

* Change copyright calculation code and replace mailto link by a direct link to our website
* Fix compatibility with PHP 5.3

===================
Centreon Web 2.8.26
===================

Enhancements
------------

* [Authentication] Set LDAP version 3 as default in LDAP configuration form - PR #6452
* [Notification] Standardize mail notifications PR #6570 (ex PR #6530)

Bug Fixes
---------

* [ACL] Do not get severity of parents if present on actual object - PR #6484
* [ACL] ACLs calculation is too slow with lot of acl resources - #6461, PR #6495
* [Chart] Fix metrics error message - PR #6474
* [Configuration] Trap generation reindexing pollers id - #6205 PR #6416
* [Configuration] Fix disable option in Centreon Engine configuration - #6518, PR 6520
* [Monitoring] In Status Details pages, display true contacts/contactgroups inheritance relation - #6177, #6176, #6467, PR #6513
* [Monitoring] Add topology url option when loading default page # 6528, PR #6551
* [Monitoring] Sort hosts by name ASC in serviceGridByHGXML - #6529, PR #6547

Documentation
-------------

* Fix doc architecture - PR #6430
* Fix images for db replication - PR #6432
* Correction of typography - #6447, PR #6453
* Improve Centreon IMP chapter - PR #6485
* Correct link references in IMP chapter - PR #6541
* Increase Centreon web version number for PDF generation - PR #6540
* Correct build errors - PR #6567
* Global review documentation content - #6560, PR #6510

Others
------

* Remove dead code from escalation page - PR #6393
* Remove old and unused file in order to avoid problems with ACL - PR #6210

Notice
------

The Standardize mail notifications enhancement is only available for new instalaltion (PR #6570)

===================
Centreon Web 2.8.25
===================

Introduction to a new banner to prepare the next releases. This feature must be
enabled for each user. After the update, users will be asked to activate or not this
feature. New banner will appear after refresh of the page. A rollback is still possible
through the "My account" menu.

Enhancements
------------

* [UX] New banner in feature flipping mode - PR #6294
* [API] Submit result for passif resources - PR #6209
* [API] Export is too long when lot of parentship - PR #6372

Bug Fixes
---------

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
--------

* Fix SQL injection from metrics RPN's field - PR #6356

Others
------

* Avoid PHP notice Undefined index: centreon in notifications.php - PR #6266
* Delete "Ping" and "Tracert" entries (no more used) - PR #6277
* Fix typo in FR documentation - PR #6375
* Fix "how to write a stream connector" chapter - PR #6296 #6295
* Add some missing developers in Centreon About - PR #6410 #6253
* Several fixes and improvements in documentation

===================
Centreon Web 2.8.24
===================

Bug Fixes
---------

* Remove duplicate entries in centreon_acl table - PR #6366

Security
--------

* Fix execution command by rrdtool command line - PR #6263
* Fix XSS on command form - PR #6260
* Fix XSS security on menu username - PR #6259
* Fix SQL injection on graphs - PR #6251
* Fix SQL Injection in administration logs - PR #6255
* Fix SQL injection in dashboard - PR #6250
* Fix SQL injection in Curve template - PR #6256
* Fix SQL Injection in Virtual Metrics - PR #6257

===================
Centreon Web 2.8.23
===================

Enhancements
------------

* [Documentation] Correct typo - PR #6202
* [Documentation] Update icon to add metrics to a meta service - PR #6167
* [Documentation] Correct typo in documentation about stream connector howto #6261

Bug Fixes
---------

* [ACL] fix select all checkbox in acl actions form - PR #6193
* [Administration] fix purge on pmax partition - PR #6232
* [Downtimes] fix recurrent downtimes on HG when no SG exist - PR #6201

Security
--------

* Update jquery ui libs +fix compat - PR #6181

Others
------

* fix(centAcl.php): Dead code removed - PR #6262
* fix(lib): allow chaining on jquery pagination plugin - PR #6219
* fix(jQuery): fix broken input in reporting_dashboard - PR #6254
* fix(style): fix style in widget preferences popin - PR #6197
* fix(style): fix padding of buttons in custom views page - PR #6198
* fix(front): retrieve jquery toggle function (renamed to toggleClick) - PR #6217
* fix(front): fix acl actions checkboxes (check all / uncheck all) - PR #6309

===================
Centreon Web 2.8.22
===================

Enhancements
------------

Bug Fixes
---------

* [CLAPI] Fix host services deployment - PR #6212

===================
Centreon Web 2.8.21
===================

Enhancements
------------

* [Documentation] Add chapter about how to write a stream connector - PR #6189
* [API] Separate REST API configuration and REST API realtime access - PR #6188

Bug Fixes
---------

* [ACL] Manage filters (poller, host, service) on servicegroup - PR #6163
* [Configuration] Fix output stream connector name for fresh install - PR #6159 #6182
* [Configuration] No "Conf changed" flag set to "yes" when deploying services to selected hosts - #6160 PR #6191

Other
-----

* Fix php warning in realtime host API - PR #6174

===================
Centreon Web 2.8.20
===================

Enhancements
------------

* [API] Add default poller - PR #6098
* [API] Link host with default poller if unknown poller - PR #6099
* [ACL] Improve performance - #6056 PR #6107
* [Documentation] Improve Centreon CLAPI usage - PR #6090 #6091
* [Documentation] Improve documentation to add a new poller - #6075 PR  #6086
* [Documentation] Add notice for 64 bits support only - PR #6101
* [Monitoring] Display links in output and comments  - #5943 PR #6113

Bug Fixes
---------

* [ACL] Allow nested groups filter in ldap configuration - #6127 PR #6128
* [API] Export specific service, add host before service in CLAPI - PR #6100
* [API] CLAPI add resource export filter - PR #6125
* [API] CLAPI Export contact with contact group - PR #6131
* [API] CLAPI Export service categories - PR #6134
* [Configuration] SNMP trap poller generation uses ACL - #6043 PR #6069
* [Custom Views] Fix share custom view - PR #6109
* [Poller Stats] Poller Statistics Graphs are displayed in first column only - #6003 PR #6122

Others
------

* Update copyright date on the login page - PR #6076
* Remove multiple debug in Centreon - PR #6138

===================
Centreon Web 2.8.19
===================

Enhancements
------------

* [API] Return error when filtered object does not exist - PR #6074 
* [API] Add clapi set option - PR #6065
* [UX] Add new loading css - PR #6066 #6072

Bug Fixes
---------

* [API] Fix clapi export with hosts parent relations - #6061
* [API] Uninitialized array causing php warning - PR #6046 #6097
* [Monitoring] Top counter very slow since upgrade from 2.8.17 to 2.8.18 - #6085 PR #6093

===================
Centreon Web 2.8.18
===================

Enhancements
------------

* [Administration] Add more actions and logging for ACL management  - PR #5841
* [API] Validate input parameters - PR #5958
* [API] Check illegal char in add function for CLAPI - PR #5948
* [API] Improve error message - PR #5972
* [API] Get multiple parameters for host - PR #5946
* [Configuration] Add form to configure Centreon Broker generic stream connectors - PR #6024 #6053 #6052 #6042 (beta)
* [Documentation] Add new chapter for Centreon ISO el7 installation - PR #6019
* [Documentation] Describe get parameters for hosts #5783 - PR #5924 
* [Knowledge-Base] Add option to disable SSL certificate - PR #6027

Bug Fixes
---------

* [Administration] Define default value for Broker - #6029 PR #6033
* [Configuration] Change low limit of EventMaxQueueSize for Centreon Broker configuration - PR #6013
* [Configuration] Avoid php notice when poller has no timezone - PR #6031
* [Install] Compatibility with PHP version 5.3 - PR #5976
* [Meta-service] Do not duplicate them on update - PR #5982
* [Meta-service] Possibility for user with ACL to display chart - PR #5952
* [Monitoring] Top Counter with ACL really slow - #5974 PR #5992
* [Monitoring] Centreon UI freezes when access to "View contact Notification" - #5760 PR #5954
* [Monitoring] Replace dot character in command line for better display - PR #5945
* [Monitoring] Fix add downtime on hostgroup or poller with ACL - PR #6023 

===================
Centreon Web 2.8.17
===================

Enhancements
------------

* [API] Add Host getparam PR #5783
* [API] Delete/Cancel Real Time Downtime #5879 PR #5894
* [API] Display future downtime PR #5903
* [Documentation] Update lifecycle in documentation PR #5901
* [Documentation] Remove obsolete paragraph PR #5898

Bug Fixes
---------

* [ACL] Undefined variable host id PR #5891
* [ACL] Use correct id for acl host relation PR #5896
* [Chart] Graphs in IE stretched #5081
* [Configuration] Fix macro password visibility PR #5873
* [Configuration] Host search not saved when activate/deactivate a host #5711 PR #5827
* [Documentation] Correct API documentation for host/service relation #5854
* [Documentation] Improve documentation install using ISO #5772 PR #5851 
* [Install] Script install.sh - Could not create user #5785 PR #5890
* [Knowledge Base] Correct typo of error message PR #5917
* [Monitoring] fix macro password with arguments in object details page PR #5928 #5881

Security
--------

* Prepare query and execute it #5904
* Improve list of objects for Select2 #5918
* Update SQL query to prevent SQL injection in setRotate form #5915

===================
Centreon Web 2.8.16
===================

Enhancements
------------

* [Administration] Improve 'Server Status' page PR #5820
* [API] Add exceptions for realtime PR #5735  #5795
* [Configuration] Broker remove non existing protocol #5830 PR #5832
* [Configuration] Check illegal characters one time only PR #5831
* [Documentation] Wrong translation in documentation #5858 PR #5862
* [Documentation] Improve installation documentation #5825 PR #5844
* [Documentation] Improve Time Period documentation #5828 #5637 PR #5845 #5843
* [Documentation] Improve API realtime downtimes examples

Bugfix
------

* [Install] Properly place update to 2.8 from 2.7. #5809
* [ACL] centAcl cron LDAP sync removes all ContactGroups on unexpected error  #5547
* [API] Parent/Child relation are not exported with CLAPI #5605 PR #5857
* [API] Authorize id 0 for object PR #5812
* [Chart] Add legend name when defined PR #5817
* [Configuration] Improve host/service macro visibility
* [Configuration] add massive change contact/cg update mode for host form #5878
* [Knowledge Base] Search function non functional for templates of services #5762 PR #5829
* [Knowledge Base] Increase page limit for mediawiki migration PR #5798
* [Monitoring] Custom MACRO not interpreted in URL #5846 PR #5850
* [Monitoring] Display 0 in top counter if SQL result is empty #5758 PR #5826
* [Security] Some field was not encoded PR #5847

===================
Centreon Web 2.8.15
===================

Important notice
----------------

This version include a fix for the calculation of downtimes with daylight saving 
time (DST). The downtime end will be calculate with the new hour.

For example, if you put a downtime from 1 AM to 5 AM, the duration of the 
downtime will be 5 hours if during the DST you get 1 hour more (3 AM come back 
to 2 AM).

Enhancements
------------

* [Documentation] Improve api documentation (url) #5792
* [Downtimes] Manage downtimes with dst (recurrent and realtime) #5780

Bugfix
------

* [Install] Fix foreign key upgrade of traps_group table PR #5752
* [CLAPI] Fix duplicate ldap serverPR #5769
* [CLAPI] Fix duplicate htpl in stpl #5774
* [CLAPI] Fix duplicate on stpl #5775
* [Chart] Add unit on y axis
* [Chart] Fix extra legend on period change
* [Chart] Fix export with empty metric
* [Configuration] Add obsess_over_hosts parameter in main centengine configuration PR #5746
* [Monitoring] Ranking of ascending / descending guests NOK #5695 PR #5744
* [Monitoring] fix variable name in centreontrapd.pm

===================
Centreon Web 2.8.14
===================

Enhancements
------------

* [API] Update CLAPI commands to show resources of a downtime PR #5705
* [API] Add possibility to grant access to children menu (or not) PR #5694
* [API] Add possibility to add and get list of on-demand downtime #5192 #5682 PR #5623 - beta
* [API] Add possibility to get realtime hosts status #5682 - beta
* [API] Add possibility to get realtime services status #5682 - beta
* [Documentation] Activate services at system startup PR #5698
* [Administration] Add possibility to test proxy configuration #5561 PR #5722

Bugfix
------

* [API] Fix list of hosts with gethosts method of Instance object #5300 PR #5603
* [Install]  Add unique key on comments table PR #5665
* [Custom Views] Sharing View problem to select multiple users #5029
* [Configuration] Multiple 'update mode' fields in massive changes #5266 PR #5636
* [configuration] Massive Change on Hosts activate Stalking Option Up #4946
* [Reporting] Reporting Dashboard messed up #5491 #5520
* [Monitoring] No inheritance in query of notified contacts #4981
* [Monitoring] Top counter display too much resources with ACL #5713 PR #5703

===================
Centreon Web 2.8.13
===================

Enhancements
------------

* [Doc] Improve centreon documentation #5611 PR #5612
* [Doc] clarify documentation of centreon clapi authentication #5625 PR #5628
* [Performance] Correct svc top counter with meta and merge SQL requests PR #5616

Bugfix
------

* [Top Counter] Metaservices not counted properly in statuses filter #5458 PR #5616
* [Configuration] Properly export interval length in storage endpoints #5461
* [Documentation] Time Range exceptions invalid format #5578
* [Chart] No graphics with backslash #5554 #5342 PR #5565
* [LDAP] Problem with LDAP autoimport and groupmapping with comma in CN #4867
* [Monitoring] No inheritance in query of notified contacts (Monitoring view) #4981

===================
Centreon Web 2.8.12
===================

Enhancements
------------

* [API] Update documentation to remove non available functions
* [API] Export/Import LDAP configuration
* [API] Export/Import ACL Groups
* [API] Export/Import ACL Menus
* [API] Export/Import ACL Actions
* [API] Export/Import ACL Resources
* [API] Replacing contact_name by contact_alias PR #5546
* [Configuration] Input text not aligned in Curves page #5534 PR #5553
* [Monitoring] Monitoring Services by Hostgroup : improvement order suggestion #5402 PR #5552
* [Monitoring] Increase perfs on EventLogs for non admin user PR #5480
* [Knowledge Base] Display API errors #5502
* [Knowledge Base] Refresh page after deletion #5503
* [Backup] Get correct datadir with CentOS7/MariaDB PR #5484

Bugfix
------

* [ACL] Bug on Access Groups #5189
* [ACL] The ACL of a contact and of a contact group is deleted during duplication #5497
* [API] CLAPI Import not working #5541
* [API] CLAPI export with select filter give PHP Warning and non result #5548
* [API] Missing functions setseverity and unsetseverity for services by hostgroup #5262
* [API] Problem with icon_image and map_icon_image of Hostgroup #5292
* [API] Missing function setservice for Service categories #5304
* [API] Problem with setting gmt in API #5291
* [API] Contact group additive inheritance isn't implemented #5311
* [API] Contact additive inheritance isn't implemented #5310
* [API] Problem with delmacro for services by hostgroup #5309
* [API] Several bugs on HG / CG when export is filtered #5297 PR #5297
* [Monitoring] Sorting by duration and Maximum page size change #5287 #5410 PR #5517
* [Configuration] Dependent host deleted during a service dependency duplication #5531
* [Configuration] All pollers had "config changed" #5549
* [Configuration] Unable to change the severity of an host template #5472
* [Configuration] Unable to change the severity of a service template #5559
* [Configuration] Meta service - unable to change the geo_coordinates #5493 PR #5505
* [Configuration] Meta service - unable to add more than one contact #5506 PR #5507
* [Configuration] Meta service - Implied contact is deleted during duplication #5495 PR #5508
* [Configuration] Problem with escalation's name during a duplication #5512 PR #5513
* [Configuration] Duplicate severity should remove link to objects #5478 PR #5509
* [Configuration] Fix search in trap select2
* [Configuration] Fix search in service template select2

===================
Centreon Web 2.8.11
===================

Enhancements
------------

* Fix typos in Enabled/Disabled filters PR #5251
* Do not list meta services in list of service to add to a SNMP trap #5418 PR #5419

Bugfix
------

* Knowledgebase - Delete wiki page not functional #5059
* Massive Change don't modify the Recovery notification delay of a host #5451
* Impossible to acknowledge several object from custom views #5420
* Load custom views - fixed database entry duplication PR #5260
* Adding SNMP traps definition : values set to fields in Relations tab are not saved #5406 PR #5415 PR #5417
* SNMP Trap, not all parameters are saved on creation #5361 PR #5415 PR #5417
* Page "Services by Servicegroup > Display > Summary" not working #5399 PR #5416
* [CLAPI] Duplicate CMD in export #5455
* [CLAPI] Fatal error with PDOException #5453 PR #5462

===================
Centreon Web 2.8.10
===================

Enhancements
------------

* Proposal break Ajax #5256
* Do not export empty Centreon Broker parameters with API #5284
* Remove duplicate $_GET["autologin"] in test #5344
* Documentation improvement #5063
* Update engine reserved macros ($HOSTID$, $SERVICEID$, $HOSTTIMEZONE$) #5246
* Config generation is too long #5388
* Rename Centreon Broker Daemon option #5276

Bugfix
------

* Failure with special character in password for mysqldump #5173
* Unable to select all services in escalation form #5326 #PR5325
* Contacts/contactgroups inheritance #5396 PR #5400
* Check if wiki is configured and extend error message #5278 PR #5269
* Select All don't work on service categories PR #5389
* Autologin + fullscreen options #5338 PR #5338
* Directory "/var/spool/centreon" not created by Centreon-common.rpm #5405
* "Fill in" option in graph doesn't work with "VDEF" DEF type #5354
* Delete SNMP Traps #5282
* Can't duplicate trap definition #5272 PR #5280
* Virtual Metric problems with French language package #5355
* Impossible to set manually a service to a meta service for non admin users #5358 PR #5391
* Graph period displayed does not match selected zoom period #5334
* Host configuration can not be saved or modified #5348

==================
Centreon Web 2.8.9
==================

Bug Fixes
---------

* Fix Incorrect style for "Scheduled downtime" in dashboard - #5240
* Apply new Centreon graphical charter to add and modify pages for metaservice indicator - #5255
* [2.8.6] : Double quote are converted in html entities in fields Args - #5205
* Duplicate host template doesn't work - #5252
* [BUG] "Home > Poller Statistics > Graphs" only works for Central - #4954
* "Recovery notification delay" is not written to centreon-engine's configuration - #5249 - PR #5268
* Severity of 'host category' - #5245
* [2.8.8] Deploy Service action won't work - #5215
* [2.8.8] Issue when adding new connector - #5233
* [2.8.8] Data pagination - #5259
* Cannot modify metaservice indicator - #5254 - PR #5267
* [2.7.11] Migration 2.7.11 to 2.8.x does not work #5265
* 2.7 to 2.8 upgrade error - #5220
* Cannot insert numbers in service description field - #5275
* [2.8.7] - Timezone / Location BUG !! - #5218
* 2.8.8 Service Trap Relation empty - #5223
* [2.7.x/2.8.X] Old school style in popup - #5232
* [BUG] ACL - Servicegroup - #5101 - PR #5222
* [2.8.7] Missing argument 1 for PEAR::isError() - #5214 - PR #5225
* [Reporting > Dashboard > Services] Unable to export CSV - #5170 - PR #5172

Graphs
------

* Graph are not correctly scaled - #5248
* [Chart] scale in charts using CPU template is wrong Kind/Bug Status/Implemented - #5130
* Graph scale values not working - #4815
* [2.8.5] Charts upper limit different from template - #5123
* Remove chart padding - #5288
* Base Graph 1000/1024 Kind/Bug Status/Implemented - #5069
* [2.8.6] non-admin user split chart permission - #5177
* After using split chart, curves are not displayed anymore (period filter not applied) - #5198 - PR #5171
* [GRAPH] Problem with external graph usage (Widgets, Centreon BAM) - #5270
* Incorrect scale and position for rta curve (performance ping graph) - #5202
* Wrong tool tip display on chart with two units when one of the curves is disabled - #5203
* Splited chart png export misnamed doesn't work with HTTPS - #5121 - PR #5171
* [2.8.5] Splited chart png export misnamed - #5120
* [Chart] curves units are displayed on incorrect side - #5113
* Assign good unit and curves to y axis when 2 axis - #5150
* remove curves artifacts - #5153
* Beta 2.8 Curve with an weird shape. - #4644
* The round of the curves - #5143
* The extra legend is option in chart. - #5156
* Add option for display or not the toggle all curves in views charts - #5159
* Use the base from graph template for humanreable ticks - #5149

==================
Centreon Web 2.8.8
==================

Bug Fixes
---------

* Fix Centreon Engine configuration form
* Fix custom view sharing
* Fix Knowledge Base script compatibility with PHP < 5.4

==================
Centreon Web 2.8.7
==================

Bug Fixes
---------

* Fix various security issues
* Fix ldap configuration form
* Fix downtime popup in listing pages
* Fix object listing pages which are empty after some actions

==================
Centreon Web 2.8.6
==================

Bug Fixes
---------

* Downtimes - Display real BA name instead of _Module_ - #5014, PR #5094
* InfluxDB broker output config: metric columns not stored properly - #5058, PR #5089
* Poller status still working when the poller is disabled - #5126
* Filter on the status host/service on the motiroring isn't working #5131, #5140
* Fix acl on host categories for inheritance
* Avoid infinite loop in acl category
* Fix error message in install process
* Fix path to centengine and cbd init scripts
* Fix topcounter must count all meta services - #5071, PR #5100
* Fix access downtime page for users with ACL - #4952, #5025, PR #5093
* Centreon > Services - Services listed twice - #5158, PR #5010
* Custom views - problem with multiselect users when sharing View - #5029, PR #5074
* Massive change  - impossible to add service group - #5132
* Fix URL decode problem with character '+' in object's name - #5128, PR #4883
* Fix CLAPI import
* Poller status still working when the poller is disabled - #5126, PR #5133

Enhancements
------------

* Display inherited categories in host details page
* Do not check modification of configuration on disabled poller for better performance - PR #4928
* Improve access to services configuration page - PR #5077, PR #5076
* Improve global performance - PR #4900
* Improve Knowledge Base configuration
* Fix wiki links of objects with spaces in their name - #4306
* Improve documentation
* Set geo_coords parameter with clapi

If you already used a knowledge base, please execute following script :
::

	php /usr/share/centreon/bin/migrateWikiPages.php


Known bugs or issues
--------------------

* There's an issue in the ldap configuration form. A fix is available and will be package with the next bugfix version. Until then you can apply the patch available `here <https://github.com/centreon/centreon/commit/8aef6dfa4e3af27f16277b4211655889cf91fb71>`_
* There's an issue on all listing pages. A fix is available and will be package with the next bugfix version. Until then you can apply the `available patch <https://github.com/centreon/centreon/commit/d9b58f203f1af377575328d6f955ac1e9c8fb804>`_

==================
Centreon Web 2.8.5
==================

Released March 29th, 2017.

The 2.8.5 release for Centreon Web is now available for download. Here are its release notes.

Features
--------

API
###

* Possibility to create an account to reach API without web access - #4980, PR #4992


Monitoring
##########

* Better display in service detail with long output or long command - #4974, #4975, PR #5002
* Recurrent downtimes, extend specific period settings to select 2nd, 2td or 5th o month - #4207, #4908


Charts
######

* Add split function in chart - #4803, #4990
* Add button to display curve legend (min/max/average) - #4595
* Add button to display multiple periods view - #4884
* Extend chart legend and add more information on helps - PR #5006
* Extend help for stacking and transparency - #4884


Ergonomics
##########

* Add new Centreon style for some buttons - PR #5060, PR #5061, PR #5062, PR #5067, PR #5068
* Add possibility to copy-paste executed command ligne from service details page - PR #5065


Bug Fixes
---------

ACL
###

* Incorrect redirection to error page with ACL - #4932
* Dashboard not works when using filter #4886, PR #5023
* Blank page on "Monitoring > Status Details > Hosts" with acl - #4960


Authentication
##############

* Only logout are logged - #4924, PR #5004
* Autologin with any token - #4668
* generateImage.php problem with akey (auto-login) - ##4920, PR #4865


Monitoring
##########

* "Executed Check Command Line" is wrong for services associated to hostgroups - #4955, PR #5037
* Poller delete stay on Poller list in Monitoring Tab - #5026, PR #5027
* Acknowledge  - duplicate comments with external command on host monitoring page - #4862, PR #5015
* Do not display services downtimes (remove filter "h") - #4918, #4947, #5000, PR #5001
* Column 'sg_id' in field list is ambiguous - #4938
* Remove 's' in service popin for duration - PR 5051
* Select servicegroup does not work - #4907, #4885
* Escaping problem in executed command - #4976, PR #4985, PR #4999
* Fix problem on graph when user ask to display graphs of a hosts - PR #4991
* Cannot Export Event Log to CSV - #4943
* View logs for service does not work - #4958
* Centreontrapd and exec code - PR #5054


Graphs
######

* Curves color on New graph is not equal to old graph - #5033
* Wrong host title in Graph - #4964 #4984


Dashboard
#########

* Incorrect CSS for reporting of a service - #4934, PR #5009


Configuration
#############

* Exploit correlation with Centreon BAM - PR #5049
* Disable notification sounds not working - #4988, PR #4973
* Add user name in the generated configuration files - #4822
* Duplicate Poller and illegal characters - #4931, PR #4986, #4987
* Can view first help icon in Centreon Broker configuration - #4944, PR #5003
* Describe arguments does not work with % character in command line - #4930
* Generate and export SNMP traps - #4972, #4978
* Host macro did not save on host edit - #4951
* Do not check modification on disabled pollers - #4945


Custom view
###########

* Rewrite system to share public views - PR #4823
* Rewrite system to share locked views to contacts or contactgroups
* Rewrite system to share non-locked views to contacts or contactgroups
* When user access to custom views menu, edition mode is disabled - #5008, PR #4811
* Listing of widget with infinite scroll displays at least 3 times each widget - #4892
* "Set Default" button not working - #5079

Documentation
#############

* Improve installation chapters - #4970, PR #4967
* open_files_limit error during installation - #5017, #5038
* Menu "Legend" doesn't exist in Centreon 2.8.x - PR #4968, PR #4969
* Update product lifecycle - PR 5044
* Correct contact creation example - PR #5035, - PR #5036

API
###

* Rename TIMEPERIOD object to TP - PR #4913, PR #4914
* CLAPI doesn't work when Centreon BAM is installed - #4921, PR #5049, PR 5005
* DowntimeManager - do not remove downtimes not linked to objects to allows configuration with API - #5057

Backup
######

* Backup export does not work - #4726, PR #5019
* Backup won't work without old deprecated variables - #4965, #PR #5007


Installation
############

* SQL script error for upgrade from 2.6.6 to 2.7.0RC1 - #5064, PR #5066
* Using sources, error with CentPlugins Trap on install - PR #4963


Known bugs or issues
--------------------

* Centreon Engine performance chart still in RRDTools PNG format;
* Zoom out on chart change period on filters;
* User with ACL can't see it own previously created meta service;
* Problem with recurrent downtimes and DST;
* Issue with international keyboard and chrome when use accented characters;

==================
Centreon Web 2.8.4
==================

Released February 8th, 2017.

The 2.8.4 release for Centreon Web is now available for download. Here are its release notes.

Features
--------

No feature.

Bug Fixes
---------

* Fix problem with the upgrade process - all Centreon systems coming from 2.7.x have a database problem - column timezone was missing in the table $STORAGE$.hosts ;
  --> this problem prevents centreon-broker from starting

Known bugs or issues
--------------------

* Centreon Engine performance chart still in RRDTools PNG format ;
* Zoom out on chart change period on filters ;
* User with ACL can't see it own previously created meta service ;
* Problem with recurrent downtimes and DST ;

==================
Centreon Web 2.8.3
==================

Released January 11th, 2017.

The 2.8.3 release for Centreon Web is now available for download. Here are its release notes.

Features
--------

* #4807: clean generation page ;

Bug Fixes
---------

* #4843: SQL error in meta-service output ;
* #4775: disabled service are displayed in graph page ;
* #4729: command arguments are not displayed ;
* #4690: make timeperiod exceptions work ;
* #4572: poller duplication does not duplicate all fields ;
* #4838: geo coord help menu not working on hostgroup page ;
* #4827: remove old centreon-partitioning script ;
* #4826: use correct configuration file when reloading centreontrapd ;
* #4809: error during link between contact and LDAP contact group ;
* #4746: fix login when SSO header is empty ;

Known bugs or issues
--------------------

* Centreon Engine performance chart still in RRDTools PNG format ;
* Zoom out on chart change period on filters ;
* User with ACL can't see it own previously created meta service ;
* Problem with recurrent downtimes and DST ;

==================
Centreon Web 2.8.2
==================

Released December 8th, 2016.

The 2.8.2 release for Centreon Web is now available for download. Here are its release notes.

Features
--------

* #4779 : Centreon Web supports proxy configuration for use with its
  modules requiring external web access. This notably concerns Centreon
  Plugin Pack Manager (component of the Centreon IMP offer).

Bug Fixes
---------

* #4791: Can't delete host command on host/host template form ;
* #4773: Centreon Clapi call and empty line at beginning ;
* #4752: Options missing in notification tab ;
* #4728: Avoid http warnings on first connection with ldap auto import ;

Known bugs or issues
--------------------

* Centreon Engine performance chart still in RRDTools PNG format ;
* Zoom out on chart change period on filters ;
* User with ACL can't see it own previously created meta service ;
* Problem with recurrent downtimes and DST ;

==================
Centreon Web 2.8.1
==================

Released November 14th, 2016

The 2.8.1 release for Centreon Web is now available for download. Here are its release notes.

Changes
-------

* New theme for Centreon web installation and update;
* Add REST exposure for Centreon API, Centreon CLAPI still available;
* Integration of Centreon Backup module in Centreon;
* Integration of Centreon Knowledge Base module in Centreon;
* Integration of Centreon Partitioning module in Centreon;
* New design to display charts using C3JS.
* New filters available to select display charts
* Possibility to display charts on 1, 2 or 3 columns;
* Apply zoom on one chart apply zoom for all displayed charts;
* Merge of meta-services and services real-time monitoring display;
* Strict inheritance of contacts and contacts groups from hosts on services notification parameters. Contacts and groups of contacts from services definition will be erased during generation of configuration by settings from host;

Features
--------

* New servicegroups filters in real-time monitoring;
* New display of chart in pop-up of services in real-time monitoring and status details
* Add poller name in pop-up of hosts in real-time monitoring;
* Add monitoring command line with macros type password hidden (via ACL) in service status details;
* Integration of poller’s name in “Monitoring > System Logs” page;
* Integration of ACL action on poller for generation and export of configuration;
* Add new notification settings to not send recovery notification if status of host or service came back quickly to non-ok (issue for SNMP traps for example);
* Add geo-coordinates settings on hosts, services and groups. Used by Centreon Map product;
* Possibility to define a command on multi-lines;
* Add Centreon Broker graphite and InfluxDB export;
* Add possibility for all Centreon web users to select their home page after connection;
* Add possibility to define downtimes on hostgroups, servicegroups and multi-hosts;
* Add an acknowledge expiration time on host and service;
* Better ergonomics on selectbox for Mac OS and MS Windows users;
* Add possibility to set downtimes on Centreon Poller display module;
* Add possibility to reduce Centreon Broker input/output configuration;
* Optimization of SQL table for logs access;
* Add timezone on host’s template definition;

Security Fixes
--------------

* #4668: Autologin with invalid token for imported users with null password ;
* #4458: User can create admin account

Bug Fixes
---------

* #4703: Macros are always listed on command line descriptions;
* #4694: Don’t display notification in pop-up for acknowledged or downtimes objects;
* #4585, #4584, #4590: Correction of CSV export in “Monitoring > Event Logs”, “Dashboard > Hostgroups” and “Dashboard > Servicegroups” pages. Correction of XML error in “Dashboard > Hostgroups” and “Dashboard > Servicegroups” pages;
* #4617, #4609: Complete contextual help in hosts and services forms;
* #4147: Fix ACL to add widget

Removed Features
----------------

* No possibility to split charts;
* No possibility to display multi-period on one chart (Day, Week, Month, Year);

Known bugs or issues
--------------------

* This release is not yet compatible with other commercial products
  from Centreon, like Centreon MBI, Centreon BAM or Centreon Map.
  If your are using any of these products, you are strongly advised
  **NOT** to update Centreon Web until new releases of the fore mentioned
  products are available and specifically mention Centreon Web 2.8
  compatibility ;
* Centreon Engine performance chart still in RRDTools PNG format ;
* Zoom out on chart change period on filters ;
* User with ACL can't see it own previously created meta service ;
* Problem with recurrent downtimes and DST ;
* Issues on SSO Authentication
