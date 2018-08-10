<?php

return function () {
    return [
        [
            'resource_name'     => '$USER1$',
            'resource_line'     => '/usr/lib/nagios/plugins',
            'resource_comment'  => 'path to the plugins',
            'resource_activate' => '1',
        ],
        [
            'resource_name'     => '$USER2$',
            'resource_line'     => 'public',
            'resource_comment'  => 'SNMP Community',
            'resource_activate' => '1',
        ],
        [
            'resource_name'     => '$CENTREONPLUGINS$',
            'resource_line'     => '/usr/lib/centreon/plugins',
            'resource_comment'  => 'Centreon Plugin Path',
            'resource_activate' => '1',
        ],
    ];
};