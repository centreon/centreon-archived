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

namespace Centreon\Domain\HostConfiguration\UseCase\V2110\HostGroup;

use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\Media\Model\Image;

/**
 * This class is a DTO for the FindHostGroups use case.
 *
 * @package Centreon\Domain\HostConfiguration\UseCase\V21\HostGroup
 */
class FindHostGroupsResponse
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private $hostGroups = [];

    /**
     * @param HostGroup[] $hostGroups
     */
    public function setHostGroups(array $hostGroups): void
    {
        foreach ($hostGroups as $hostGroup) {
            $this->hostGroups[] = [
                'id' => $hostGroup->getId(),
                'name' => $hostGroup->getName(),
                'alias' => $hostGroup->getAlias(),
                'notes' => $hostGroup->getNotes(),
                'notes_url' => $hostGroup->getNotesUrl(),
                'action_url' => $hostGroup->getActionUrl(),
                'icon' => $this->imageToArray($hostGroup->getIcon()),
                'icon_map' => $this->imageToArray($hostGroup->getIconMap()),
                'rrd' => $hostGroup->getRrd(),
                'geo_coords' => $hostGroup->getGeoCoords(),
                'is_activated' => $hostGroup->isActivated(),
                'comment' => $hostGroup->getComment()
            ];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getHostGroups(): array
    {
        return $this->hostGroups;
    }

    /**
     * @param Image|null $image
     * @return array<string, string|int|null>|null
     */
    private function imageToArray(?Image $image): ?array
    {
        if ($image !== null) {
            return [
                'id' => $image->getId(),
                'name' => $image->getName(),
                'path' => $image->getPath(),
                'comment' => $image->getComment()
            ];
        }
        return null;
    }
}
