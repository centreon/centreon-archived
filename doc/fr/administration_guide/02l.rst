##################
Configuring Broker
##################

This section aims to help user understand how Centreon Broker works and how
it should be configured. It references Centreon's best practices and
describe the various options used by Centreon Broker.

****************
General Overview
****************

Centreon Broker is at is core a simple multiplexing engine. It takes events
from *Inputs* and send them to various *Outputs*. *Inputs* are typically other
instances of Centreon Broker over TCP/IP, while *Outputs* can be a
SQL database, other brokers, a BI/BAM engine, Centreon Map, etc.

Each *Input* or *Output* has a *type* that describe what it does and several
parameters, some of them mandatory and other optional. Additionally,
an *Output* can have a *Failover* that will start when the *Output* is
in an error state to allow retention of data.

An important distinction to make is the standalone Centreon Broker versus
a Centreon Broker installed as Centreon Engine's module. Both have the
exact same capabilities and support the same *Inputs* and *Outputs*. The
difference is that Centreon Broker configured as a module will be
automatically started when Centreon Engine starts and automatically generates
the events associated to this Centreon Engine. Often, those modules only have
one *Output* to an instance of Centreon Broker acting as a concentrator.

***********************
Main Configuration Page
***********************

This section lists all the instances of Centreon Broker configured in your park,
either in standalone or module mode. Each instance has a name, is associated
with a poller, has a number of *Inputs*, *Outputs*, and *Loggers*, and can be
'enabled' or 'disabled'.

A poller of type 'Central' will have three instances of Centreon Broker by
default. One Centreon Broker installed as a module for Centreon Engine
(here called *central-module-master*), one Centreon Broker acting as a
stand-alone concentrator (here called *central-broker-master*) and one
Centreon Broker specialized in generating the RRD data used by the graphs
(here called *central-rrd-master*). A best practice is to always use
a separate instance of Centreon Broker to generate RRD data. This way, an issue
in the RRD stack will not cause any issue in your main monitoring.

As expected, *central-module-master* has only one *Output* and zero *Input*.
Configured as a module to Centreon Engine, it generates events on its own
and forward them to the standalone instance of Centreon Broker.

A poller generally only have an instance of Centreon Broker,
configured as a module for Centreon Engine.

*********************************
Broker General Configuration Page
*********************************

This section lists all the general options associated with an instance of
Centreon Broker.

Main options:

Poller
  The poller where this instance lives.
Name
  The name of this instance.
Config file name
  The name of the configuration file used by this instance.
Retention path
  When an *Output* is in an error state, a *Failover* is
  launched. *Failovers* save data in files called retention files.
  Those in turn are saved in the directory specified here.
  Best practice is '/var/lib/centreon-broker/'
Status
  This is used to enable or disable this instance.

Log options:

Write timestamp
  If activated, each log entry is preceded by the timestamp of the time it was
  written down.
  This is useful to know when an error occured. Best practice is 'Yes'.
Write thread id
  If activated, each log entry is preceded by the ID of the thread being
  executed at this instant.
  This is only useful for advanced debugging purpose. Best practice is 'No'.

Advanced Options:

Statistics
  Centreon Broker has a mechanism of on-demand status reporting that can be
  enabled here. This is used by Centreon Web to check the status
  of this instance at any time, to know which *Inputs* and *Outputs* are in
  an error state, and to generate various statistics on event processing.
  Best practice is 'Yes'.
Correlation
  Centreon Broker has a mechanism of top-level correlation.
  This should only be actived if top-level correlation has been properly
  configured in Centreon Web. In all other cases, default to 'No'.
Event queue max size
  The max size of the in-memory queue, in events.
  If the number of events in memory exceeds this number, Centreon Broker
  will start to use 'temporary files' to prevent Broker from using too much
  memory at the cost of additional disk I/O. The exact number can be tweaked
  to use more or less memory. A good default is '50000'.

If 'Statistics' is enabled, on-demand status can be queried manually through
a file placed in /var/lib/centreon-broker/*name*.stats.

*******************************
Broker Input Configuration Page
*******************************

This section lists all the *Inputs* activated for this instance of
Centreon Broker. Centreon Broker can have as many *Inputs* as needed.

Inputs read events from a TCP connection. All *Inputs* have the following
parameters:

Name
  The name of the input. Must be unique.
Serialization protocol
  The protocol that was used to serialize the data.
  Can be either 'BBDO' or 'NDO'. NDO is an old textual protocol that suffers
  from very poor performance, poor density of data, and poor security. BBDO
  is a next-gen binary protocol that is performant and secure. NDO is
  deprecated. It should never be used in new installation.
  Best practice is 'BBDO'.
Compression
  If compression was used to serialize the data.
  Can be 'auto', 'yes', or 'no'. If left on 'auto' Centreon Broker
  will detect if compression was used while doing a TCP handshake
  (or assume no compression was used for files). Default to 'auto' for TCP,
  'no' for files.
Filter category
  The categories of events accepted by this *Input*.
  If empty, no restriction on events accepted. If filled, only events
  of the given type will be processed. *Input* that accept data from
  Centreon Engines' Broker module should be set to accept only 'Neb' events.
Connection Port
  Which port will be used for the connection. Mandatory.
Host to connect to
  This important parameter will decide if this input will
  listen or attempt to initiate a connection. Left empty, this input
  will listen on its given port. If filled, this input will attempt
  to initiate a  connection to the given host/port.
Enable TLS encryption
  Enable the encryption of the flux. For the encryption
  to work, the private key file, the public certificate and the trusted CA's
  certificate need to be set on both end. Default to 'auto', i.e 'no' unless
  TCP negociation has been activated and the remote endpoint has activated
  encryption.
Private Key File
  The private key file used for the encryption.
Public certificate
  The public certificate used for the encryption.
Trusted CA's certificate
  The trused CA certificate used for the encryption.
Enable negociation
  Enable negociation. If 'yes', this *Intput* will try
  to negociate encryption and compression with the remote endpoint.
One peer retention mode
  By default, a listening input will accept any
  number of incoming connections. In 'one peer retention' mode only one
  connection is accepted at the same time, on a first-come first-serve basis.
  Default to 'no'.

To reiterate, TCP *Input* can either listen on a given port or
can attempt to initiate a connection if a host is given. This allow flexible
network topology.

********************************
Broker Logger Configuration Page
********************************

This section lists all the loggers activated for this instance of
Centreon Broker. Centreon Broker can have as many loggers as needed.

For each logger, the parameters are:

Type
  4 types of loggers are managed by Centreon Broker.

  1. 'File': This logger will write its log into the file specified into its
     'name' parameter.
  2. 'Standard': This logger will write into the standard output if named
     'stdout' or 'cout' or into the standard error output if named
     'stderr' or 'cerr'.
  3. 'Syslog': This logger will write into the syslog as provided by the system, prefixed by 'centreonbroker'.
  4. 'Monitoring': This logger will write into the log of Centreon Engine. It can only be activated if this instance of Centreon Broker is a module.

Name
  The name of this logger. This name must be the path of a file if the
  logger has the type 'File' or 'stdout', 'cout', 'stderr' or 'cerr'
  if the logger has the type 'Standard'. This option is mandatory.
Configuration messages
  Should configuration messages be logged?
  Configuration messages are one-time messages that pop-up when Centreon Broker
  is started. Default is 'Yes'.
Debug messages
  Should debug messages be logged?
  Debug messages are messages used to debug Broker's behavior. They are
  extremely verbose and should not be used in a production environment.
  Default is 'No'.
Error messages
  Should error messages be logged?
  Error messages are messages logged when a runtime error occurs.
  They are generally important. Default is 'Yes'.
Informational messages
  Should informational messages be logged?
  Informational messages are messages that are used to provide an information
  on a specific subject. They are somewhat verbose. Default is 'No'.
Logging level
  The level of the verbosity accepted by this logger.
  The higher the verbosity, the more messages will be logged.
  Default to 'Base'.

Additionally, the type 'File' has the following parameter:

Max file size
  The maximum size of log file in bytes.
  When the file has reached its limit, old data will be overwritten
  in a round robin fashion.

A Broker will usually have at least one 'File' logger which will log
Configuration and Error messages. Others can be configured freely.
A maximal logger (every category to 'Yes' and logging level to 'Very detailed')
is valuable to debug some issues, but be warned that it will generate
a very large amount of data quickly.

********************************
Broker Output Configuration Page
********************************

This section lists all the *Outputs* activated for this instance of
Centreon Broker. Centreon Broker can have as many *Outputs* as needed.

For each *Outputs*, the parameters are:

Type
  There is a several types for *Outputs* managed by Centreon Broker.

  1. 'TCP - IPV4' and 'TCP - IPV6': This *Output* forwards data to another
     server, either another Centreon Broker or Centreon Map.
  2. File: This *Output* write data into a file.
  3. RRD: This *Output* will generate RRD data from performance data.
  4. Storage: This *Output* will write metrics into the database and generate performance data.
  5. SQL: This *Output* will write real time status into Centreon's database.
  6. Dumper Reader: This *Output* will read from a database when Broker is asked to synchronize databases.
  7. Dumper Writer: This *Output* will write into a database when Broker is asked to synchronize databases.
  8. BAM Monitoring: This *Output* will generate BAM data from raw events and update real time BAM status.
  9. BAM Reporting: This *Output* will write long term BAM logs that can then be used by BI.

Failover
  A *Failover* is an *Output* that will be started when this *Output*
  is in error state. Example are TCP connections gone haywire, MySQL server
  suddenly disconnecting, etc.
  By default, each *Output* has an automatic *Failover* that will
  automatically store data in retention files and replay it when the primary
  *Output* recover from its error state. This is what you want in 99% of the
  case. Otherwhise, you can specify here another *Output* that will act
  as a *Failover* if this is what you need.
Retry interval
  When this *Output* is in error state, this parameter
  control how much time the *Output* will wait before retrying.
  Default is one attempt every 30 seconds.
Buffering timeout
  When this *Output* is in error state, Centreon Broker
  will wait this much time before launching the *Failover*. This is mainly
  useful if you want to make Centreon Broker wait for another software to
  initialize before activating its *Failover*. In all other cases, this should
  not be used. Default is 0 seconds.
Filter category
  The categories of events accepted by this *Output*.
  If empty, no restriction on events accepted. If filled, only events
  of the given type will be processed. The exact best practices are *Output*
  specific.

  1. 'BAM Reporting' should only accept 'Bam' events.
  2. 'Dump Writer' should only accept 'Dumper' events.
  3. 'RRD' should only accept 'Storage' events.

  In all other cases, no restriction should be configured.

Events generated by an *Output* are reinjected into Centreon Broker's event
queue.

Some *Outputs* only works when consuming data generated by another *Output*.
A 'RRD' *Output* consumes data from a Storage *Output*, a 'Dumper Writer' consumes
data from a 'Dumper Reader', and a 'BAM Reporting' *Output* consumes data
from a 'BAM Monitoring' *Output*.

Centreon Web needs at least an active *Output* 'SQL' to activate its real time
monitoring capabilities. The *Outputs* 'Storage' and 'RRD' are needed
to activate Centreon Web metric plotting. The *Output* 'BAM Monitoring'
is needed for real time BAM data and the *Output* 'BAM Reporting' for
BI report.

Due to the fully distributed nature of Centreon Broker, producer and consumer
*Outputs* can be located on logically or physically different instances, as
long as they are connected to each other.

**Important**: Centreon Web 2.x features two databases, the configuration
database and the real time database. Those are respectively called 'centreon'
and 'centreon-storage'. Different *Outputs* expect different database
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

===========
TCP Outputs
===========

TCP *Outputs* forward events to a a remote endpoint. As with TCP *Inputs*,
TCP *Output* can either listen on a given port or can attempt to
initiate a connection if a host parameter is given.
This allow flexible network topology.

*Outputs* of type 'TCP' have the following parameters:

Serialization protocol
  The protocol that will be used to serialize the data.
  Can be either 'BBDO' or 'NDO'. NDO is an old textual protocol that suffers
  from very poor performance, poor density of data, and poor security. BBDO
  is a next-gen binary protocol that is performant and secure. NDO is
  deprecated. It should never be used in new installation.
  Best practice is 'BBDO'.
Enable negociation
  Enable negociation. If 'yes', this *Output* will try
  to negociate encryption and compression with the remote endpoint.
Connection Port
  Which port will be used for the connection. Mandatory.
Host to connect to
  This important parameter will decide if this *Output* will
  listen or attempt to initiate a connection. Left empty, this *Output*
  will listen on its given port. If filled, this *Output* will attempt
  to initiate a  connection to the given host/port.
Enable TLS encryption
  Enable the encryption of the flux. For the encryption
  to work, the private key file, the public certificate and the trusted CA's
  certificate need to be set on both end. Default to 'auto', i.e 'no' unless
  TCP negociation has been activated and the remote endpoint has activated
  encryption.
Private Key File
  The private key file used for the encryption.
Public certificate
  The public certificate used for the encryption.
Trusted CA's certificate
  The trused CA certificate used for the encryption.
One peer retention mode
  By default, a listening *Output* will accept any
  number of incoming connections. In 'one peer retention' mode only one
  connection is accepted at the same time, on a first-come first-serve basis.
  Default to 'no'.
Compression
  If compression should be used to serialize the data.
  Can be 'auto', 'yes', or 'no'. If left on 'auto' Centreon Broker
  will detect if compression is supported by the endpoint during a TCP
  negociation. Default to 'auto' for TCP.
Compression Level
  The level of compression that should be used, from 1 to 9.
  Default (or if not filled) is 6. The higher the compression level is,
  the higher the compression will be at the cost of processing power.
Compression Buffer
  The size of the compression buffer that should be used.
  Best practice is '0' or nothing.

============
File Outputs
============

File *Outputs* send events into a file on the disk. Additionally, they have
the capability of replaying the data of this file if used as a *Failover*
*Output*. Most 'File' *Outputs* will be used as *Failovers*.

*Outputs* of type 'File' have the following parameters:

Serialization protocol
  The protocol that will be used to serialize the data.
  Can be either 'BBDO' or 'NDO'. NDO is an old textual protocol that suffers
  from very poor performance, poor density of data, and poor security. BBDO
  is a next-gen binary protocol that is performant and secure. NDO is
  deprecated. It should never be used in new installation.
  Best practice is 'BBDO'.
File path
  The path of the file being written to.
Compression
  If compression should be used to serialize the data.
  Can be 'auto', 'yes', or 'no'. 'auto' is equal to 'no' for files.
Compression Level
  The level of compression that should be used, from 1 to 9.
  Default (or if not filled) is 6. The higher the compression level is,
  the higher the compression will be at the cost of processing power.
Compression Buffer
  The size of the compression buffer that should be used.
  Best practice is '0' or nothing.

===========
RRD Outputs
===========

RRD *Outputs* generate RRD data (used by Centreon Web to generate graphs)
from metrics data generated by a 'Storage' *Output*. Best practice is to
isolate this *Output* on its own instance of Centreon Broker to ensure
that an issue in the RRD stack will not have any effect on the main instance
of Centreon Broker.

*Outputs* of type 'RRD' have the following parameters:

RRD file directory for metrics
  The directory where the RRD files of the
  metrics will be written.
  A good default is /var/lib/centreon/metrics/.
RRD file directory for statuses
  The directory where the RRD files of the
  statuses will be written.
  A good default is /var/lib/centreon/statuse/
TCP port
  The port used by RRDCached, if RRDCached has been configured on
  this server. If not, nothing.
Unix socket
  The unix socket used by RRDCached, if RRDCached has been
  configured on this server. If not, nothing.
Write metrics
  Should RRD metric files be written? Default 'yes'.
Write status
  Should RRD status files be written? Default 'yes'.

===============
Storage Outputs
===============

Perfdata storage *Outputs* save metric data into a database and generate RRD
data used by the 'RRD' *Output*. This *Output* usually generates a lot of
queries and is very performance intensive. If Centreon Broker is slow, tweaking
the Maximum Queries Per Transaction parameter of this *Output* is the first
optimization to attempt.

This *Output* can be tasked to rebuild 'RRD' data from a database of stored
metric data. This is usually a costly, slow process, during which it is still
able to process new metric data, though not as quickly.

*Outputs* of type 'Storage' have the following parameters:

DB Type
  The type of the database being accessed.
  MariaDB is a state-of-the-art database that has been extensively tested
  with Centreon. We advice the use of MariaDB.
DB Port
  The port of the database being accessed.
DB User
  The user used by this *Output* to connect to this database.
DB Name
  The name of this database. In Centreon term, this is the database
  containing the real-time monitoring data, generally called
  'centreon-storage'.
DB Password
  The password used by this *Output* to connect to this database.
Maximum queries per transaction
  This parameter is used to batch several
  queries in large transaction. This allow fine performance tuning but
  can generate latency if not enough queries are generated to fill those batches.
  The Default is 20000 queries per transaction. If you have very low load and
  unexpectedly high latency, try lowering this number. If you have a very high
  load and high latency, try raising this number.
Transaction commit timeout
  How many seconds are allowed to pass before
  a forced commit is made. Default is infinite. If you have very low
  load and unexpectedly high latency, try 5 seconds.
Replication enabled
  Should Centreon Broker check that the replication status
  of this database is complete before trying to insert data in it? Only useful
  if replication is enabled for this database.
Rebuild check interval in seconds
  The amount of seconds between each rebuild check. Default 300 seconds.
Store in performance data in data_bin
  Should this *Output* saves the metric
  data in the database? Default 'yes'. If 'no', this *Output* will generate
  RRD data without saving them into the database, making a rebuild impossible.
Insert in index data
  Insert new ids into the database. Default 'no'.
  This should never be modified unless prompted by Centreon Support or
  explicitely written down into a documentation.

===========
SQL Outputs
===========

SQL *Outputs* save real time status data into the real time database
used by Centreon Web. This is the most important *Output* for the
operation of Centreon Web.

Moreover, this *Output* has a garbage collector that will clean old data from
the database occasionally. This is an optional process, as old data is marked
'disabled', and can actually be useful to keep around for debugging purpose.

*Outputs* of type 'SQL' have the following parameters:

DB Type
  The type of the database being accessed.
  MariaDB is a state-of-the-art database that has been extensively tested
  with Centreon. We advice the use of MariaDB.
DB Port
  The port of the database being accessed.
DB User
  The user used by this *Output* to connect to this database.
DB Name
  The name of this database. In Centreon term, this is the database
  containing the real-time monitoring data, generally called
  'centreon-storage'.
DB Password
  The password used by this *Output* to connect to this database.
Maximum queries per transaction
  This parameter is used to batch several
  queries in large transaction. This allow fine performance tuning but
  can generate latency if not enough queries are generated to fill those batches.
  The Default is 20000 queries per transaction. If you have very low load and
  unexpectedly high latency, try lowering this number. If you have a very high
  load and high latency, try raising this number.
Transaction commit timeout
  How many seconds are allowed to pass before
  a forced commit is made. Default is infinite. If you have very low
  load and unexpectedly high latency, try 5 seconds.
Replication enabled
  Should Centreon Broker check that the replication status
  of this database is complete before trying to insert data in it? Only useful
  if replication is enabled for this database.
Cleanup check interval
  How many seconds between each run of the garbage
  collector cleaning old data in the database? Default is never.
Instance timeout
  How many seconds before an instance is marked as
  'unresponding' and all of its hosts and services marked as 'unknown'.
  Default is 300 seconds.

===========
Lua Outputs
===========

Lua *Outputs* send metrics information into a script by a key-value system.
The Lua script should be on your server.

Path
  The path of the Lua script in your server.
Filter category
  The categories of events accepted by this Output. If empty, no restriction on events accepted.
  If filled, only events of the given type will be processed. Outputs that accept data from
  Centreon Engine's Broker module should be set to accept only ‘Neb’ events.

*Lua parameter*

Type
  Type of the metric value.
Name/Key
  Name of the metric value.
Value
  Value of the metric.

====================
Dumper Reader/Writer
====================

A Dumper Reader/Writer pair is used to synchronize part of a database
between two instances of Centreon Broker. In the future there will be an
extensive synchronization mechanism, but today it is mainly used to
synchronize BA for the BAM Poller Display mechanism.

The BAM Poller Display configuration documentation explains how to properly
configure those *Outputs*.

*Outputs* of type 'Dumper Reader' and 'Dumper Writer' have the following parameters:

DB Type
  The type of the database being accessed.
  MariaDB is a state-of-the-art database that has been extensively tested
  with Centreon. We advice the use of MariaDB.
DB Port
  The port of the database being accessed.
DB User
  The user used by this *Output* to connect to this database.
DB Name
  The name of this database. In Centreon term, this is the database
  containing the configuration data, generally called 'centreon'.
DB Password
  The password used by this *Output* to connect to this database.
