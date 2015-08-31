<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

echo _("Port to listen on (empty host) or to connect to (with host filled).");
echo _("IP address or hostname of the host to connect to (leave blank for listening mode).");
echo _("Trusted CA's certificate.");
echo _("Private key file path when TLS encryption is used.");
echo _("Public certificate file path when TLS encryption is used.");
echo _("Enable TLS encryption.");
echo _("IP address or hostname of the database server.");
echo _("Database user.");
echo _("Password of database user.");
echo _("Database name.");
echo _("Path to the file.");
echo _("Serialization protocol.");
echo _("RRD file directory, for example /var/lib/centreon/metrics");
echo _("RRD file directory, for example /var/lib/centreon/status");
echo _("Target DBMS.");
echo _("Interval length in seconds.");
echo _("RRD storage duration in seconds.");
echo _("Port on which the DB server listens");
echo _("For a file logger this is the path to the file. For a standard logger, one of 'stdout' or 'stderr'.");
echo _("Enable or not configuration messages logging.");
echo _("Enable or not debug messages logging.");
echo _("Enable or not error messages logging.");
echo _("Enable or not informational messages logging.");
echo _("How much messages must be logged.");
echo _("Enable or not data stream compression.");
echo _("Ranges from 0 (no compression) to 9 (best compression). Default is -1 (zlib compression)");
echo _("The higher the buffer size is, the best compression. This however increase data streaming latency. Use with caution.");
echo _("Name of the input or output object that will act as failover.");
echo _("Path to the correlation file which holds host, services, dependencies and parenting definitions.");
echo _("File where correlation state will be stored during correlation engine restart");
echo _("Time in seconds to wait between each connection attempt.");
echo _("Time in seconds to wait before launching failover.");
echo _("File where Centreon Broker statistics will be stored");
echo _("The maximum queries per transaction before commit.");
echo _("The transaction timeout before running commit.");
echo _("The Unix socket used to communicate with rrdcached. This is a global option, go to Administration > Options > RRDTool to modify it.");
echo _("The TCP port used to communicate with rrdcached. This is a global option, go to Administration > Options > RRDTool to modify it.");
echo _("The maximum size of log file.");
echo _("When enabled, the broker engine will check whether or not the replication is up to date before attempting to update data.");
echo _("The interval between check if some metrics must be rebuild. The default value is 300s");
echo _("Maximum size in bytes.");
echo _("It should be enabled to control whether or not Centreon Broker should insert performance data in the data_bin table.");
echo _("Whether or not Broker should create entries in the index_data table. This process should be done by Centreon and this option should only be enabled by advanced users knowing what they're doing");
echo _("This can be used to disable graph update and therefore reduce I/O");
echo _("This can be used to disable graph update and therefore reduce I/O");
echo _("Enable negociation option (use only for version of Centren Broker >= 2.5)");
echo _("Category filter for flux in output");
echo _("This allows the retention to work even if the socket is listening");

?>