#####################
Centreon Web 19.04.19
#####################

Bug fixes
---------

* [ACL] Incorrect inheritance of categories/severities for services
* [CLAPI] Add getparams
* [CLAPI] Carriage return and line feed breaks comments
* [Configuration] Dependencies not deleted when last parent deleted
* [Remote-Server] incorrect url to contact Centreon Central Server
* [Widgets] Can't change position of widgets
* [Widgets] Parameters are deleted when importing/deleting/importing a custom view

Security fixes
--------------

* [API] Information Disclosure in centreon_wiki internal API
* [Administration] Horizontal privilege escalation / session takeover
* [Configuration] Cross Site Scripting in widget rename
* [Configuration] RCE in SNMP trap import
* [Configuration] Vulnérabilités d’injections SQL in "Configuration > Host categories"
* [Configuration] Vulnérabilités d’injections SQL in "Configuration > Service categories"
* [Configuration] ]Vulnérabilités d’injections SQL in "Configuration > Service Groups"
* [Knowledge-Base] ]Password in plain text in "Configuration > Knowledge base" menu
