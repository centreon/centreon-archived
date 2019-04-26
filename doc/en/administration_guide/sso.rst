.. _sso:

*************
Implement SSO
*************

How SSO works with Centreon ?
=============================

This is an example of architecture with LemonLDAP :

.. image:: /images/howto/SSO_architecture.png
   :align: center

1. The user signs in SSO authentication portal
2. The authentication portal checks user access on LDAP server
3. The LDAP server returns user information
4. The authentication portal creates a session to store user information and returns SSO cookie to the user
5. The user is redirected to Centreon Web and catched by the SSO handler which checks user access
6. The SSO handler sends request to Centreon Web with login header (i.e HTTP_AUTH_USER)
7. Centreon Web checks user access by login on LDAP server
8. The LDAP server returns user information
9. Centreon Web returns information to the handler
10. The SSO handler transfers information to the user

How to configure SSO in Centreon ?
==================================

You can configure SSO in **Administration > Parameters** :

.. image:: /images/howto/SSO_configuration.png
   :align: center

For more information, please refer :ref:`here<centreon_parameters>`

Security warning
================

SSO feature has only to be enabled in a secured and dedicated environment for SSO.
Direct access to Centreon UI from users have to be disabled.

