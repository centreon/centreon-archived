***********
Description
***********

"centreond" is a daemon which handles some tasks. You can plug-in some modules:

* centreond-action: execute commands, send files/directories
* centreond-cron: schedule tasks
* centreond-acl: manage centreon ACL
* centreond-proxy: push tasks (to another "centreond" instance) or execute (through SSH)

The daemon is installed on centreon central server and also poller server.
It uses zeromq library.

******************
centreond protocol 
******************

"centreond-core" (main mandatory module) can have 2 interfaces:

* internal: uncrypted dialog (used by internal modules. Commonly in ipc)
* external: crypted dialog (used by third-party clients. Commonly in tcp)

.. _handshake-scenario:

==================
Handshake scenario
==================

Third-party clients connected had to use the zeromq library and the following process:

* client : need to create an uniq identity (will be used in "zmq_setsockopt" and "ZMQ_IDENTITY")
* client -> server : send the following message crypted with the public key of the server:

::

  [HELO] [HOSTNAME]

* server: uncrypt the client message:

  * If uncrypted message result is not "HELO", server refused the connection and send it back:

  ::
  
    [ACK] [] { "code" => 1, "data" => { "message" => "handshake issue" } }

  * If uncrypted message result is "HELO", server accepts the connection. It creates symmetric key and send the following message crypted with its private key:

  ::

    [KEY] [HOSTNAME] [symmetric key]

* client: uncrypt the server message with the public key of the server.
* client and server uses the symmetric key to dialog

The server keeps sessions for 24 hours since the last message of the client. Otherwise, it purges the identity/symmetric-key of the client.
If a third-party client with the same identity try to open a new session, the server deletes the old identity/symmetric-key.

.. Warning::
  Be sure to have the same parameters to crypt/uncrypt with the symmetric key. Commonly: 'AES' cipher, keysize of 32 bytes, vector '0123456789012345', 

==============
Client request
==============

After a successful handshake, client requests uses the following syntax:
::

  [ACTION] [TOKEN] [TARGET] DATA

* ACTION: the request. For example: COMMAND, ENGINECOMMAND,... It depends of the target server capabilites
* TOKEN: Can be used to create some "sessions". If empty, the server creates an uniq token for each requests
* TARGET: which "centreond" must execute the request. With the following option, you can execute a command to a specific server through another. The poller id is needed. If empty, the server (which is connected with the client) is the target.
* DATA: json stream. It depends of the request

For each client requests, the server get an immediate response:
::

  [ACK] [TOKEN] { "code" => x, "data" => { "message" => "xxxxx" } }

* TOKEN: a uniq ID to follow the request
* DATA: a json stream

  * 0 : OK
  * 1 : NOK

There are some exceptions for 'CONSTATUS' and 'GETLOG' requests.

=============
Core requests
=============

---------
CONSTATUS
---------

The following request gives you a table with the last ping response of "centreond" nodes connected to the server.
The command is useful to know if some pollers are disconnected.

The client request:
::

  [CONSTATUS] [] []

The server response:
::

  [ACK] [token_id] DATA

An example of the json stream:
::

  { 
    code => 1, 
    data => { 
                action => 'constatus', 
                mesage => 'ok', 
                data => {
                  last_ping => xxxx,
                  entries => {
                     1 => xxx,
                     2 => xxx,
                     ...
                  }
                }
              } 
  }

'last_ping' and 'entries' values are unix timestamp in seconds. The 'last_ping' is the date when the daemon had launched a ping broadcast to the poller connected.
'entries' values are the last time the poller had responded to the ping broadcast.

------
GETLOG
------

The following request gives you the capability to follow your requests. "centreond" protocol is asynchronous. 
An example: when you request a command execution, the server gives you a direct response and a token. These token can be used to know what happened to your command.

The client request:
::

  [GETLOG] [TOKEN] [TARGET] { code => 'xx', ctime => 'xx', etime => 'xx', token => 'xx', id => 'xx' }

At least one of the 5 values must be defined:

* code: get logs if code = value
* token: get logs if token = value
* ctime: get logs if creation time in seconds >= value
* etime: get logs if event time in seconds >= value
* id: get logs if id > value

The 'etime' is when the event had occured. The 'ctime' is when the server had stored the log in its database.

The server response:
::

  [ACK] [token_id] DATA

An example of the json stream:
::

  { 
    code => 1, 
    data => { 
                action => 'getlog', 
                mesage => 'ok', 
                result => {
                  10 => {
                    id => 10,
                    token => 'xxxx',
                    code => 1,
                    etime => 1419252684,
                    ctime => 1419252686,
                    data => xxxxx,
                  },
                  100 => {
                    id => 100,
                    token => 'xxxx',
                    code => 1,
                    etime => 1419252688,
                    ctime => 1419252690,
                    data => xxxxx,
                  },
                  ...
                }
              } 
  }

Each 'centreond' nodes store its logs. But every 5 minutes (by default), the central server gets the new logs of its connected nodes and stores it. 
A client can force a synchronization with the following request:
::

  [GETLOG] [] [target_id]

The client have to set the poller id.

------
PUTLOG
------

The request shouldn't be used by third-party program. It's commonly used by the internal modules.
The client request:
::

  [PUTLOG] [TOKEN] [TARGET] { code => xxx, etime => xxx, token => xxxx, data => { some_datas } }

===============
module requests
===============

-------------
centreond-acl
-------------

xxxxx
^^^^^

----------------
centreond-action
----------------

COMMAND
^^^^^^^

With the following request, you can execute shell commands.
A client example:
::

  [COMMAND] [] [target_id] { command => 'ls /' }

The code responses:

* x0: problem. It stopped (read the message)
* 31: command proceed
* 32: command proceed end
* 35: problem. It stopped (read the message)
* 36: command had been finished

With the code 36, you can get following attributes:
::

  { code => 36, stdout => 'xxxxx', exit_code => xxx }

ENGINECOMMAND
^^^^^^^^^^^^^

With the following request, you can submit external commands to the scheduler like "centreon-engine".
A client example:
::

  [COMMAND] [] [target_id] { command => '[1417705150] ENABLE_HOST_CHECK;host1', engine_pipe => '/var/lib/centreon-engine/rw/centengine.cmd'

The code responses:

* x0: problem. It stopped (read the message)
* 31: command proceed
* 32: command proceed end
* 35: problem. It stopped (read the message)
* 36: command had been submitted

You only have the message to get informations (it tells you if there are some permission problems or file missing).

***
FAQ
***



===============================
Which modules should i enable ?
===============================

A poller with centreond should have the following modules:

* centreond-action
* centreond-pull: if the connection to the central should be opened by the poller 

A central with centreond should have the following modules:

* centreond-acl
* centreond-action
* centreond-proxy
* centreond-cron

=================================================
I want to create a client. How should i proceed ?
=================================================

First, you must choose a language which can used zeromq library and have some knowledge about zeromq.
I recommend following scenarios:

* Create a ZMQ_DEALER
* Manage the handshake with the server. See :ref:`handshake-scenario`
* Do a request:

  * if you don't need to get the result: close the connection
  * if you need to get the result:
  
    1. get the token
    2. if you have used a target, force a synchronization with 'GETLOG'
    3. do a 'GETLOG' request with the token to get the result
    4. repeat actions 2 and 3 if you don't have a result (you should stop after X retries) 

You can see the code from 'test-client.pl'.

***************
Database scheme
***************

::

  CREATE TABLE IF NOT EXISTS `centreond_identity` (
    `id` INTEGER PRIMARY KEY,
    `ctime` int(11) DEFAULT NULL,
    `identity` varchar(2048) DEFAULT NULL,
    `key` varchar(4096) DEFAULT NULL
  );
  
  CREATE INDEX IF NOT EXISTS idx_centreond_identity_identity ON centreond_identity (identity);
  
  CREATE TABLE IF NOT EXISTS `centreond_history` (
    `id` INTEGER PRIMARY KEY,
    `token` varchar(255) DEFAULT NULL,
    `code` int(11) DEFAULT NULL,
    `etime` int(11) DEFAULT NULL,
    `ctime` int(11) DEFAULT NULL,
    `data` TEXT DEFAULT NULL
  );
  
  CREATE INDEX IF NOT EXISTS idx_centreond_history_id ON centreond_history (id);
  CREATE INDEX IF NOT EXISTS idx_centreond_history_token ON centreond_history (token);
  CREATE INDEX IF NOT EXISTS idx_centreond_history_etime ON centreond_history (etime);
  CREATE INDEX IF NOT EXISTS idx_centreond_history_code ON centreond_history (code);
  CREATE INDEX IF NOT EXISTS idx_centreond_history_ctime ON centreond_history (ctime);
  
  CREATE TABLE IF NOT EXISTS `centreond_synchistory` (
    `id` int(11) DEFAULT NULL,
    `ctime` int(11) DEFAULT NULL,
    `last_id` int(11) DEFAULT NULL
  );

  CREATE INDEX IF NOT EXISTS idx_centreond_synchistory_id ON centreond_synchistory (id);