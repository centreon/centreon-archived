====================
Centreon Web 19.04.5
====================

Bug fixes
---------

* LDAP users using DN with special chars cannot login
* LDAP connection issue
* Log pagination does not work
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
* Incorrect CSV export of Event Logs
* Disable the install button when installing modules (last install step)
* Select all elements in select2 freeze the screen
* Recurrent downtimes search bug
* Unable to hide service template macro with Clapi
* Calculation of contact group too frequent
* Unable to set host notification to none
* Purge old user actions
* Remove unused radio button in meta-service configuration
* Correctly toggle edit when widgets load
* Add curve label in API
* Display scrollbar behind popin

Security
--------

* No check for authentication
* SQL injections
* Cross-site request forgery
* Session fixation
* RCE flaws
* Authentication flaw
* XSS

Documentation
-------------

* Update performance FAQ for rrdcached
