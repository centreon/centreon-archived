##################
Centreon Web 2.8.1
##################

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
* Better ergonomy on selectbox for Mac OS and MS Windows users;
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
  **NOT** to update Centreon Web until new releases of the forementioned
  products are available and specifically mention Centreon Web 2.8
  compatibility ;
* Centreon Engine performance chart still in RRDTools PNG format ;
* Zoom out on chart change period on filters ;
* User with ACL can't see it own previously created meta service ;
* Problem with recurrent downtimes and DST ;
* Issues on SSO Authentication

