<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

namespace CentreonRemote\Domain\Resources\RemoteConfig;

use Centreon\Domain\Entity\CfgCentreonBrokerInfo;

/**
 * Configuration if input flow of central broker to get data from the remote poller
 */
class InputFlowOnePeerRetention
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @param string $pollerName the poller name
     * @param string $pollerIP the poller ip address
     * @return CfgCentreonBrokerInfo[] the configuration template
     */
    public static function getConfiguration(string $pollerName, string $pollerIP): array
    {
        return [
            new CfgCentreonBrokerInfo('name', "connection-to-${pollerName}"),
            new CfgCentreonBrokerInfo('port', '5669'),
            new CfgCentreonBrokerInfo('retry_interval', '15'),
            new CfgCentreonBrokerInfo('buffering_timeout', '0'),
            new CfgCentreonBrokerInfo('host', $pollerIP),
            new CfgCentreonBrokerInfo('protocol', 'bbdo'),
            new CfgCentreonBrokerInfo('tls', 'no'),
            new CfgCentreonBrokerInfo('failover', ''),
            new CfgCentreonBrokerInfo('private_key', ''),
            new CfgCentreonBrokerInfo('public_cert', ''),
            new CfgCentreonBrokerInfo('ca_certificate', ''),
            new CfgCentreonBrokerInfo('negociation', 'yes'),
            new CfgCentreonBrokerInfo('one_peer_retention_mode', 'no'),
            new CfgCentreonBrokerInfo('compression', 'no'),
            new CfgCentreonBrokerInfo('compression_level', ''),
            new CfgCentreonBrokerInfo('compression_buffer', ''),
            new CfgCentreonBrokerInfo('type', 'ipv4'),
            new CfgCentreonBrokerInfo('blockId', '2_3')
        ];
    }
}
