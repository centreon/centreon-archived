.. _ldap:

====
LDAP
====

This guide aims to describe how the LDAP authentication works on Centreon.


******************
LDAP configuration
******************

It is possible to have multiple LDAP configurations, each user will be bound to a single configuration. Let's have a look into the configuration page.

First of all, add a new configuration:

.. image:: /_static/images/user/advanced/ldap_conf_1.png
   :align: center


Enter the general information regarding the configuration:

.. image:: /_static/images/user/advanced/ldap_conf_2.png
   :align: center

================================================== ================================================================
Parameter                                          Description
================================================== ================================================================
Configuration name                                 Name used for identifying the configuration

Description                                        Short description regarding the configuration

Enable LDAP authentification                       Whether this configuration is enabled for LDAP authentication

Store LDAP password                                Whether or not user passwords will be stored in database when
                                                   they log in. This could act as a fallback system if necessary

Auto import users                                  Whether the users will be automatically imported into the
                                                   Centreon database on connection

LDAP search size limit                             Maximum number of entries that Centreon will retrieve on lookup
                                                   For better performances, it is best to keep this number as low
                                                   as possible.

LDAP search timeout                                Timeout on LDAP search (in seconds)

Contact template                                   Imported users will be tied to this contact template

Use service DNS                                    When enabled, Centreon will look for LDAP servers based on DNS
================================================== ================================================================


Information regarding the LDAP server(s):

.. image:: /_static/images/user/advanced/ldap_conf_3.png
   :align: center

Click on the ``Add a new LDAP server`` link to declare a new LDAP server. 

================================================== ================================================================
Parameter                                          Description
================================================== ================================================================
Host name                                          Host address of the LDAP server

Port                                               Port used by LDAP

SSL                                                Whether SSL is enabled

TLS                                                Whether TLS is enabled

Order                                              Priority order, used in case of failover (requires one or more
                                                   LDAP servers)
================================================== ================================================================


.. note::
  Failover works only if the LDAP servers have the same structure


Information regarding the structure of the LDAP server(s):

.. image:: /_static/images/user/advanced/ldap_conf_4.png
   :align: center

This part is specific to your LDAP server, contact your LDAP administrator for more information.


****************
LDAP user import
****************

It is possible to manually import users from LDAP servers.

Click on the ``Import users manually`` button from the LDAP configuration form, you will be redirected to the import page.

.. image:: /_static/images/user/advanced/ldap_conf_5.png
   :align: center

Select the LDAP server to scan and hit the ``Search`` button. The search should return results:

.. image:: /_static/images/user/advanced/ldap_conf_6.png
   :align: center

.. note::
  When looking for a specific user, it is best to edit the search filter

Select the user(s) to import and hit the ``Import`` button. You should now see the new users in the contact list:

.. image:: /_static/images/user/advanced/ldap_conf_7.png
   :align: center


***************************
LDAP virtual contact groups
***************************

When LDAP is enabled in Centreon, you will see new contact groups appear in the form of ``ACL access group``. These contact groups are the same as the ones that are found during the LDAP search. Linking these groups to the ACL access groups will apply global ACL rules on the freshly imported users, based on their LDAP groups.

.. image:: /_static/images/user/advanced/ldap_conf_8.png
   :align: center

For more information regarding the ACL mechanism of Centreon, refer to this :ref:`section <acl>`.
