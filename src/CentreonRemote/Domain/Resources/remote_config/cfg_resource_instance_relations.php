<?php

return function (array $resources, $serverID) {
    $data = [];

    foreach ($resources as $resource) {
        $data[] = [
            'resource_id' => $resource,
            'instance_id' => $serverID,
        ];
    }

    return $data;
};
