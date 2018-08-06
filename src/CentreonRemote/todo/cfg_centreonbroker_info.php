<?php

// To forward collected data, the Centreon Broker module must have the following configuration:

// ------------------ Centreon Broker module ------------------
// This configuration is performed in the “Configuration > Pollers > Broker configuration” menu in “Logger” tab.
// +------------+--------------------------------------------+--------------+-----------------+-----------+
// | config_key | config_value                               | config_group | config_group_id | grp_level |
// +------------+--------------------------------------------+--------------+-----------------+-----------+
// | name       | /var/log/centreon-broker/broker-module.log | logger       |               0 |         0 |
// | config     | yes                                        | logger       |               0 |         0 |
// | debug      | no                                         | logger       |               0 |         0 |
// | error      | yes                                        | logger       |               0 |         0 |
// | info       | no                                         | logger       |               0 |         0 |
// | level      | low                                        | logger       |               0 |         0 |
// | max_size   |                                            | logger       |               0 |         0 |
// | type       | file                                       | logger       |               0 |         0 |
// | blockId    | 3_17                                       | logger       |               0 |         0 |
// +------------+--------------------------------------------+--------------+-----------------+-----------+

// This configuration is performed in the “Configuration > Pollers > Broker configuration” menu in “Output” tab.
// +-------------------------+----------------+--------------+-----------------+-----------+
// | config_key              | config_value   | config_group | config_group_id | grp_level |
// +-------------------------+----------------+--------------+-----------------+-----------+
// | name                    | Central-Output | output       |               0 |         0 |
// | port                    | 5669           | output       |               0 |         0 |
// | host                    | localhost      | output       |               0 |         0 |
// | failover                |                | output       |               0 |         0 |
// | retry_interval          |                | output       |               0 |         0 |
// | buffering_timeout       |                | output       |               0 |         0 |
// | protocol                | bbdo           | output       |               0 |         0 |
// | tls                     | no             | output       |               0 |         0 |
// | private_key             |                | output       |               0 |         0 |
// | public_cert             |                | output       |               0 |         0 |
// | ca_certificate          |                | output       |               0 |         0 |
// | negociation             | yes            | output       |               0 |         0 |
// | one_peer_retention_mode | no             | output       |               0 |         0 |
// | compression             | no             | output       |               0 |         0 |
// | compression_level       |                | output       |               0 |         0 |
// | compression_buffer      |                | output       |               0 |         0 |
// | type                    | ipv4           | output       |               0 |         0 |
// | blockId                 | 1_3            | output       |               0 |         0 |
// +-------------------------+----------------+--------------+-----------------+-----------+

// ------------------ Centreon Broker SQL ------------------
// This configuration is performed in the “Configuration > Pollers > Broker configuration” menu in “Input” tab:
// +-------------------------+--------------+--------------+-----------------+-----------+
// | config_key              | config_value | config_group | config_group_id | grp_level |
// +-------------------------+--------------+--------------+-----------------+-----------+
// | name                    | Master-Input | input        |               0 |         0 |
// | port                    | 5669         | input        |               0 |         0 |
// | host                    |              | input        |               0 |         0 |
// | failover                |              | input        |               0 |         0 |
// | retry_interval          |              | input        |               0 |         0 |
// | buffering_timeout       |              | input        |               0 |         0 |
// | protocol                | bbdo         | input        |               0 |         0 |
// | tls                     | no           | input        |               0 |         0 |
// | private_key             |              | input        |               0 |         0 |
// | public_cert             |              | input        |               0 |         0 |
// | ca_certificate          |              | input        |               0 |         0 |
// | negociation             | yes          | input        |               0 |         0 |
// | one_peer_retention_mode | no           | input        |               0 |         0 |
// | compression             | no           | input        |               0 |         0 |
// | compression_level       |              | input        |               0 |         0 |
// | compression_buffer      |              | input        |               0 |         0 |
// | type                    | ipv4         | input        |               0 |         0 |
// | blockId                 | 2_3          | input        |               0 |         0 |
// +-------------------------+--------------+--------------+-----------------+-----------+

// This configuration is performed in the “Configuration > Pollers > Broker configuration” menu in “Logger” tab:
// +------------+-----------------------------------------+--------------+-----------------+-----------+
// | config_key | config_value                            | config_group | config_group_id | grp_level |
// +------------+-----------------------------------------+--------------+-----------------+-----------+
// | name       | /var/log/centreon-broker/broker-sql.log | logger       |               0 |         0 |
// | config     | yes                                     | logger       |               0 |         0 |
// | debug      | no                                      | logger       |               0 |         0 |
// | error      | yes                                     | logger       |               0 |         0 |
// | info       | no                                      | logger       |               0 |         0 |
// | level      | low                                     | logger       |               0 |         0 |
// | max_size   |                                         | logger       |               0 |         0 |
// | type       | file                                    | logger       |               0 |         0 |
// | blockId    | 3_17                                    | logger       |               0 |         0 |
// +------------+-----------------------------------------+--------------+-----------------+-----------+

// This configuration is performed in the “Configuration > Pollers > Broker configuration” menu in “Output” tab:
// +-------------------------+------------------+--------------+-----------------+-----------+
// | config_key              | config_value     | config_group | config_group_id | grp_level |
// +-------------------------+------------------+--------------+-----------------+-----------+
// | name                    | Perfdata-Master  | output       |               0 |         0 |
// | interval                | 60               | output       |               0 |         0 |
// | retry_interval          |                  | output       |               0 |         0 |
// | buffering_timeout       |                  | output       |               0 |         0 |
// | failover                |                  | output       |               0 |         0 |
// | length                  | 3456000          | output       |               0 |         0 |
// | db_type                 | mysql            | output       |               0 |         0 |
// | db_host                 | localhost        | output       |               0 |         0 |
// | db_port                 | 3306             | output       |               0 |         0 |
// | db_user                 | centreon         | output       |               0 |         0 |
// | db_password             | FDuM1710         | output       |               0 |         0 |
// | db_name                 | centreon_storage | output       |               0 |         0 |
// | queries_per_transaction | 500              | output       |               0 |         0 |
// | read_timeout            | 5                | output       |               0 |         0 |
// | check_replication       | no               | output       |               0 |         0 |
// | rebuild_check_interval  |                  | output       |               0 |         0 |
// | store_in_data_bin       | yes              | output       |               0 |         0 |
// | insert_in_index_data    | 1                | output       |               0 |         0 |
// | filters                 |                  | output       |               0 |         0 |
// | category                | neb              | output       |               0 |         1 |
// | type                    | storage          | output       |               0 |         0 |
// | blockId                 | 1_14             | output       |               0 |         0 |
// +-------------------------+------------------+--------------+-----------------+-----------+

// +-------------------------+------------------+--------------+-----------------+-----------+
// | config_key              | config_value     | config_group | config_group_id | grp_level |
// +-------------------------+------------------+--------------+-----------------+-----------+
// | name                    | Status-Master    | output       |               1 |         0 |
// | db_type                 | mysql            | output       |               1 |         0 |
// | failover                |                  | output       |               1 |         0 |
// | retry_interval          |                  | output       |               1 |         0 |
// | buffering_timeout       |                  | output       |               1 |         0 |
// | db_host                 | localhost        | output       |               1 |         0 |
// | db_port                 | 3306             | output       |               1 |         0 |
// | db_user                 | centreon         | output       |               1 |         0 |
// | db_password             | FDuM1710         | output       |               1 |         0 |
// | db_name                 | centreon_storage | output       |               1 |         0 |
// | queries_per_transaction | 400              | output       |               1 |         0 |
// | read_timeout            | 5                | output       |               1 |         0 |
// | check_replication       | no               | output       |               1 |         0 |
// | filters                 |                  | output       |               1 |         0 |
// | category                | correlation      | output       |               1 |         1 |
// | category                | neb              | output       |               1 |         1 |
// | cleanup_check_interval  |                  | output       |               1 |         0 |
// | instance_timeout        |                  | output       |               1 |         0 |
// | type                    | sql              | output       |               1 |         0 |
// | blockId                 | 1_16             | output       |               1 |         0 |
// +-------------------------+------------------+--------------+-----------------+-----------+

// +-------------------------+--------------+--------------+-----------------+-----------+
// | config_key              | config_value | config_group | config_group_id | grp_level |
// +-------------------------+--------------+--------------+-----------------+-----------+
// | name                    | RRD-Master   | output       |               2 |         0 |
// | port                    | 5670         | output       |               2 |         0 |
// | host                    | localhost    | output       |               2 |         0 |
// | failover                |              | output       |               2 |         0 |
// | retry_interval          |              | output       |               2 |         0 |
// | buffering_timeout       |              | output       |               2 |         0 |
// | protocol                | bbdo         | output       |               2 |         0 |
// | tls                     | no           | output       |               2 |         0 |
// | private_key             |              | output       |               2 |         0 |
// | public_cert             |              | output       |               2 |         0 |
// | ca_certificate          |              | output       |               2 |         0 |
// | negociation             | yes          | output       |               2 |         0 |
// | one_peer_retention_mode | no           | output       |               2 |         0 |
// | filters                 |              | output       |               2 |         0 |
// | category                | storage      | output       |               2 |         1 |
// | compression             | no           | output       |               2 |         0 |
// | compression_level       |              | output       |               2 |         0 |
// | compression_buffer      |              | output       |               2 |         0 |
// | type                    | ipv4         | output       |               2 |         0 |
// | blockId                 | 1_3          | output       |               2 |         0 |
// +-------------------------+--------------+--------------+-----------------+-----------+

// ------------------ Centreon Broker RRD ------------------
// This configuration is performed in the “Configuration > Pollers > Broker configuration” menu in “Input” tab:
// +-------------------------+--------------+--------------+-----------------+-----------+
// | config_key              | config_value | config_group | config_group_id | grp_level |
// +-------------------------+--------------+--------------+-----------------+-----------+
// | name                    | rrd-input    | input        |               0 |         0 |
// | port                    | 5670         | input        |               0 |         0 |
// | host                    |              | input        |               0 |         0 |
// | failover                |              | input        |               0 |         0 |
// | retry_interval          | 60           | input        |               0 |         0 |
// | buffering_timeout       | 0            | input        |               0 |         0 |
// | protocol                | bbdo         | input        |               0 |         0 |
// | tls                     | no           | input        |               0 |         0 |
// | private_key             |              | input        |               0 |         0 |
// | public_cert             |              | input        |               0 |         0 |
// | ca_certificate          |              | input        |               0 |         0 |
// | negociation             | yes          | input        |               0 |         0 |
// | one_peer_retention_mode | no           | input        |               0 |         0 |
// | compression             | auto         | input        |               0 |         0 |
// | compression_level       |              | input        |               0 |         0 |
// | compression_buffer      |              | input        |               0 |         0 |
// | type                    | ipv4         | input        |               0 |         0 |
// | blockId                 | 2_3          | input        |               0 |         0 |
// +-------------------------+--------------+--------------+-----------------+-----------+

// This configuration is performed in the “Configuration > Pollers > Broker configuration” menu in “Logger” tab:
// +------------+-----------------------------------------+--------------+-----------------+-----------+
// | config_key | config_value                            | config_group | config_group_id | grp_level |
// +------------+-----------------------------------------+--------------+-----------------+-----------+
// | name       | /var/log/centreon-broker/broker-rrd.log | logger       |               0 |         0 |
// | config     | yes                                     | logger       |               0 |         0 |
// | debug      | no                                      | logger       |               0 |         0 |
// | error      | yes                                     | logger       |               0 |         0 |
// | info       | yes                                     | logger       |               0 |         0 |
// | level      | low                                     | logger       |               0 |         0 |
// | max_size   | 100000000                               | logger       |               0 |         0 |
// | type       | file                                    | logger       |               0 |         0 |
// | blockId    | 3_17                                    | logger       |               0 |         0 |
// +------------+-----------------------------------------+--------------+-----------------+-----------+

// This configuration is performed in the “Configuration > Pollers > Broker configuration” menu in “Output” tab:
// +-------------------+----------------------------+--------------+-----------------+-----------+
// | config_key        | config_value               | config_group | config_group_id | grp_level |
// +-------------------+----------------------------+--------------+-----------------+-----------+
// | name              | rrd-output                 | output       |               0 |         0 |
// | metrics_path      | /var/lib/centreon/metrics/ | output       |               0 |         0 |
// | failover          |                            | output       |               0 |         0 |
// | status_path       | /var/lib/centreon/status/  | output       |               0 |         0 |
// | retry_interval    | 60                         | output       |               0 |         0 |
// | buffering_timeout |                            | output       |               0 |         0 |
// | path              |                            | output       |               0 |         0 |
// | port              |                            | output       |               0 |         0 |
// | write_metrics     | yes                        | output       |               0 |         0 |
// | write_status      | yes                        | output       |               0 |         0 |
// | type              | rrd                        | output       |               0 |         0 |
// | blockId           | 1_13                       | output       |               0 |         0 |
// +-------------------+----------------------------+--------------+-----------------+-----------+
