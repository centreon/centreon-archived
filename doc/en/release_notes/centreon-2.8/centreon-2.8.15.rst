###################
Centreon Web 2.8.15
###################

Important notice
================

This version include a fix for the calculation of downtimes with daylight saving 
time (DST). The downtime end will be calculate with the new hour.

For example, if you put a downtime from 1 AM to 5 AM, the duration of the 
downtime will be 5 hours if during the DST you get 1 hour more (3 AM come back 
to 2 AM).

Enhancements
============

* [Documentation] Improve api documentation (url) #5792
* [Downtimes] Manage downtimes with dst (recurrent and realtime) #5780

Bugfix
======

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
