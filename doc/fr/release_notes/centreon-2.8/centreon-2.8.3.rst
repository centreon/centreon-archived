##################
Centreon Web 2.8.3
##################

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
