Resource
========

Overview
--------

Object name: **centreon-configuration:Resource**

Available parameters are the following:

===================   ===========================
Parameter             Description
===================   ===========================
*--resource-name**    Resource name

--resource-line       Resource description

--resource-pollers    Slug name of poller

--resource-comment    Resource comment

--resource-activate   Enable (0 or 1)

===================   ===========================

List
----

In order to list resource, use **list** action::

  ./centreonConsole centreon-configuration:Resource:list
  id;name;slug;line;activate;comment;organization;pollers
  1;command;command;polklfd;1;dfdfdfdf;1;central
  2;switch;switch;ma ligne;0;rere fdfd;1;central
  10;resource1;resource1;;;rere fdfd;1;central
  14;$USER1$;user1-2;$1 dfdf/lk $5;1;rere fdfd;1;central



Columns are the following:

================== ===========================
Column             Description
================== ===========================
id                 Resource id

name               Resource name

slug               Slug of resource

line               Resource description

comment            Comment of the resource

activate           Enable (0 or 1)

organization       Organization

poller             Identifiant of poller

================== ===========================

Show
----

In order to show a resource, use **show** action::

  ./centreonConsole centreon-configuration:Resource:show --resource "switch"
  id: 1
  name: $USER1$
  slug: user1
  line: /usr/lib/nagios/plugins
  comment: 
  activate: 1
  organization: 1


Create
------

In order to create a resource, use **create** action::

  ./centreonConsole centreon-configuration:Resource:create --resource-name '$USER1$' --resource-pollers 'central' --resource-comment 'comment' --resource-line '/usr/lib/nagios/plugins'
  user1
  Object successfully created


Slug
----
In order to get slug of connector, use **getSlug** action::
  ./centreonConsole centreon-configuration:Resource:getSlug --resource-name '$USER1$'
  user1

Update
------

In order to update a resource, use **update** action::

  ./centreonConsole centreon-configuration:Resource:update --resource 'user1' --resource-name "$USER1$" --resource-pollers 'central' --resource-comment 'comment' --resource-line '/usr/lib/nagios/plugins' --enable
  Object successfully updated

Delete
------

In order to delete a resource, use **delete** action::

  ./centreonConsole centreon-configuration:Resource:delete --resource "linkDown"
  Object successfully deleted
