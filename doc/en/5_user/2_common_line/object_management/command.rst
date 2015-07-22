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
  id;name;slug;command line;type
  1;Send mail;send-mail;mail -s test test;1
  2;check_centreon_ping;check-centreon-ping;$USER1$/check_icmp -H $HOSTADDRESS$ -n $_SERVICEPACKETNUMBER$ -w $_SERVICEWARNING$ -c $_SERVICECRITICAL$;2
  3;check-host-alive;check-host-alive;$USER1$/check_icmp -H $HOSTADDRESS$ -n $_HOSTPACKETNUMBER$ -w $_HOSTWARNING$ -c $_HOSTCRITICAL$;2


Columns are the following:

============== ==============
Column         Description
============== ==============
id             Command id

name           Command name

slug           Command slug

command line   Command line

type           Command type
============== ==============


Show
----

In order to show a command, use **show** action::

  ./centreonConsole centreon-configuration:Command:show \
    --command 'check-centreon-ping'
  id: 2
  connector-id: 
  name: check_centreon_ping
  slug: check-centreon-ping
  command line: $USER1$/check_icmp -H $HOSTADDRESS$ -n $_SERVICEPACKETNUMBER$ -w $_SERVICEWARNING$ -c $_SERVICECRITICAL$
  command_example: 
  type: 2
  enable-shell: 0
  command-comment: 
  graph_id: 
  cmd_cat_id: 
  organization_id: 1


Create
------

In order to create a command, use **create** action::

  ./centreonConsole centreon-configuration:Command:create \
    --command-name='check_centreon_ping' \
    --command-type=2 \
    --command-line='$USER1$/check_icmp -H $HOSTADDRESS$ -n $_SERVICEPACKETNUMBER$ -w $_SERVICEWARNING$ -c $_SERVICECRITICAL$'
  check-centreon-ping
  Object successfully created


Slug
----
In order to get slug of command, use **getSlug** action::
  ./centreonConsole centreon-configuration:Command:getSlug \
    --command-name 'check_centreon_ping'
  check-centreon-ping


Update
------

In order to update a command, use **update** action::

  ./centreonConsole centreon-configuration:Command:update \
    --command 'check-centreon-ping' \
    --command-line '$USER1$/check_icmp -H $HOSTADDRESS$ -c 5'
  Object successfully updated

With the "update" command you can easily update all parameters 

Delete
------

In order to delete a command, use **delete** action::

  ./centreonConsole centreon-configuration:Command:delete \
    --command 'check-centreon-ping'
  Object successfully deleted


Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a command, use **duplicate** action::

  ./centreonConsole centreon-configuration:Command:duplicate \
    --command 'check-centreon-ping'
  Object successfully duplicated


