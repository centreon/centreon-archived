##############
Centreon 2.8.5
##############

Released March Xth, 2017.

The 2.8.5 release for Centreon Web is now available for download. Here are its release notes.

Features
========

API
---

* Possibility to create an account to reach API without web access - #4980, PR #4992

Monitoring
----------

* Better display in service detail with long output or long command - #4974, #4975, PR #5002

Bug Fixes
=========

ACL
---

* Incorrect redirection to error page with ACL - #4932
* Dashboard not works when using filter #4886, PR #5023
* Blank page on "Monitoring > Status Details > Hosts" with acl - #4960

Authentication
--------------

* Only logout are logged - #4924, PR #5004
* generateImage.php problem with akey (auto-login) - ##4920, PR #4865

Event Logs
----------

* Select servicegroup does not work - #4907, #4885

Monitoring
----------

* "Executed Check Command Line" is wrong for services associated to hostgroups - #4955, PR #5037
* Poller delete stay on Poller list in Monitoring Tab - #5026, PR #5027
* Acknowledge  - duplicate comments with external command on host monitoring page - #4862, PR #5015
* Do not display services downtimes (remove filter "h") - #5000, PR #5001
* Column 'sg_id' in field list is ambiguous - #4938

Graphs
------

* Curves colour on New graph is not equal to old graph - #5033
* Wrong host title in Graph - #4964 #4984

Dashboard
---------

* Incorrect CSS for reporting of a service - #4934, PR #5009

Configuration
-------------

* Exploit correlation with Centreon BAM - PR #5049
* Disable notification sounds not working - #4988, PR #4973
* Add user name in the generated configuration files - #4822
* Duplicate Poller and illegal characters - #4931, PR #4986, #4987
* Can view first help icon in Centreon Broker configuration - #4944, PR #5003

Custom view
-----------

* Rewrite system to share public views - PR #4823
* Rewrite system to share locked views to contacts or contactgroups
* Rewrite system to share non-locked views to contacts or contactgroups
* When user access to custom views menu, edition mode is disabled - #5008, PR ##4811

Documentation
-------------

* Improve installation chapters - #4970
* open_files_limit error during installation - #5017, #5038
* Menu "Legend" doesn't exist in Centreon 2.8.x - PR #4968, PR #4969

API
---

* Rename TIMEPERIOD object to TP - PR #4913
* CLAPI doesn't work when Centreon BAM is installed - #4921, PR #5049

Backup
------

* Backup export does not work - #4726, PR #5019
* Backup won't work without old deprecated variables - #4965, #PR #5007

Installation
------------

* SQL script error for upgrade from 2.6.6 to 2.7.0RC1 - #5064, PR #5066

Known bugs or issues
====================

* Centreon Engine performance chart still in RRDTools PNG format;
* Zoom out on chart change period on filters;
* User with ACL can't see it own previously created meta service;
* Problem with recurrent downtimes and DST;
