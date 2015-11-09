.. _contacts:

========
Contacts
========

Overview
--------

Object name: **CONTACT**


Show
----

In order to list available contacts, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o contact -a show
  id;name;alias;email;pager;gui access;admin;activate
  4;Guest;guest;guest@localhost;;0;0;0
  5;Supervisor;admin;root@localhost;;1;1;1
  6;User;user;user@localhost;;0;0;0

Columns are the following :

=============== ================================================
Column          Description
=============== ================================================
ID		ID of contact

Name            Name of contact

Alias           Alias of contact (also login id)

Email           Email of contact

Pager           Phone number of contact
      
GUI Access      *1* (can access UI) or *0* (cannot access UI)

Admin           *1* (admin) or *0* (non admin)

activate        *1* (enabled) or *0* (disabled)
=============== ================================================

Add
---

In order to add a contact, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CONTACT -a ADD -v "user;user;user@mail.com;mypassword;1;1;en_EN;local" 


The required parameters are the following:

========================== ================================================
Parameter                  Description
========================== ================================================
Name                       Name of contact

Alias (login)              Alias of contact (also login id)

Email                      Email of contact

Password                   Password of contact

Admin                      *1* (admin) or *0* (non admin)

GUI Access                 *1* (can access UI) or *0* (cannot access UI)

Language                   Language pack has to be installed on Centreon

Authentication type        *local* or *ldap*
========================== ================================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Del
---

In order to delete one contact, use the **DEL** action. The contact name is used for identifying the contact you would like to delete::

  [root@centreon core]# ./centreon -u admin -p centreon -o contact -a del -v "user" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Setparam
--------

If you want to change a specific parameter for a contact, use the **SETPARAM** action::

  [root@centreon core]# ./centreon -u admin -p centreon -o contact -a setParam -v "contact alias;hostnotifcmd;command name" 
  [root@centreon core]# ./centreon -u admin -p centreon -o contact -a setParam -v "contact alias;svcnotifcmd;command name" 
  root@centreon core]# ./centreon -u admin -p centreon -o contact -a setParam -v "contact alias;hostnotifperiod;period name" 

The required parameters are the following:

=============   ===========================
Parameter       Description
=============   ===========================
Contact alias   Alias of contact to update

Parameter       Parameter to update

Value           New value of parameter
=============   ===========================


Parameters that you can change are the following:

========================== ============================================================================================
Parameter	           Description
========================== ============================================================================================
name	                   Name

alias	                   Alias

comment                    Comment

email	                   Email Address

password	           User Password

access                     Can reach centreon, *1* if user has access, *0* otherwise

language	           Locale

admin	                   *1* if user is admin, *0* otherwise

authtype	           *ldap* or *local*

hostnotifcmd	           host notification command(s). Multiple commands can be defined with delimiter "|"

svcnotifcmd	           service notification command(s). Multiple commands can be defined with delimiter "|"

hostnotifperiod	           host notification period

svcnotifperiod	           service notification period

hostnotifopt               can be d,u,r,f,s,n

servicenotifopt	           can be w,u,c,r,f,s,n

address1	           Address #1

address2	           Address #2

address3	           Address #3

address4	           Address #4

address5	           Address #5

address6	           Address #6

ldap_dn                    LDAP domain name

enable_notifications	   *1* when notification is enable, *0* otherwise

autologin_key	           Used for auto login

template	           Name of the template to apply to the contact
========================== ============================================================================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Enable
------

In order to enable a contact, use the **ENABLE** action::

  [root@centreon core]# ./centreon -u admin -p centreon -o contact -a enable -v "test" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.

Disable
-------

In order to disable a contact, use the **DISABLE** action::

  [root@centreon core]# ./centreon -u admin -p centreon -o contact -a disable -v "test" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.
