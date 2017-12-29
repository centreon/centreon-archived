###################
Centreon Web 2.8.14
###################

Enhancements
============

* [API] Update CLAPI commands to show resources of a downtime PR #5705
* [API] Add possibility to grant access to children menu (or not) PR #5694
* [API] Add possibility to add and get list of on-demand downtime #5192 #5682 PR #5623 - beta
* [API] Add possibility to get realtime hosts status #5682 - beta
* [API] Add possibility to get realtime services status #5682 - beta
* [Documentation] Activate services at system startup PR #5698
* [Administration] Add possibility to test proxy configuration #5561 PR #5722

Bugfix
======

* [API] Fix list of hosts with gethosts method of Instance object #5300 PR #5603
* [Install]  Add unique key on comments table PR #5665
* [Custom Views] Sharing View problem to select multiple users #5029
* [Configuration] Multiple 'update mode' fields in massive changes #5266 PR #5636
* [configuration] Massive Change on Hosts activate Stalking Option Up #4946
* [Reporting] Reporting Dashboard messed up #5491 #5520
* [Monitoring] No inheritance in query of notified contacts #4981
* [Monitoring] Top counter display too much resources with ACL #5713 PR #5703
