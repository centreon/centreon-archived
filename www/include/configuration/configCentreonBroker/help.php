<?php

/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */
 
echo _("Time in seconds to wait before launching failover.");
echo _("Category filter for flux in output");
echo _("Trusted CA's certificate.");
echo _("When enabled,
 the broker engine will check whether or not the replication is up to date before attempting to update data.");
echo _("Interval in seconds before delete data from deleted pollers.");
echo _("File for external commands");
echo _("Enable or not data stream compression.");
echo _("The higher the buffer size is, the best compression.
 This however increase data streaming latency. Use with caution.");
echo _("Ranges from 0 (no compression) to 9 (best compression). Default is -1 (zlib compression)");
echo _("Enable or not configuration messages logging.");
echo _("IP address or hostname of the database server.");
echo _("Database name.");
echo _("Password of database user.");
echo _("Port on which the DB server listens");
echo _("Target DBMS.");
echo _("Database user.");
echo _("Enable or not debug messages logging.");
echo _("Enable or not error messages logging.");
echo _("Name of the input or output object that will act as failover.");
echo _("File where Centreon Broker statistics will be stored");
echo _("Path to the correlation file which holds host, services, dependencies and parenting definitions.");
echo _("IP address or hostname of the host to connect to (leave blank for listening mode).");
echo _("Enable or not informational messages logging.");
echo _("Whether or not Broker should create entries in the index_data table.
 This process should be done by Centreon and this option should only be enabled by advanced 
 users knowing what they're doing");
echo _("Interval in seconds before change status of resources from a disconnected poller");
echo _("Interval length in seconds.");
echo _("RRD storage duration in seconds.");
echo _("How much messages must be logged.");
echo _("Maximum size in bytes.");
echo _("The maximum size of log file.");
echo _("RRD file directory, for example /var/lib/centreon/metrics");
echo _("For a file logger this is the path to the file. For a standard logger, one of 'stdout' or 'stderr'.");
echo _("Enable negotiation option (use only for version of Centren Broker >= 2.5)");
echo _("This allows the retention to work even if the socket is listening");
echo _("The Unix socket used to communicate with rrdcached.
 This is a global option, go to Administration > Options > RRDTool to modify it.");
echo _("Path to the file.");
echo _("Port to listen on (empty host) or to connect to (with host filled).");
echo _("The TCP port used to communicate with rrdcached.
 This is a global option, go to Administration > Options > RRDTool to modify it.");
echo _("Private key file path when TLS encryption is used.");
echo _("Serialization protocol.");
echo _("Public certificate file path when TLS encryption is used.");
echo _("The maximum queries per transaction before commit.");
echo _("The transaction timeout before running commit.");
echo _("The interval between check if some metrics must be rebuild. The default value is 300s");
echo _("File where correlation state will be stored during correlation engine restart");
echo _("Time in seconds to wait between each connection attempt.");
echo _("RRD file directory, for example /var/lib/centreon/status");
echo _("It should be enabled to control whether or not Centreon Broker
 should insert performance data in the data_bin table.");
echo _("Enable TLS encryption.");
echo _("This can be used to disable graph update and therefore reduce I/O");
echo _("This can be used to disable graph update and therefore reduce I/O");
