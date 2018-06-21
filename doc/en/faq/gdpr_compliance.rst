.. _gdpr_compliance:

===============
GDPR Compliance
===============

Information Centreon customers should know to prepare for their GDPR Compliance
===============================================================================

In a Managed Service Provider (MSP) context, the Centreon platform delivers monitoring services to the MSP's customers.

Storing User Identification information
---------------------------------------

For each MSP's customer, the Centreon Central Server stores in its SQL database the identification information of the users that can access the monitoring service:

* name
* alias (login), password
* email address
* phone number (optional, for notification purpose)

The Central Server also stores the service parameters of each user:

* default language, timezone
* notification parameters
* ACL groups

Information management:

* Each user can access to his/her own information from the **Administration > Parameters > MyAccount** menu.
* The users can be created, changed or deleted from the **Configuration > Users** menu by any user which ACL grant access to this menu.

Logging User actions
--------------------

If a user is allowed to change the monitoring configuration (as defined by its ACL), a log message with the user alias is stored on the Centreon Central Server SQL database each time a configuration action is performed by this user:

* These logs can be listed in the **Administration > Logs** menu, filtered by user.
* These logs can only be deleted by accessing the SQL database and deleting any relevant record.

HTTP Transactions
-----------------

Centreon recommends securing the monitoring platform by activating the HTTPS mode on the Apache server. A signed official certificate is required to ensure a minimum level of security.

Authentication
--------------

In order to stay consistent with your security policy and to better manage user lifecycle and approvals, Centreon has an option to enable linking to an Active Directory or LDAP directory. Centreon recommends enabling this option and not using a local account.

Backup
------

Centreon provides a Centreon data extraction module to enable the implementation of a supervisory data backup policy. Centreon strongly recommends to set up this module and especially not to leave the data on the supervision platform.
