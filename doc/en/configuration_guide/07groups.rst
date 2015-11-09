======
Groups
======

A group allows us to group together one or more objects. There are three kinds of groups: hosts, services and contacts.

The hosts groups and services groups serve mainly for viewing graphics or to group the objects.
Contact groups are used mainly for the configuration of ACLs.

.. _hostgroups:

***********
Host Groups
***********

To add  a host group:

#. Go to the menu: **Configuration** ==> **Hosts**
#. In the left menu, click on **Host Groups**
#. Click on **Add**

.. image:: /images/user/configuration/07hostgroup.png
   :align: center 

* The **Host Group Name** and **Alias** defines the name and the alias of the host group.
* The **Linked Hosts** list allows us to add hosts in the hostgroup.
* The **Notes** field allows us to add optional notes concerning the host group.
* The **Notes URL** field defined a URL which can be used to give more information on the hostgroup.
* The **Action URL** field defined a URL normally use to give information on actions on the hostgroup (maintenance, etc.).
* The **Icon** field indicates the icon to be use for the host group.
* The **Map Icon** is the icon use for mapping.
* The **RRD retention** field is expressed in days, it serves to define the duration of retention of the services belonging to this hostgroup in the RRD database. It will be the default duration defined in the menu: “ **Administration** ==> **Options** ==> **CentStorage** ” if this value is not defined.
* The **Status** and **Comments** fields allow to enable or disable the host group and to make comments on it.

.. _servicegroups:

**************
Service Groups
**************

To add a service group:

#. Go into the menu: **Configuration** ==> **Services**
#. In the left menu, click on **Service Groups**
#. Click on **Add**

.. image:: /images/user/configuration/07servicegroup.png
   :align: center 

* The **Service Group Name** and **Description** fields describes the name and the description of the service group.
* The **Linked Host Services** list allows us to choose the various services that will be included in this group.
* The **Linked Host Group Services** list allows us to choose the services linked to a host group that will be part of this group.
* The **Linked Service Templates** list allows to deploy a service based on this template on all hosts linked to this group.
* The **Status** and **Comments** fields allow to enable or disable the service group and to make comment on it.

.. _contactgroups:

**************
Contact Groups
**************

To add a group of contacts:

#. Go into the menu: **Configuration** ==> **Users**
#. In the left menu, click on **Contact Groups**
#. Click on **Add**

.. image:: /images/user/configuration/07contactgroup.png
   :align: center 

* The **Contact Group Name** and **Alias** fields define the name and the description of the contact group.
* The **Linked Contacts** list allows us to add contacts to the contact group.
* The **Status** and **Comment** fields allow to enable or disable the group of contacts and to make comment on it.

.. note::
   For more information refer to the associated chapter covering :ref:`groups<categoriesandgroups>`.
