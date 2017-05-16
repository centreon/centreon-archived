##################
Centreon Web 2.8.4
##################

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
