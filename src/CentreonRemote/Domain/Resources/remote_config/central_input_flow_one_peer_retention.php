<?php

use Centreon\Domain\Entity\CfgCentreonBrokerInfo;

// configuration if input flow of central broker to get data from the remote poller
return function (string $serverIP) {
    return [
        new CfgCentreonBrokerInfo('name', 'connection-to-poller'),
        new CfgCentreonBrokerInfo('port', '5669'),
        new CfgCentreonBrokerInfo('retry_interval', '60'),
        new CfgCentreonBrokerInfo('buffering_timeout', '0'),
        new CfgCentreonBrokerInfo('host', $serverIP),
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
};
