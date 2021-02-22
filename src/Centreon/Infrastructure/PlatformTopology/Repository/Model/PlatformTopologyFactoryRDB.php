<?php

/*
 *
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

namespace Centreon\Infrastructure\PlatformTopology\Repository\Model;

use Centreon\Domain\PlatformTopology\Model\PlatformPending;
use Centreon\Domain\PlatformTopology\Model\PlatformRegistered;

class PlatformTopologyFactoryRDB
{
    /**
     * Return a Registered platform entity
     *
     * @param array $platformData
     * @return PlatformRegistered
     */
    public function createPlatformRegistered(array $platformData): PlatformRegistered
    {
        $platformReturned = new PlatformRegistered();
        foreach ($platformData as $key => $value) {
            switch ($key) {
                case 'id':
                    $platformReturned->setId($value);
                    break;
                case 'name':
                    $platformReturned->setName($value);
                    break;
                case 'hostname':
                    $platformReturned->setHostname($value);
                    break;
                case 'type':
                    $platformReturned->setType($value);
                    break;
                case 'address':
                    $platformReturned->setAddress($value);
                    break;
                case 'parentAddress':
                    $platformReturned->setParentAddress($value);
                    break;
                case 'parentId':
                    $platformReturned->setParentId($value);
                    break;
                case 'serverId':
                    $platformReturned->setServerId($value);
                    break;
                case 'isLinkedToAnotherServer':
                    $platformReturned->setLinkedToAnotherServer($value);
                    break;
            }
        }

        return $platformReturned;
    }

    /**
     * Return a pending platform pending
     *
     * @param array $platformData
     * @return PlatformPending
     */
    public function createPlatformPending(array $platformData): PlatformPending
    {
        $platformReturned = new PlatformPending();
        foreach ($platformData as $key => $value) {
            switch ($key) {
                case 'id':
                    $platformReturned->setId($value);
                    break;
                case 'name':
                    $platformReturned->setName($value);
                    break;
                case 'hostname':
                    $platformReturned->setHostname($value);
                    break;
                case 'type':
                    $platformReturned->setType($value);
                    break;
                case 'address':
                    $platformReturned->setAddress($value);
                    break;
                case 'parentAddress':
                    $platformReturned->setParentAddress($value);
                    break;
                case 'parentId':
                    $platformReturned->setParentId($value);
                    break;
                case 'serverId':
                    $platformReturned->setServerId($value);
                    break;
                case 'isLinkedToAnotherServer':
                    $platformReturned->setLinkedToAnotherServer($value);
                    break;
            }
        }

        return $platformReturned;
    }
}
