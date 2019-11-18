====================
Centreon Web 19.04.5
====================

Bug fixes
---------

* Unable to add downtime to service group
* The option to hide auto login has no effect
* Macro passwords are not hidden
* Broker form might be lost when saving configuration
* Be able to open menu entry in a new tab
* Better error handling when PNG generation fails
* Fail to upgrade after reloading the process
* Double host name display in host detail
* Improve centreonworker logging in Remote server
* Metric name are not properly ordered on CSV export
* Disable the install button when installing modules (last install step)
* Select all elements in select2 freeze the screen
* Recurrent downtimes search bug
* Unable to hide service template macro with Clapi
* Calculation of contact group too frequent
* Unable to set host notification to none
* Purge old user actions
* Remove unused radio button in meta-service configuration

Security
--------

* No check for authentication
* SQL injection
* Cross-site request forgery
* Session fixation
* RCE flaws
* Authentication flaw
* Persistent XSS in "My account"
* SQL injection in makeXMLForOneService.php
* Authenticated RCE through Settings -> Commands -> Miscellaneous
* Persistent cross-site scripting

Documentation
-------------

* Update performance FAQ for rrdcached
