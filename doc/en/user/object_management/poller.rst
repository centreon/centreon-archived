Poller
======

Overview
--------

Object name: **centreon-configuration:poller**

Available parameters are the following:

============================== =====================================
Parameter                      Description
============================== =====================================
**name**                       Poller name

**ip-address**                 Poller IP address

**template**                   Poller template (Central, Poller ...)

**engine-init-script**         Engine init script

**engine-binary**              Engine binary

**engine-conf-dir**            Engine configuration directory

**engine-logs-dir**            Engine logs directory

**engine-var-lib-dir**         Engine var lib directory

**engine-modules-dir**         Engine modules directory

**broker-config-dir**          Broker configuration directory

**broker-modules-dir**         Broker modules directory

**broker-data-dir**            Broker data directory

**broker-logs-dir**            Broker logs directory

**broker-cbmod-dir**           Broker cbmod directory

**broker-init-script**         Broker init script
============================== =====================================

List
----

In order to list pollers, use **list** action::

  ./centreonConsole centreon-configuration:poller:list
  id;node;name;slug;one peer retention;template name;organization;enable
  1;1;central;central;1;Central;1;1
  2;2;new;new;1;Central;1;1


Columns are the following:

===================== ======================
Column                Description
===================== ======================
id                    Poller id

node                  Poller node

name                  Poller name

slug                  Poller slug

one peer retention    One peer retention

template name         Template name

organization          Organization id

enable                Enabled (0 or 1)
===================== ======================

Show
----

In order to show a poller, use **show** action::

  ./centreonConsole centreon-configuration:poller:show --poller=central
  id: 1
  node: 1
  organization: 1
  name: central
  slug: central
  port: 0
  one peer retention: 1
  template name: Central
  enable: 1

Create
------

In order to create a poller, use **create** action::

  ./centreonConsole centreon-configuration:poller:create --name=central1 --template=Central --ip-address="127.0.0.1" --engine-init-script='/etc/init.d/centengine' --engine-binary='/usr/sbin/centengine' --engine-conf-dir='/etc/centreon-engine/' --engine-logs-dir='/var/log/centreon-engine/' --engine-var-lib-dir='/var/lib/centreon-engine/' --engine-modules-dir='/usr/lib64/centreon-engine/' --broker-conf-dir='/etc/centreon-broker/' --broker-modules-dir='/usr/share/centreon/lib/centreon-broker/' --broker-data-dir='/var/lib/centreon-broker' --broker-logs-dir='/var/log/centreon-broker/' --broker-cbmod-dir='/usr/lib64/nagios/' --broker-init-script='/etc/init.d/cbd'
  central1
  Object successfully created


Slug
----
In order to get slug of poller, use **getSlug** action::
  ./centreonConsole centreon-configuration:poller:getSlug --description 'Central'
  central


Update
------

In order to update a poller, use **update** action::

  ./centreonConsole centreon-configuration:poller:update --poller=central --engine-init-script=/etc/init.d/nagios
  Object successfully updated

Delete
------

In order to delete a poller, use **delete** action::

  ./centreonConsole centreon-configuration:poller:delete --poller=poller1
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a poller, use **duplicate** action::

  ./centreonConsole centreon-configuration:poller:duplicate --poller=poller1
  Object successfully duplicated

