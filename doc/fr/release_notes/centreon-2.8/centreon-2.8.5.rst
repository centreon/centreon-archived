##################
Centreon Web 2.8.5
##################

Released March 29th, 2017.

The 2.8.5 release for Centreon Web is now available for download. Here are its release notes.

Features
========

API
---

* Possibility to create an account to reach API without web access - #4980, PR #4992


Monitoring
----------

* Better display in service detail with long output or long command - #4974, #4975, PR #5002
* Recurrent downtimes, extend specific period settings to select 2nd, 2td or 5th o month - #4207, #4908


Charts
------

* Add split function in chart - #4803, #4990
* Add button to display curve legend (min/max/average) - #4595
* Add button to display multiple periods view - #4884
* Extend chart legend and add more information on helps - PR #5006
* Extend help for stacking and transparency - #4884


Ergonomics
----------

* Add new Centreon style for some buttons - PR #5060, PR #5061, PR #5062, PR #5067, PR #5068
* Add possibility to copy-paste executed command ligne from service details page - PR #5065


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
* Autologin with any token - #4668
* generateImage.php problem with akey (auto-login) - ##4920, PR #4865


Monitoring
----------

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
* Describe arguements does not work with % character in command line - #4930
* Generate and export SNMP traps - #4972, #4978
* Host marco did not save on host edit - #4951
* Do not check modification on disabled pollers - #4945


Custom view
-----------

* Rewrite system to share public views - PR #4823
* Rewrite system to share locked views to contacts or contactgroups
* Rewrite system to share non-locked views to contacts or contactgroups
* When user access to custom views menu, edition mode is disabled - #5008, PR #4811
* Listing of widget with infinite scroll displays at least 3 times each widget - #4892
* "Set Default" button not working - #5079

Documentation
-------------

* Improve installation chapters - #4970, PR #4967
* open_files_limit error during installation - #5017, #5038
* Menu "Legend" doesn't exist in Centreon 2.8.x - PR #4968, PR #4969
* Update product lifecycle - PR 5044
* Correct contact creation example - PR #5035, - PR #5036


API
---

* Rename TIMEPERIOD object to TP - PR #4913, PR #4914
* CLAPI doesn't work when Centreon BAM is installed - #4921, PR #5049, PR 5005
* DowntimeManager - do not remove downtimes not linked to objects to allows configuration with API - #5057


Backup
------

* Backup export does not work - #4726, PR #5019
* Backup won't work without old deprecated variables - #4965, #PR #5007


Installation
------------

* SQL script error for upgrade from 2.6.6 to 2.7.0RC1 - #5064, PR #5066
* Using sources, error with CentPlugins Trap on install - PR #4963


Known bugs or issues
====================

* Centreon Engine performance chart still in RRDTools PNG format;
* Zoom out on chart change period on filters;
* User with ACL can't see it own previously created meta service;
* Problem with recurrent downtimes and DST;
* Issue with international keyboard and chrome when use accented characters;
