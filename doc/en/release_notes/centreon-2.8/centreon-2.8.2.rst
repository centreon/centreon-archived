##################
Centreon Web 2.8.2
##################

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
