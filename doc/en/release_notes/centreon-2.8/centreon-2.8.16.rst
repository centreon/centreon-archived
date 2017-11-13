###################
Centreon Web 2.8.16
###################

Enhancements
============

* [Administration] Improve 'Server Status' page PR #5820
* [API] Add exceptions for realtime PR #5735  #5795
* [Configuration] Broker remove non existing protocol #5830 PR #5832
* [Configuration] Check illegal charaters one time only PR #5831
* [Documentation] Wrong translation in documentation #5858 PR #5862
* [Documentation] Improve installation documentation #5825 PR #5844
* [Documentation] Improve Time Period documentation #5828 #5637 PR #5845 #5843
* [Documentation] Improve API realtime downtimes examples

Bugfix
======

* [Install] Properly place update to 2.8 from 2.7. #5809
* [ACL] centAcl cron LDAP sync removes all ContactGroups on unexpected error  #5547
* [API] Parent/Child relation are not exported with CLAPI #5605 PR #5857
* [API] Authorize id 0 for object PR #5812
* [Chart] Add legend name when defined PR #5817
* [Configuration] Improve host/service macro visibility
* [Configuration] add massive change contact/cg update mode for host form #5878
* [Knowledge Base] Search function non functionnal for templates of services #5762 PR #5829
* [Knowledge Base] Increase page limit for mediawiki migration PR #5798
* [Monitoring] Custom MACRO not interpreted in URL #5846 PR #5850
* [Monitoring] Display 0 in top counter if SQL result is empty #5758 PR #5826
* [Security] Some field was not encoded PR #5847
