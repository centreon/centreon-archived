<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Infrastructure\HostConfiguration\API\Model;

use Centreon\Domain\HostConfiguration\UseCase\v2_1\FindHostGroupsResponse;

/**
 * This class is designed to represent the formatted response of the API request.
 *
 * @package Centreon\Infrastructure\HostConfiguration\API\Model
 */
class HostGroupV21
{

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $alias;

    /**
     * @var string|null
     */
    public $notes;

    /**
     * @var string|null
     */
    public $notesUrl;

    /**
     * @var string|null
     */
    public $actionUrl;

    /**
     * @var string|null
     */
    public $icon;

    /**
     * @var string|null
     */
    public $iconMap;

    /**
     * @var string|null
     */
    public $rrd;

    /**
     * @var string|null
     */
    public $geoCoords;

    /**
     * @var string|null
     */
    public $comment;

    /**icon
     * @var bool
     * @EntityDescriptor(column="is_activated", modifier="setActivted")
     */
    public $isActivated = true;

    /**
     * @param FindHostGroupsResponse $response
     * @return HostGroupV21[]
     */
    public static function createFromResponse(FindHostGroupsResponse $response): array
    {
        $hostGroups = [];
        foreach ($response->getHostGroups() as $hostGroup) {
            $newHostGroup = new self();
            $newHostGroup->id = $hostGroup['id'];
            $newHostGroup->name = $hostGroup['name'];
            $newHostGroup->alias = $hostGroup['alias'];
            $newHostGroup->notes = $hostGroup['notes'];
            $newHostGroup->notesUrl = $hostGroup['notes-url'];
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
