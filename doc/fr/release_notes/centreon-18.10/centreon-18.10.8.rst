====================
Centreon Web 18.10.8
====================

Bug fixes
---------

* Missing Centengine configuration options
* Unable to add downtime to service groups
* The option to hide auto login has no effect
* Macro passwords are not hidden
* Broker form might be lost when saving configuration
* LDAP contact groups are not exported properly
* Better error handling when PNG generation fails
* Double host name display in host detail
* Metric name are not properly ordered on CSV export
* Recurrent downtimes search bug
* Unable to hide service template macro with Clapi
* Purge old user actions
* Remove unused radio button in meta-service configuration

Security
--------

* No check for authentication
* SQL injection
* Cross-site request forgery
* Session fixation
* RCE flaws
* Authentication flaws
* Persistent XSS in "My account"
* SQL injection in makeXMLForOneService.php
* Authenticated RCE through Settings -> Commands -> Miscellaneous
* Persistent cross-site scripting

Documentation
-------------

* Update performance FAQ for rrdcached
