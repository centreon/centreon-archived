<?php

return function ($configID, $pollerName) {
    $pollerName = str_replace(' ', '_', $pollerName);

    return [
        [
            'cfg_nagios_id' => $configID,
            'broker_module' => "/usr/lib64/nagios/cbmod.so /etc/centreon-broker/{$pollerName}-module.xml",
        ],
        [
            'cfg_nagios_id' => $configID,
            'broker_module' => '/usr/lib64/centreon-engine/externalcmd.so',
        ],
    ];
};
