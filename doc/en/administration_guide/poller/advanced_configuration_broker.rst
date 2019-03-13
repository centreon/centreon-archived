.. _advance_conf_broker:

=============================
Advanced poller configuration
=============================

--------------------------------
Centreon Broker and the firewall
--------------------------------

In certain cases you may not be able to initialize the Centreon Broker data flow from the
poller (or the Remote Server) to the Central Server or the Remote Server.

Centreon has, however, developed a solution for initializing the flow from the Centreon
Central Server, or from the Remote Server, to the poller.

Go to the **Configuration > Pollers > Broker configuration** menu and click on
**Centreon Broker SQL** configuration on the Central Server or Remote Server.

Go to the **Input** tab panel and add a new **TCP - IPv4** entry.

Enter the **Name** of the configuration, the TCP **Connection port** for connecting
to the poller, and the **Host to connect to**. Then **Save** your configuration.

.. image:: /_static/images/configuration/one_peer_conf_1.png
    :align: center

Go to the **Configuration > Pollers > Broker configuration** menu and click on
the **Broker module** of your poller.

On the **Output** tab panel modify the **Output 1 - IPv4** form:

1. Remove the entry for **Host to connect to**.
2. Check the **Connection port**.
3. Set **Yes** for **One peer retention** option.

.. image:: /_static/images/configuration/one_peer_conf_2.png
    :align: center

Click **Save** and generate the configuration of the affected servers.

-----------------------------------
Centreon Broker flow authentication
-----------------------------------

If you need to authenticate pollers that are sending data to the
monitoring system, you can use the Centreon Broker
authentication mechanism, which is based on X.509 certificates.

First generate a Certificate Authority (CA) certificate with OpenSSL. *ca.key*
will be the private key (stored securely), while *ca.crt* will be the
public certificate for authenticating incoming connections::

    $> openssl req -x509 -newkey rsa:2048 -nodes -keyout ca.key -out ca.crt -days 365

Now generate the certificates using the CA key::

	$> openssl req -new -newkey rsa:2048 -nodes -keyout central.key -out central.csr -days 365
	$> openssl req -new -newkey rsa:2048 -nodes -keyout poller.key -out poller.csr -days 365
	$> openssl x509 -req -in central.csr -CA ca.crt -CAkey ca.key -CAcreateserial -out central.crt -days 365 -sha256
	$> openssl x509 -req -in poller.csr -CA ca.crt -CAkey ca.key -CAcreateserial -out poller.crt -days 365 -sha256

Put *central.key*, *central.crt* and *ca.crt* on the Centreon central server
(e.g., in **/etc/centreon-broker**) and *poller.key*, *poller.crt* and
*ca.crt* on your poller.

You must now configure the Centreon Broker in order to use these files. Go to
**Configuration > Pollers > Broker configuration**. For the
*central-broker-master*, under the **Input** tab, set the following
parameters for *central-broker-master-input*:

- Enable TLS encryption = Yes
- Private key file = /etc/centreon-broker/central.key
- Public certificate = /etc/centreon-broker/central.crt
- Trusted CA's certificate = /etc/centreon-broker/ca.crt

.. image:: /_static/images/configuration/broker_certificates.png
   :align: center

As with the poller, you will have to modify the TCP output under the Output
tab with the following parameters:

- Enable TLS encryption = Yes
- Private key file = /etc/centreon-broker/poller.key
- Public certificate = /etc/centreon-broker/poller.crt
- Trusted CA's certificate = /etc/centreon-broker/ca.crt

Regenerate the configuration of the affected pollers
(**Configuration > Pollers**) and the authentication will be complete.

---------------------------
Centreontrapd Configuration
---------------------------

Poller
######

You must edit the Centreontrapd configuration file to be able to use the local SQLite database. 
Refer to the chapter: :ref:`configuration_advanced_snmptrapds`).

Remote Server
#############

Configuring the Centreontrapd process is the same as on the Central Server.

-----------------------------------------
Advanced configuration of Centreon Broker
-----------------------------------------

This section will help you understand how Centreon Broker works and how
it should be configured according to Centreon's best practices. The various
options used by Centreon Broker are described.

Overview
########

The Centreon Broker's core is a simple multiplexing engine that takes *inputs*
events and sends them to various *outputs*. Inputs are typically other
Centreon Broker instances received via TCP/IP, while outputs can be an
SQL database, other brokers, a BI/BAM engine, Centreon Map, etc.

Each input or output has a *type* that describes what it does plus several
parameters, some mandatory and others optional. Additionally,
an output can have a *failover* that will start when the output is
in an error state in order to ensure data retention.

An important distinction should be made between a standalone Centreon Broker and
a Centreon Broker installed as a Centreon Engine module. Both have the
same capabilities and support the same inputs and outputs. The
difference is that the Centreon Broker configured as a module is
automatically started when Centreon Engine starts. This broker automatically generates
the events associated with the Centreon Engine. Broker modules often only have
one output to a Centreon Broker instance acting as a concentrator.

Main configuration page
#######################

This section lists all the Centreon Broker instances configured in your infrastructure,
either in standalone or module mode. Each instance has a name, is associated
with a poller, has a number of inputs, outputs*, and *loggers*, and can be
*enabled* or *disabled*.

A *central*-type poller will have three Centreon Broker instances by default: 

- one Centreon Broker installed as a module for a Centreon Engine (called a *central-module-master*)
- one Centreon Broker acting as a stand-alone concentrator (called a *central-broker-master*)
- one Centreon Broker specialized in generating the RRD data used by graphs (called *central-rrd-master*).

The best practice is to always use a separate Centreon Broker instance to generate RRD data. This way, an issue occuring
in the RRD stack will not have any impact on your main monitoring.

As expected, the *central-module-master* has only one output and zero inputs.
Configured as a Centreon Engine module, it generates events on its own
and forwards them to the standalone Centreon Broker instance.

A poller generally has only one Centreon Broker instance configured as a Centreon Engine module.

Broker general configuration page
#################################

This section lists all the general options associated with a Centreon Broker instance.

Main options:

Poller
  The poller containing the instance.
Name
  The name of the instance.
Config file name
  The name of the configuration file used by this instance.
Retention path
  When an output is in an error state, a failover is
  launched. Failovers save data in files called *retention files*.
  These in turn are saved in the directory specified here.
  The best practice is */var/lib/centreon-broker/*.
Status
  Used to enable or disable the instance.

Log options:

Write timestamp
  If activated, each log entry is preceded by the timestamp of the time it was
  written.
  This is useful to know when an error has occured. Best practice is *Yes*.
Write thread id
  If activated, each log entry is preceded by the ID of the thread being
  executed at that instant.
  This is only used for advanced debugging purposes. Best practice is *No*.

Advanced Options:

Statistics
  Centreon Broker has an on-demand status reporting mechanism that can be
  enabled here. This is used by Centreon Web to check the status
  of the instance at any time and determine which inputs and outputs are in
  an error state and to generate various statistics on event processing.
  Best practice is *Yes*.
Correlation
  Centreon Broker has a top-level correlation mechanism.
  This should only be activated if top-level correlation has been properly
  configured in Centreon Web. In all other cases, default is *No*.
Event queue max size
  The maximum size of the in-memory queue in events.
  If the number of events in memory exceeds this number, Centreon Broker
  will start to use temporary files to prevent the broker from using too much
  memory. This, however, causes additional disk I/O. The exact number can be adjusted
  to use more or less memory. A suggested default is 50000.

If *Statistics* is enabled, on-demand status can be queried manually through
a file in */var/lib/centreon-broker/name.stats*.

Broker input configuration page
###############################

This section lists all the inputs activated for this instance of
Centreon Broker. Centreon Broker can have as many inputs as needed.

Inputs read events from a TCP connection. All inputs have the following
parameters:

Name
  The name of the input. Must be unique.
Serialization protocol
  The protocol that was used to serialize the data.
  Can be either *BBDO* or *NDO*. NDO is a legacy textual protocol with inferior
  performance, data density and security. BBDO
  is a next-generation binary protocol that is effective and secure. NDO is
  deprecated. It should never be used for a new software installation.
  Best practice is *BBDO*.
Compression
  If compression is used to serialize the data, the options are: *auto*, *yes*, or *no*. If left on *auto*, the Centreon Broker
  will detect whether compression was used during a TCP handshake
  (or assume that no compression was used for files). Default is *auto* for TCP, *no* for files.
Filter category
  The categories of events accepted by this input.
  If empty, no restriction on events accepted. If filled, only events
  of the given type will be processed. Inputs that accept data from
  the Centreon Engine Broker module should be set to only accept *Neb* events.
Connection Port
  The port that will be used for the connection. Mandatory.
Host to connect to
  This important parameter decides whether the input will
  listen or attempt to initiate a connection. If left empty, the input
  will listen on its given port. If specified, it will attempt
  to initiate a connection to the given host/port.
Enable TLS encryption
  Enables the encryption of the flow. For the encryption
  to work, the private key file, the *Public certificate* and the *Trusted CA's certificate* 
  need to be set on both ends. Default is *auto*, i.e., *no* unless
  TCP negotiation has been activated and the remote endpoint has activated encryption.
Private Key File
  The private key file used for the encryption.
Public certificate
  The public certificate used for the encryption.
Trusted CA's certificate
  The trusted CA certificate used for the encryption.
Enable negotiation
  If set to *yes*, this input will try
  to negotiate encryption and compression with the remote endpoint.
One peer retention mode
  By default, a listening input will accept any
  number of incoming connections. In *one peer retention* mode only one
  connection at a time is accepted, on a first-come first-serve basis.
  Default is *no*.

TCP *input* can either listen on a given port or
can attempt to initiate a connection if a host is given. This allows flexible
network topology.

Broker Logger configuration page
################################

This section lists all the loggers activated for this
Centreon Broker instance. A Centreon Broker can have as many loggers as needed.

For each logger, the parameters are the following:

Type
  Four types of loggers are managed by Centreon Broker:

  1. *File*: This logger will write a log into the file specified in its
     *name* parameter.
  2. *Standard*: This logger will write into the standard output if named
     *stdout* or *cout* or into the standard error output if named
     *stderr* or *cerr*.
  3. *Syslog*: This logger will write into the syslog as provided by the system, prefixed by *centreonbroker*.
  4. *Monitoring*: This logger will write in the Centreon Engine log. It should only be activated if the Centreon Broker instance is loaded by the Centreon Engine at start-up.

Name
  The name of this logger. This name must be the path of a file if the
  logger has the type *file* and either *stdout*, *cout*, *stderr* or *cerr*,
  if the logger has the type *standard*. This option is mandatory.
Configuration messages
  Should configuration messages be logged?
  Configuration messages are one-time messages that pop up when Centreon Broker
  is started. Default is *Yes*.
Debug messages
  Should debug messages be logged?
  Debug messages are messages used to debug Broker behavior. They are
  extremely verbose and should not be used in a production environment.
  Default is *No*.
Error messages
  Should error messages be logged?
  Error messages are messages logged when a runtime error occurs.
  They are generally important. Default is *Yes*.
Informational messages
  Should informational messages be logged?
  Informational messages are used to provide information
  on a specific subject. They are somewhat verbose. Default is *No*.
Logging level
  The level of the verbosity accepted by this logger.
  The higher the verbosity, the more messages will be logged.
  Default is *Base*.

Additionally, the *File* type has the following parameter:

Max file size
  The maximum size of a log file in bytes.
  When the file has reached its limit, the old data will be overwritten
  in a round-robin fashion.

A broker will usually have at least one *file* logger which will log
configuration and error messages. Others can be configured freely.
A maximal logger (every category set to *Yes* and logging level set to *Very detailed*)
is valuable to debug some issues, but be warned that it will quickly generate
a very large amount of data.

Broker output configuration page
################################

This section lists all the outputs activated for this 
Centreon Broker instance. Centreon Broker can have as many outputs as needed.

For each output, the parameters are:

Type
  There are several types of outputs managed by the Centreon Broker:

  1. *TCP - IPV4* and *TCP - IPV6*: This output forwards data to another
     server, another Centreon Broker or Centreon Map.
  2. File: Writes data into a file.
  3. RRD: Generates RRD data from performance data.
  4. Storage: Writes metrics into the database and generates performance data.
  5. SQL: Writes the real-time status into Centreon's database.
  6. Dumper Reader: Reads from a database when Broker is asked to synchronize databases.
  7. Dumper Writer: Writes into a database when Broker is asked to synchronize databases.
  8. BAM Monitoring: Generates BAM data from raw events and updates real-time BAM status.
  9. BAM Reporting: Writes long-term BAM logs that can then be used by BI.

Failover
  A *failover* is an output that will be started when in
  an error state. Examples are TCP connections "gone haywire" or a MySQL server
  suddenly disconnecting, etc.
  By default, each output has an automatic failover that will
  always store data in retention files and replay it when the primary
  output recovers from its error state. This is desirable 99% of the
  time. Alternatively, you can specify another output that will act
  as a failover if needed.
Retry interval
  When the output is in an error state, this parameter
  controls the amount of time the output will wait before retrying.
  Default is one attempt every 30 seconds.
Buffering timeout
  When this output is in an error state, Centreon Broker
  will wait a specified time before launching the failover. This is mainly
  useful if Centreon Broker should wait for another software to
  initialize before activating its failover. In all other cases, this parameter should
  not be used. Default is 0 seconds.
Filter category
  The categories of events accepted by this output.
  If left empty, no restriction on events accepted. If filled, only events
  of the given type will be processed. The exact best practices are output
  specific:

  1. *BAM Reporting* should only accept *BAM* events.
  2. *Dump Writer* should only accept *dumper* events.
  3. *RRD* should only accept *storage* events.

  In all other cases, no restriction should be configured.

Events generated by an output are reinjected into Centreon Broker's event
queue.

Some outputs only work when consuming data generated by another output.
An RRD output consumes data from a storage output, a *dumper writer* output consumes
data from a *dumper reader*, and a *BAM reporting* output consumes data
from a *BAM monitoring* output.

Centreon Web needs at least an active output *SQL* ouput to activate its real-time
monitoring capabilities. The storage and RRD outputs are needed
to activate Centreon Web metric plotting. The BAM monitoring output
is needed for real-time BAM data and the BAM reporting output for
BI reports.

Due to the fully distributed nature of Centreon Broker, producer and consumer
outputs can be located on logically or physically different instances as
long as they are connected to each other.

**Important**: Centreon Web 2.x features two databases, the configuration
database and the real-time database. Those are respectively called *centreon*
and *centreon-storage*. Different outputs expect may different databases
in their configuration.

==============  =================
Output Type     Expected database
==============  =================
SQL             centreon-storage
Storage         centreon-storage
Dumper Reader   centreon
Dumper Writer   centreon
BAM Monitoring  centreon
BAM Reporting   centreon-storage
==============  =================

TCP outputs
===========

TCP outputs forward events to a remote endpoint. As with TCP inputs,
TCP outputs can either listen on a given port or attempt to
initiate a connection if a host parameter is given.
This allows for flexible network topology.

*TCP*-type outputs have the following parameters:

Serialization protocol
  The protocol used to serialize the data.
  Can be either *BBDO* or *NDO*. NDO is an legacy textual protocol with inferior
  performance, data density and security. BBDO
  is a next-generation binary protocol that is effective and secure. NDO is
  deprecated. It should never be used for new installations.
  Best practice is *BBDO*.
Enable negotiation
  If *yes*, this output will try
  to negotiate encryption and compression with the remote endpoint.
Connection Port
  Port used for the connection. Mandatory.
Host to connect to
  This key parameter decides whether the input will
  listen or attempt to initiate a connection. If left empty, the input
  will listen on its given port. If specified, it will attempt
  to initiate a connection to the given host/port.
Enable TLS encryption
  Enables the encryption of the flow. For the encryption
  to work, the private key file, *Public certificate* and *Trusted CA's certificate* 
  need to be set on both ends. Default is *auto*, i.e., *no* unless
  TCP negotiation has been activated and the remote endpoint has activated encryption.
Private Key File
  The private key file used for the encryption.
Public certificate
  The public certificate used for the encryption.
Trusted CA's certificate
  The trusted CA certificate used for the encryption.
One peer retention mode
  By default, a listening input will accept any
  number of incoming connections. In *one peer retention* mode only one
  connection at a time is accepted, on a first-come first-serve basis.
  Default is *no*.
Compression
  If compression should be used to serialize the data.
  Can be *auto*, *yes*, or *no*. If left on *auto* Centreon Broker
  will detect if compression is supported by the endpoint during a TCP
  negotiation. Default is *auto* for TCP.
Compression Level
  The level of compression that should be used, from 1 to 9.
  Default (or if not filled) is 6. The higher the compression level is,
  the higher the compression will be at the expense of processing power.
Compression Buffer
  The size of the compression buffer that should be used.
  Best practice is *0* or nothing.

File outputs
============

File *outputs* send events into a file on the disk. Additionally, they have
the capability of replaying the data of this file if used as a failover
output. Most file outputs will be used as failovers.

*File* type outputs have the following parameters:

Serialization protocol
  The protocol that was used to serialize the data.
  Can be either *BBDO* or *NDO*. NDO is a legacy textual protocol with inferior
  performance, data density and security. BBDO
  is a next-generation binary protocol that is effective and secure. NDO is
  deprecated. It should never be used for new installations.
  Best practice is *BBDO*.
File path
  The path of the file being written to.
Compression
  If compression should be used to serialize the data.
  Can be *auto*, *yes*, or *no*. *auto* is equal to *no* for files.
Compression Level
  The level of compression to be used, from 1 to 9.
  Default (or if not filled) is 6. The higher the compression level is,
  the higher the compression will be at the expense of processing power.
Compression Buffer
  The size of the compression buffer to be used.
  Best practice is *0*.

RRD outputs
===========

*RRD* outputs generate RRD data (used by Centreon Web to generate graphs)
from metrics data generated by a storage output. The best practice is to
isolate this output on its own Centreon Broker instance to ensure
that an issue in the RRD stack will not have any impact on the main Centreon Broker instance.

*RRD*-type outputs have the following parameters:

RRD file directory for metrics
  The directory where the RRD files of the
  metrics will be written.
  A recommended default is */var/lib/centreon/metrics/*.
RRD file directory for status
  The directory where the RRD files of the
  status will be written.
  A recommended default is */var/lib/centreon/status/*
TCP port
  The port used by RRDCached, if RRDCached has been configured on
  this server. If not, leave empty.
Unix socket
  The Unix socket used by RRDCached, if RRDCached has been
  configured on this server. If not, leave empty.
Write metrics
  Should RRD metric files be written? Default is *yes*.
Write status
  Should RRD status files be written? Default is *yes*.

Storage Outputs
===============

Perfdata storage outputs save metric data into a database and generate RRD
data used by the RRD output. This output usually generates multiple
queries and is very performance intensive. If Centreon Broker is slow, try adjusting 
the *maximum queries per transaction* parameter to optimize processing speed.

This output can be tasked to rebuild RRD data from a database of stored
metric data. This is usually a slow, costly process, though you can simultaneously
process new metric data at a reduced speed.

*Storage*-type outputs have the following parameters:

DB Type
  The type of the database being accessed.
  MariaDB is a state-of-the-art database that has been extensively tested
  by Centreon. We advise using MariaDB.
DB Port
  The port of the database being accessed.
DB User
  The user account for connecting to this database.
DB Name
  The name of the database. In Centreon terms, this is the database
  containing the real-time monitoring data, generally called
  *centreon-storage*.
DB Password
  The password used by the output to connect to this database.
Maximum queries per transaction
  This parameter is used to batch several
  queries in large transactions. This allows for improved performance but
  can generate latency if an insufficient number of queries are generated to fill those batches.
  The default is 20000 queries per transaction. If you have a low load and
  unexpectedly high latency, try lowering this number. If you have a high
  load and high latency, try raising it.
Transaction commit timeout
  Number of seconds allowed before
  a forced commit is made. Default is infinite. If you have a low
  load and unexpectedly high latency, try 5 seconds.
Replication enabled
  Should Centreon Broker check that the replication status
  of this database is complete before trying to insert data? Only useful
  if replication is enabled for this database.
Rebuild check interval in seconds
  The number of seconds between each rebuild check. Default 300 seconds.
Store in performance data in data_bin
  Should this output save the metric
  data in the database? Default is *yes*. If *no*, this output will generate
  RRD data without saving them into the database, making a rebuild impossible.
Insert in index data
  Should new index data be inserted into the database? Default is *no*.
  This should never be modified unless prompted by Centreon Support or
  explicitly advised in the documentation.

SQL outputs
===========

*SQL* outputs save real-time status data into the real-time database
used by Centreon Web. This is the most important output for the
operation of Centreon Web.

Moreover, this output has a *garbage collector* that will clean old data from
the database occasionally. This is an optional process, as old data is marked
*disabled*, and can actually be useful to keep for debugging purpose.

*SQL*-type outputs have the following parameters:

DB Type
  The type of the database being accessed.
  MariaDB is a state-of-the-art database that has been extensively tested
  by Centreon. We advise using MariaDB.
DB Port
  The port of the database being accessed.
DB User
  The user account for connecting to this database.
DB Name
  The name of the database. In Centreon terms, this is the database
  containing the real-time monitoring data, generally called
  *centreon-storage*.
DB Password
  The password used by the output to connect to this database.
Maximum queries per transaction
  This parameter is used to batch several
  queries in large transactions. This allows for improved performance but
  can generate latency if an insufficient number of queries are generated to fill those batches.
  The default is 20000 queries per transaction. If you have a low load and
  unexpectedly high latency, try lowering this number. If you have a high
  load and high latency, try raising this number.
Transaction commit timeout
  Number of seconds allowed before
  a forced commit is made. Default is infinite. If you have a low
  load and unexpectedly high latency, try 5 seconds.
Replication enabled
  Should Centreon Broker check that the replication status
  of this database is complete before trying to insert data? Only useful
  if replication is enabled for this database.
Cleanup check interval
  Number of seconds between each run of the garbage
  collector "cleaning" out old data in the database. Default is never.
Instance timeout
  Number of seconds before an instance is marked as
  *unresponding* and all of its hosts and services marked as *unknown*.
  Default is 300 seconds.

Lua outputs
===========

*Lua* outputs send metrics information into a script by a key-value system.
The Lua script should reside on your server.

Path
  The path of the Lua script in your server.
Filter category
  The categories of events accepted by this output. If empty, no restriction on events is accepted.
  If specified, only events of the given type will be processed. Outputs that accept data from
  Centreon Engine's Broker module should be set to only accept *Neb* events.

*Lua parameter*

Type
  Type of metric value.
Name/Key
  Name of metric value.
Value
  Value of metric.

Dumper reader/writer
====================

A *dumper reader/writer* pair is used to synchronize part of a database
between two instances of Centreon Broker. In the future we will provide an
extensive synchronization mechanism, but today this system is mainly used to
synchronize BAs for the BAM Poller Display mechanism.

The BAM Poller Display configuration documentation explains how to properly
configure these outputs.

*Dumper Reader*-type and *Dumper Writer*-type outputs have the following parameters:

DB Type
  The type of the database being accessed.
  MariaDB is a state-of-the-art database that has been extensively tested
  by Centreon. We advise using MariaDB.
DB Port
  The port of the database being accessed.
DB User
  The user account for connecting to this database.
DB Name
  The name of the database. In Centreon terms, this is the database
  containing the real-time monitoring data, generally called
  *centreon-storage*.
DB Password
  The password used by the output to connect to this database.
