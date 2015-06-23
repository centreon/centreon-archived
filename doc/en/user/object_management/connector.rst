Connector
=========

Overview
--------

Object name: **centreon-configuration:Connector**

Available parameters are the following:

================== ======================
Parameter          Description
================== ======================
**name**           Connector name

**command-line**   Command line

**enabled**        Enable (0 or 1)

description        Connector description

connector-command  Linked commands
================== ======================

List
----

In order to list commands, use **list** action::

  ./centreonConsole centreon-configuration:Connector:list
  id;name;description;command_line;activate
  1;Perl Connector;;$USER3$/centreon_connector_perl;1
  2;SSH Connector;;$USER3$/centreon_connector_ssh;1

Columns are the following:

============== ======================
Column         Description
============== ======================
id             Connector id

name           Connector name

description    Connector description

command line   Command line

activate       Enable (0 or 1)
============== ======================

Show
----

In order to show a connector, use **show** action::

  ./centreonConsole centreon-configuration:Connector:show --connector 'ssh-connector'
  id: 2
  name: SSH Connector
  slug: ssh-connector
  description:
  command_line: $USER3$/centreon_connector_ssh
  activate: 1
  created: 1432132910
  modified: 1432132910
  organization_id: 1

Create
------

In order to create a connector, use **create** action::

  ./centreonConsole centreon-configuration:Connector:create --name 'ssh-connector' --command-line '$USER3$/ssh_connector' --enabled 1
  Object successfully created

Update
------

In order to update a connector, use **update** action::

  ./centreonConsole centreon-configuration:Connector:update --command 'ssh-connector' --description 'ssh-connector' --enabled 0
  Object successfully updated

Delete
------

In order to delete a connector, use **delete** action::

  ./centreonConsole centreon-configuration:Connector:delete --connector 'ssh-connector'
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a connector, use **duplicate** action::

  ./centreonConsole centreon-configuration:Connector:duplicate --command 'ssh-connector'
  Object successfully duplicated

