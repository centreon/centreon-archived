===============
Centreon broker
===============

Overview
--------

Object name: **CENTBROKERCFG**


Show
----

In order to list available Centreon Broker CFG, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a show 
  config id;config name;instance
  1;Central CFG;Central
  2;Sattelite CFG;Sattelite
  [...]

Columns are the following:

======= ===========================================
Order	Description
======= ===========================================
1	ID

2	Name of configuration

3	Instance that is linked to broker cfg
======= ===========================================


Add
---

In order to add a Centreon Broker CFG, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a add -v "broker cfg for poller test;Poller test" 


Required fields are:

======= =========================================
Order	Description
======= =========================================
1	    Name of configuration

2	    Instance that is linked to broker cfg
======= =========================================


Del
---

If you want to remove a Centreon Broker CFG, use the **DEL** action. The Name is used for identifying the configuration to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a del -v "broker cfg for poller test" 


Setparam
--------

If you want to change a specific parameter of a Centreon Broker configuration, use the **SETPARAM** action. The configuration name is used for identifying the configuration to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a setparam -v "broker cfg for poller test;name;new broker cfg name" 

Arguments are composed of the following columns:

======== =========================================
Order	 Column description
======== =========================================
1	     Name of Centreon Broker configuration

2	     Parameter name

3	     Parameter value
======== =========================================

Parameters that you may change are:

======================== ==================================================
Column	                 Description
======================== ==================================================
filename                 Filename of configuration (.xml extension)

name	                 Name of configuration

instance                 Instance that is linked to Centreon Broker CFG

event_queue_max_size     Event queue max size (when number is reached,
                         temporary output will be used).
======================== ==================================================


Listinput, Listoutput, Listlogger, Listcorrelation, Listtemporary and Liststats
----------------------------------------------------------------------------------

If you want to list specific input output types of Centreon Broker, use one of the following commands:
listinput
listoutput
listlogger
listcorrelation
liststats

Example::

   [root@centreon ~]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a listoutput -v "broker cfg for poller test" 
   id;name
   1;Storage
   2;RRD
   3;PerfData

Columns are the following :

======= ============
Column	Description
======= ============
ID	    I/O ID
Name	I/O Name
======= ============

Getinput, Getoutput, Getlogger, Getcorrelation, Gettemporary and Getstats
-------------------------------------------------------------------------

In order to get parameters of a specific I/O object, use one of the following commands:
 - getinput
 - getoutput
 - getlogger
 - getcorrelation
 - getstats

Example::

   [root@centreon ~]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a getoutput -v "broker cfg for poller test;3" 
   parameter key;parameter value
   db_host;localhost
   db_name;centreon_storage
   db_password;centreon
   db_port;3306
   db_type;mysql
   db_user;centreon
   failover;15552000
   interval;60
   length;
   name;PerfData
   type;storage

The ID is used for identifying the I/O to get.

Columns are the following :

======== ===========================
Order	 Description
======== ===========================
1	 Parameter key of the I/O

2	 Parameter value of the I/O
======== ===========================


Addinput, Addoutput, Addlogger, Addcorrelation, Addtemporary and Addstats
-------------------------------------------------------------------------

In order to add a new I/O object, use one of the following commands:
 - **ADDINPUT**
 - **ADDOUTPUT**
 - **ADDLOGGER**
 - **ADDCORRELATION**
 - **ADDTEMPORARY**
 - **ADDSTATS**

Example::

   [root@centreon ~]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a addlogger -v "broker cfg for poller test;/var/log/centreon-broker/central-module.log;file" 
   [root@centreon ~]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a listlogger -v "broker cfg for poller test" 
   id;name
   1;/var/log/centreon-broker/central-module.log


Arguments are composed of the following columns:

======== ============================
Order	 Column description
======== ============================
1	 Name of Centreon Broker CFG

2	 Name of the I/O object

3	 Nature of I/O object
======== ============================


Delinput, Deloutput, Dellogger, Delcorrelation, Deltemporary and Delstats
-------------------------------------------------------------------------

In order to remove an I/O object from the Centreon Broker configuration, use one of the following commands:
 - **DELINPUT**
 - **DELOUTPUT**
 - **DELLOGGER**
 - **DELCORRELATION**
 - **DELTEMPORARY**
 - **DELSTATS**

Example::

   [root@centreon ~]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a dellogger -v "broker cfg for poller test;1" 

The I/O ID is used for identifying the object to delete.


Setintput, Setoutput, Setlogger, Setcorrelation, Settemporary and Setstats
--------------------------------------------------------------------------

In order to set parameters of an I/O object, use one of the following commands:
 - **SETINPUT**
 - **SETOUTPUT**
 - **SETLOGGER**
 - **SETCORRELATION**
 - **SETTEMPORARY**
 - **SETSTATS**

Example::

   [root@centreon ~]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a setcorrelation -v "broker cfg for poller test;1;file;/etc/centreon-broker/correlation.xml" 

Arguments are composed of the following columns:

======= ===========================================================
Order	Column description
======= ===========================================================
1	    Name of Centreon Broker CFG

2	    ID of I/O object

3	    Parameter name

4	    Parameter value, for multiple values, use the "," delimiter
======= ===========================================================

You may get help with the following CLAPI commands:
 - **GETTYPELIST**
 - **GETFIELDLIST**
 - **GETVALUELIST**

Example::

  [root@localhost core]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a gettypelist -v "output" 
  type id;short name;name
  16;sql;Broker SQL Database
  11;file;File
  3;ipv4;IPv4
  10;ipv6;IPv6
  15;local_client;Local Client Socket
  12;local_server;Local Server Socket
  14;storage;Perfdata Generator (Centreon Storage)
  13;rrd;RRD File Generator

  [root@localhost core]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a getfieldlist -v "ipv4" 
  field id;short name;name
  3;ca_certificate;Trusted CA's certificate;text
  2;host;Host to connect to;text
  46;negociation;Enable negociation;radio
  48;one_peer_retention_mode;One peer retention;radio
  1;port;Connection port;int
  4;private_key;Private key file.;text
  12;protocol*;Serialization Protocol;select
  5;public_cert;Public certificate;text
  6;tls;Enable TLS encryption;radio

.. note::
  Note that the "protocol" entry is followed by a star. This means that you have to use one of the possible values. 

This is how you get the list of possible values of a given field::

  [root@localhost core]# ./centreon -u admin -p centreon -o CENTBROKERCFG -a getvaluelist -v "protocol" 
  possible values
  ndo


The following chapters describes the parameters of each Object type


correlation
~~~~~~~~~~~

correlation:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
file                           Correlation file                                             Path to the correlation file which holds host, services, 
                                                                                            dependencies and parenting definitions.                      -                                                          

retention                      Retention file                                               File where correlation state will be stored during 
                                                                                            correlation engine restart                                   -                                                          

============================== ============================================================ ============================================================ ===========================================================



input
~~~~~

ipv4:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

compression                    Compression (zlib)                                           Enable or not data stream compression.                       -                                                          

compression_buffer             Compression buffer size                                      The higher the buffer size is, the best compression. 
                                                                                            This however increase data streaming latency. 
                                                                                            Use with caution.                                            -                                                          

compression_level              Compression level                                            Ranges from 0 (no compression) to 9 (best compression). 
                                                                                            Default is -1 (zlib compression)                             -                                                          

failover                       Failover name                                                Name of the input or output object that will act as 
                                                                                            failover.                                                    -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

category                       Filter category                                              Category filter for flux in input                            -

ca_certificate                 Trusted CA's certificate                                     Trusted CA's certificate.                                    -                                                          

host                           Host to connect to                                           IP address or hostname of the host to connect to 
                                                                                            (leave blank for listening mode).                            -                                                          

one_peer_retention_mode        One peer retention                                           This allows the retention to work even                       -
                                                                                            if the socket is listening

port                           Connection port                                              Port to listen on (empty host) or to connect to 
                                                                                            (with host filled).                                          -                                                          

private_key                    Private key file.                                            Private key file path when TLS encryption is used.           -                                                          

protocol                       Serialization protocol                                       Serialization protocol.                                      ndo                                                        

public_cert                    Public certificate                                           Public certificate file path when TLS encryption is used.    -                                                          

tls                            Enable TLS encryption                                        Enable TLS encryption.                                       -                                                          

============================== ============================================================ ============================================================ ===========================================================


ipv6:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

compression                    Compression (zlib)                                           Enable or not data stream compression.                       -                                                          

compression_buffer             Compression buffer size                                      The higher the buffer size is, the best compression. 
                                                                                            This however increase data streaming latency.
                                                                                            Use with caution.                                            -                                                          

compression_level              Compression level                                            Ranges from 0 (no compression) to 9 (best compression).
                                                                                            Default is -1 (zlib compression)                             -                                                          

failover                       Failover name                                                Name of the input or output object that will act as 
                                                                                            failover.                                                    -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

category                       Filter category                                              Category filter for flux in input                            -

ca_certificate                 Trusted CA's certificate                                     Trusted CA's certificate.                                    -                                                          

host                           Host to connect to                                           IP address or hostname of the host to connect to 
                                                                                            (leave blank for listening mode).                            -                                                          

one_peer_retention_mode        One peer retention                                           This allows the retention to work even                       -
                                                                                            if the socket is listening

port                           Connection port                                              Port to listen on (empty host) or to connect to 
                                                                                            (with host filled).                                          -                                                          

private_key                    Private key file.                                            Private key file path when TLS encryption is used.           -                                                          

protocol                       Serialization protocol                                       Serialization protocol.                                      ndo                                                        

public_cert                    Public certificate                                           Public certificate file path when TLS encryption is used.    -                                                          

tls                            Enable TLS encryption                                        Enable TLS encryption.                                       -                                                          

============================== ============================================================ ============================================================ ===========================================================


file:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

compression                    Compression (zlib)                                           Enable or not data stream compression.                       -                                                          

compression_buffer             Compression buffer size                                      The higher the buffer size is, the best compression. 
                                                                                            This however increase data streaming latency.
                                                                                            Use with caution.                                            -                                                          

compression_level              Compression level                                            Ranges from 0 (no compression) to 9 (best compression). 
                                                                                            Default is -1 (zlib compression)                             -                                                          

failover                       Failover name                                                Name of the input or output object that will act as 
                                                                                            failover.                                                    -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

max_size                       Maximum size of file                                         Maximum size in bytes.                                       -                                                          

path                           File path                                                    Path to the file.                                            -                                                          

protocol                       Serialization protocol                                       Serialization protocol.                                      ndo                                                        

============================== ============================================================ ============================================================ ===========================================================


local_server:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

compression                    Compression (zlib)                                           Enable or not data stream compression.                       -                                                          

compression_buffer             Compression buffer size                                      The higher the buffer size is, the best compression. 
                                                                                            This however increase data streaming latency. 
                                                                                            Use with caution.                                            -                                                          

compression_level              Compression level                                            Ranges from 0 (no compression) to 9 (best compression). 
                                                                                            Default is -1 (zlib compression)                             -                                                          

failover                       Failover name                                                Name of the input or output object that will act as 
                                                                                            failover.                                                    -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

path                           File path                                                    Path to the file.                                            -                                                          

protocol                       Serialization protocol                                       Serialization protocol.                                      ndo                                                        

============================== ============================================================ ============================================================ ===========================================================


local_client:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

compression                    Compression (zlib)                                           Enable or not data stream compression.                       -                                                          

compression_buffer             Compression buffer size                                      The higher the buffer size is, the best compression. 
                                                                                            This however increase data streaming latency. 
                                                                                            Use with caution.                                            -                                                          

compression_level              Compression level                                            Ranges from 0 (no compression) to 9 (best compression). 
                                                                                            Default is -1 (zlib compression)                             -                                                          

failover                       Failover name                                                Name of the input or output object that will act as 
                                                                                            failover.                                                    -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

path                           File path                                                    Path to the file.                                            -                                                          

protocol                       Serialization protocol                                       Serialization protocol.                                      ndo                                                        

============================== ============================================================ ============================================================ ===========================================================



logger
~~~~~~

file:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
config                         Configuration messages                                       Enable or not configuration messages logging.                -                                                          

debug                          Debug messages                                               Enable or not debug messages logging.                        -                                                          

error                          Error messages                                               Enable or not error messages logging.                        -                                                          

info                           Informational messages                                       Enable or not informational messages logging.                -                                                          

level                          Logging level                                                How much messages must be logged.                            high,low,medium                                            

max_size                       Max file size in bytes                                       The maximum size of log file.                                -                                                          

name                           Name of the logger                                           For a file logger this is the path to the file. For a 
                                                                                            standard logger, one of 'stdout' or 'stderr'.                -                                                          

============================== ============================================================ ============================================================ ===========================================================


standard:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
config                         Configuration messages                                       Enable or not configuration messages logging.                -                                                          

debug                          Debug messages                                               Enable or not debug messages logging.                        -                                                          

error                          Error messages                                               Enable or not error messages logging.                        -                                                          

info                           Informational messages                                       Enable or not informational messages logging.                -                                                          

level                          Logging level                                                How much messages must be logged.                            high,low,medium                                            

name                           Name of the logger                                           For a file logger this is the path to the file. 
                                                                                            For a standard logger, one of 'stdout' or 'stderr'.          -                                                          

============================== ============================================================ ============================================================ ===========================================================


syslog:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
config                         Configuration messages                                       Enable or not configuration messages logging.                -                                                          

debug                          Debug messages                                               Enable or not debug messages logging.                        -                                                          

error                          Error messages                                               Enable or not error messages logging.                        -                                                          

info                           Informational messages                                       Enable or not informational messages logging.                -                                                          

level                          Logging level                                                How much messages must be logged.                            high,low,medium                                            

============================== ============================================================ ============================================================ ===========================================================


monitoring:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
config                         Configuration messages                                       Enable or not configuration messages logging.                -                                                          

debug                          Debug messages                                               Enable or not debug messages logging.                        -                                                          

error                          Error messages                                               Enable or not error messages logging.                        -                                                          

info                           Informational messages                                       Enable or not informational messages logging.                -                                                          

level                          Logging level                                                How much messages must be logged.                            high,low,medium                                            

name                           Name of the logger                                           For a file logger this is the path to the file.
                                                                                            For a standard logger, one of 'stdout' or 'stderr'.          -                                                          

============================== ============================================================ ============================================================ ===========================================================



output
~~~~~~

ipv4:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

compression                    Compression (zlib)                                           Enable or not data stream compression.                       -                                                          

compression_buffer             Compression buffer size                                      The higher the buffer size is, the best compression. 
                                                                                            This however increase data streaming latency. 
                                                                                            Use with caution.                                            -                                                          

compression_level              Compression level                                            Ranges from 0 (no compression) to 9 (best compression). 
                                                                                            Default is -1 (zlib compression)                             -                                                          

failover                       Failover name                                                Name of the input or output object that will act as 
                                                                                            failover.                                                    -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

category                       Filter category                                              Category filter for flux in output                           -

ca_certificate                 Trusted CA's certificate                                     Trusted CA's certificate.                                    -                                                          

host                           Host to connect to                                           IP address or hostname of the host to connect to 
                                                                                            (leave blank for listening mode).                            -                                                          

one_peer_retention_mode        One peer retention                                           This allows the retention to work even                       -
                                                                                            if the socket is listening     

port                           Connection port                                              Port to listen on (empty host) or to connect to 
                                                                                            (with host filled).                                          -                                                          

private_key                    Private key file.                                            Private key file path when TLS encryption is used.           -                                                          

protocol                       Serialization protocol                                       Serialization protocol.                                      ndo                                                        

public_cert                    Public certificate                                           Public certificate file path when TLS encryption is used.    -                                                          

tls                            Enable TLS encryption                                        Enable TLS encryption.                                       -                                                          

============================== ============================================================ ============================================================ ===========================================================


ipv6:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

compression                    Compression (zlib)                                           Enable or not data stream compression.                       -                                                          

compression_buffer             Compression buffer size                                      The higher the buffer size is, the best compression.
                                                                                            This however increase data streaming latency.
                                                                                            Use with caution.                                            -                                                          

compression_level              Compression level                                            Ranges from 0 (no compression) to 9 (best compression). 
                                                                                            Default is -1 (zlib compression)                             -                                                          

failover                       Failover name                                                Name of the input or output object that will act as 
                                                                                            failover.                                                    -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

category                       Filter category                                              Category filter for flux in output                           -

ca_certificate                 Trusted CA's certificate                                     Trusted CA's certificate.                                    -                                                          

host                           Host to connect to                                           IP address or hostname of the host to connect to 
                                                                                            (leave blank for listening mode).                            -                                                          

one_peer_retention_mode        One peer retention                                           This allows the retention to work even                       -
                                                                                            if the socket is listening

port                           Connection port                                              Port to listen on (empty host) or to connect to 
                                                                                            (with host filled).                                          -                                                          

private_key                    Private key file.                                            Private key file path when TLS encryption is used.           -                                                          

protocol                       Serialization protocol                                       Serialization protocol.                                      ndo                                                        

public_cert                    Public certificate                                           Public certificate file path when TLS encryption is used.    -                                                          

tls                            Enable TLS encryption                                        Enable TLS encryption.                                       -                                                          

============================== ============================================================ ============================================================ ===========================================================


file:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

compression                    Compression (zlib)                                           Enable or not data stream compression.                       -                                                          

compression_buffer             Compression buffer size                                      The higher the buffer size is, the best compression. 
                                                                                            This however increase data streaming latency.
                                                                                            Use with caution.                                            -                                                          

compression_level              Compression level                                            Ranges from 0 (no compression) to 9 (best compression).
                                                                                            Default is -1 (zlib compression)                             -                                                          

failover                       Failover name                                                Name of the input or output object that will act as 
                                                                                            failover.                                                    -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

max_size                       Maximum size of file                                         Maximum size in bytes.                                       -                                                          

path                           File path                                                    Path to the file.                                            -                                                          

protocol                       Serialization protocol                                       Serialization protocol.                                      ndo                                                        

============================== ============================================================ ============================================================ ===========================================================


local_server:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

compression                    Compression (zlib)                                           Enable or not data stream compression.                       -                                                          

compression_buffer             Compression buffer size                                      The higher the buffer size is, the best compression. 
                                                                                            This however increase data streaming latency. 
                                                                                            Use with caution.                                            -                                                          

compression_level              Compression level                                            Ranges from 0 (no compression) to 9 (best compression). 
                                                                                            Default is -1 (zlib compression)                             -                                                          

failover                       Failover name                                                Name of the input or output object that will act as 
                                                                                            failover.                                                    -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

path                           File path                                                    Path to the file.                                            -                                                          

protocol                       Serialization protocol                                       Serialization protocol.                                      ndo                                                        

============================== ============================================================ ============================================================ ===========================================================


rrd:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

failover                       Failover name                                                Name of the input or output object that will act as 
                                                                                            failover.                                                    -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

metrics_path                   RRD file directory for metrics                               RRD file directory, for example /var/lib/centreon/metrics    -                                                          

path                           Unix socket                                                  The Unix socket used to communicate with rrdcached. 
                                                                                            This is a global option, go to 
                                                                                            Administration > Options > RRDTool to modify it.             -                                                          

port                           TCP port                                                     The TCP port used to communicate with rrdcached. 
                                                                                            This is a global option, go to 
                                                                                            Administration > Options > RRDTool to modify it.             -                                                          

status_path                    RRD file directory for statuses                              RRD file directory, for example /var/lib/centreon/status     -                                                          

============================== ============================================================ ============================================================ ===========================================================


storage:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

failover                       Failover name                                                Name of the input or output object that will act as 
                                                                                            failover.                                                    -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

check_replication              Replication enabled                                          When enabled, the broker engine will check whether or not 
                                                                                            the replication is up to date before attempting to 
                                                                                            update data.                                                 -                                                          

db_host                        DB host                                                      IP address or hostname of the database server.               -                                                          

db_name                        DB name                                                      Database name.                                               -                                                          

db_password                    DB password                                                  Password of database user.                                   -                                                          

db_port                        DB port                                                      Port on which the DB server listens                          -                                                          

db_type                        DB type                                                      Target DBMS.                                                 db2,ibase,mysql,oci,odbc,postgresql,sqlite,tds             

db_user                        DB user                                                      Database user.                                               -                                                          

interval                       Interval length                                              Interval length in seconds.                                  -                                                          

length                         RRD length                                                   RRD storage duration in seconds.                             -                                                          

queries_per_transaction        Maximum queries per transaction                              The maximum queries per transaction before commit.           -                                                          

read_timeout                   Transaction commit timeout                                   The transaction timeout before running commit.               -                                                          

rebuild_check_interval         Rebuild check interval in seconds                            The interval between check if some metrics must be rebuild. 
                                                                                            The default value is 300s                                    -                                                          

============================== ============================================================ ============================================================ ===========================================================


local_client:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

compression                    Compression (zlib)                                           Enable or not data stream compression.                       -                                                          

compression_buffer             Compression buffer size                                      The higher the buffer size is, the best compression. 
                                                                                            This however increase data streaming latency.
                                                                                            Use with caution.                                            -                                                          

compression_level              Compression level                                            Ranges from 0 (no compression) to 9 (best compression). 
                                                                                            Default is -1 (zlib compression)                             -                                                          

failover                       Failover name                                                Name of the input or output object that 
                                                                                            will act as failover.                                        -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

path                           File path                                                    Path to the file.                                            -                                                          

protocol                       Serialization protocol                                       Serialization protocol.                                      ndo                                                        

============================== ============================================================ ============================================================ ===========================================================


sql:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
buffering_timeout              Buffering timeout                                            Time in seconds to wait before launching failover.           -                                                          

failover                       Failover name                                                Name of the input or output object that will act as 
                                                                                            failover.                                                    -                                                          

retry_interval                 Retry interval                                               Time in seconds to wait between each connection attempt.     -                                                          

check_replication              Replication enabled                                          When enabled, the broker engine will check whether or not 
                                                                                            the replication is up to date before attempting to 
                                                                                            update data.                                                 -                                                          

db_host                        DB host                                                      IP address or hostname of the database server.               -                                                          

db_name                        DB name                                                      Database name.                                               -                                                          

db_password                    DB password                                                  Password of database user.                                   -                                                          

db_port                        DB port                                                      Port on which the DB server listens                          -                                                          

db_type                        DB type                                                      Target DBMS.                                                 db2,ibase,mysql,oci,odbc,postgresql,sqlite,tds             

db_user                        DB user                                                      Database user.                                               -                                                          

queries_per_transaction        Maximum queries per transaction                              The maximum queries per transaction before commit.           -                                                          

read_timeout                   Transaction commit timeout                                   The transaction timeout before running commit.               -                                                          

============================== ============================================================ ============================================================ ===========================================================



stats
~~~~~

stats:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
fifo                           File for Centeron Broker statistics                          File where Centreon Broker statistics will be stored         -                                                          

============================== ============================================================ ============================================================ ===========================================================



temporary
~~~~~~~~~

file:

============================== ============================================================ ============================================================ ===========================================================
ID                             Label                                                        Description                                                  Possible values                                            
============================== ============================================================ ============================================================ ===========================================================
compression                    Compression (zlib)                                           Enable or not data stream compression.                       -                                                          

compression_buffer             Compression buffer size                                      The higher the buffer size is, the best compression. 
                                                                                            This however increase data streaming latency. 
                                                                                            Use with caution.                                            -                                                          

compression_level              Compression level                                            Ranges from 0 (no compression) to 9 (best compression). 
                                                                                            Default is -1 (zlib compression)                             -                                                          

max_size                       Maximum size of file                                         Maximum size in bytes.                                       -                                                          

path                           File path                                                    Path to the file.                                            -                                                          

protocol                       Serialization protocol                                       Serialization protocol.                                      ndo                                                        

============================== ============================================================ ============================================================ ===========================================================

