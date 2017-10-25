###############
Centreon 2.7.13
###############

The 2.7.13 release for Centreon Web is now available for `download <https://download.centreon.com>`_.
The full release notes for 2.7.13 follow.

* CSV Export of chart is wrong if missing values in selected period #5756
* Manage downtimes with dst - 2.7.x #5786

******
Notice
******

This version include a fix for the calculation of downtimes with daylight saving 
time (DST). The downtime end will be calculate with the new hour.

For example, if you put a downtime from 1 AM to 5 AM, the duration of the 
downtime will be 5 hours if during the DST you get 1 hour more (3 AM come back 
to 2 AM).

If you are upgrading from a version prior to 2.7.0, make sure to go through all the release notes available
`here <http://documentation.centreon.com/docs/centreon/en/latest/release_notes/index.html>`_.




