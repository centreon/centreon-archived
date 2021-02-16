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

use Centreon\Domain\PlatformTopology\Interfaces\PlatformInterface;
use Centreon\Domain\PlatformTopology\Model\Platform;
use Centreon\Domain\PlatformTopology\Model\PlatformPending;

class PlatformTopologyFactoryRDB
{
    public function __construct()
    {
    }

    /**
     * @param array $platformData
     * @return PlatformInterface
     */
    public function create(array $platformData): PlatformInterface
    {
        if (true === $platformData['pending']) {
            $platform = new PlatformPending();
        } else {
            $platform = new Platform();
        }
        foreach ($platformData as $key => $value) {
            switch ($key) {
                case 'id':
                    $platform->setId($value);
                    break;
                case 'address':
                    $platform->setAddress($value);
                    break;
                case 'hostname':
                    $platform->setHostname($value);
                    break;
                case 'name':
                    $platform->setName($value);
                    break;
                case 'type':
                    $platform->setType($value);
                    break;
                case 'parent_id':
                    $platform->setParentId($value);
                    break;
                case 'server_id':
                    $platform->setServerId($value);
                    break;
            }
        }

        return $platform;
    }
}