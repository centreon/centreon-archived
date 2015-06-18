Command
=======

Overview
--------

Object name: **centreon-configuration:Command**

Available parameters are the following:

================== =========================
Parameter          Description
================== =========================
**command-name**   Command name

**command-type**   Command type

**command-line**   Command line

enable-shell       Enable shell (0 or 1)

connector-id       Connector id

command-comment    Comments for the command
================== =========================

List
----

In order to list commands, use **list** action::

  ./centreonConsole centreon-configuration:Command:list
  id;name;command line;type
  1;check_icmp;$USER1$/check_icmp -H $HOSTADDRESS$;2
  2;check_http;$USER1$/check_http -H $HOSTADDRESS$;2

Columns are the following:

============== ==============
Column         Description
============== ==============
id             Command id

name           Command name

command line   Command line

type           Command type
============== ==============

Show
----

In order to show a command, use **show** action::

  ./centreonConsole centreon-configuration:Command:show --command 'check-icmp'
  id: 1
  connector_id:
  name: check_icmp
  command_slug: check-icmp
  command line: $USER1$/check_icmp -H $HOSTADDRESS$
  command_example:
  type: 2
  enable_shell: 0
  command_comment:
  graph_id:
  cmd_cat_id:
  organization_id: 1

Create
------

In order to create a command, use **create** action::

  ./centreonConsole centreon-configuration:Command:create --command-name 'check_icmp' --command-type 2 --command-line '$USER1$/check_icmp -H $HOSTADDRESS$'
  Object successfully created

Update
------

In order to update a command, use **update** action::

  ./centreonConsole centreon-configuration:Command:update --command 'check-icmp' --command-line '$USER1$/check_icmp -H $HOSTADDRESS$ -c 5'
  Object successfully updated

Delete
------

In order to delete a command, use **delete** action::

  ./centreonConsole centreon-configuration:Command:delete --command 'check-icmp'
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a command, use **duplicate** action::

  ./centreonConsole centreon-configuration:Command:duplicate --command 'check-icmp'
  Object successfully duplicated

