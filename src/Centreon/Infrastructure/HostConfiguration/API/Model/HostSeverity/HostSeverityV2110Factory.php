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

namespace Centreon\Infrastructure\HostConfiguration\API\Model\HostSeverity;

use Centreon\Domain\HostConfiguration\UseCase\V2110\HostSeverity\FindHostSeveritiesResponse;

/**
 * This class is designed to create the hostSeverityV21 entity
 *
 * @package Centreon\Infrastructure\HostConfiguration\API\Model\HostSeverity
 */
class HostSeverityV2110Factory
{
    /**
     * @param FindHostSeveritiesResponse $response
     * @return HostSeverityV2110[]
     */
    public static function createFromResponse(FindHostSeveritiesResponse $response): array
    {
        $hostSeverities = [];
        foreach ($response->getHostSeverities() as $hostSeverity) {
            $newHostSeverity = new HostSeverityV2110();
            $newHostSeverity->id = $hostSeverity['id'];
            $newHostSeverity->name = $hostSeverity['name'];
            $newHostSeverity->alias = $hostSeverity['alias'];
            $newHostSeverity->level = $hostSeverity['level'];
            $newHostSeverity->icon = $hostSeverity['icon'];
            $newHostSeverity->comments = $hostSeverity['comments'];
            $newHostSeverity->isActivated = $hostSeverity['is_activated'];
            $hostSeverities[] = $newHostSeverity;
        }
        return $hostSeverities;
    }
}
