<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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
declare(strict_types=1);

namespace Centreon\Infrastructure\HostConfiguration\API\Model\HostGroup;

use Centreon\Domain\HostConfiguration\UseCase\V2110\HostGroup\FindHostGroupsResponse;

/**
 * This class is designed to create the HostGroupV21 entity
 *
 * @package Centreon\Infrastructure\HostConfiguration\API\Model\HostGroup
 */
class HostGroupV2110Factory
{
    /**
     * @param FindHostGroupsResponse $response
     * @return HostGroupV2110[]
     */
    public static function createFromResponse(FindHostGroupsResponse $response): array
    {
        $hostGroups = [];
        foreach ($response->getHostGroups() as $hostGroup) {
            $newHostGroup = new HostGroupV2110();
            $newHostGroup->id = $hostGroup['id'];
            $newHostGroup->name = $hostGroup['name'];
            $newHostGroup->alias = $hostGroup['alias'];
            $newHostGroup->notes = $hostGroup['notes'];
            $newHostGroup->notesUrl = $hostGroup['notes_url'];
            $newHostGroup->actionUrl = $hostGroup['action_url'];
            $newHostGroup->icon = $hostGroup['icon'];
            $newHostGroup->iconMap = $hostGroup['icon_map'];
            $newHostGroup->rrd = $hostGroup['rrd'];
            $newHostGroup->geoCoords = $hostGroup['geo_coords'];
            $newHostGroup->comment = $hostGroup['comment'];
            $newHostGroup->isActivated = $hostGroup['is_activated'];
            $hostGroups[] = $newHostGroup;
        }
        return $hostGroups;
    }
}
