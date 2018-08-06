<?php

// This table describes which C/C++ libraries will be launched by Centreon Engine on start.
// Currently, there is only two possibilities:
//  cbmod.so + configuration file
//  externalcmd.so

// Notice: $pollerName is the name of poller without space (replaced by underscore character ‘_’).
$pollerName = '';
$data = [
    [
        'cfg_nagios_id' => '', // TODO relation id
        'broker_module' => "/usr/lib64/nagios/cbmod.so /etc/centreon-broker/{$pollerName}-module.xml",
    ],
    [
        'cfg_nagios_id' => '', // TODO relation id
        'broker_module' => '/usr/lib64/centreon-engine/externalcmd.so',
    ],
];

// Notice for later: Do not put the name of the configuration file
// because this one is defined in another table for the Centreon Broker configuration.
