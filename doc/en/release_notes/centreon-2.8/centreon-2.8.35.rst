###################
Centreon Web 2.8.35
###################

Security
--------

* [ACL/Access Groups] Cross-site Scripting (XSS) Stored/Persistent for search
* [ACL/Actions Access] Cross-site Scripting (XSS) Stored/Persistent for search
* [ACL/Resources Access] Cross-site Scripting (XSS) Stored/Persistent for search
* [Administration/LDAP] new LDAP configurations are broken
* [Configuration > Servicegroups] Leak of technical information
* [Configuration/Connectors/Commands] Cross-site Scripting (XSS) Stored/Persistent
* [Configuration/Contact Groups] Cross-site Scripting (XSS) Stored/Persistent
* [Configuration/Contact] XSS in updateContactParam.php & commonJS.php
* [Configuration/H/HTPL/S/STPL] Password in plain text
* [Core] Centreon token is vulnerable against replay attack
* [Core] Lack of click diversion protection (Clickjacking)
* [Core] Lack of protection for session cookies
* [Core] Support for the HTTP TRACE method
* [Core] Token usage is not mandatory
* [Custom Views] List of user accounts in custom view
* [Custom Views] XSS stored in widget name
* [Media] Broken authentication of uploaded files
* [Media] PHP warning about missing tmp dir used during media upload