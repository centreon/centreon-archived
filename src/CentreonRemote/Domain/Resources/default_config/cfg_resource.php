<?php

return [
    [
        'resource_name'     => '$USER1$',
        'resource_line'     => '@plugin_dir@',
        'resource_comment'  => 'Nagios Plugins Path',
        'resource_activate' => '1',
    ],
    [
        'resource_name'     => '$CENTREONPLUGINS$',
        'resource_line'     => '@centreonplugins@',
        'resource_comment'  => 'Centreon Plugins Path',
        'resource_activate' => '1',
    ],
];
