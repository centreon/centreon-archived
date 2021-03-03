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
use Centreon\Domain\PlatformTopology\Model\PlatformPending;
use Centreon\Domain\PlatformTopology\Model\PlatformRegistered;

class PlatformTopologyFactoryRDB
{
    /**
     * Create a platform entity depending on the pending status
     * @param array<int|string> $platformData
     * @return PlatformInterface
     */
    public static function create(array $platformData): PlatformInterface
    {
        if ('1' === $platformData['pending']) {
            return self::createPlatformPending($platformData);
        }

        return self::createPlatformRegistered($platformData);
    }

    /**
     * Return a Registered platform entity
     * @param array<int|string> $platformData
     * @return PlatformRegistered
     */
    private static function createPlatformRegistered(array $platformData): PlatformRegistered
    {
        $platformReturned = new PlatformRegistered();
        foreach ($platformData as $key => $value) {
            switch ($key) {
                case 'id':
                    $value = (int) $value;
                    if (null !== $value && $value > 0) {
                        $platformReturned->setId($value);
                    }
                    break;
                case 'name':
                    if (null !== $value && is_string($value)) {
                        $platformReturned->setName($value);
                    }
                    break;
                case 'hostname':
                    if (null !== $value && is_string($value)) {
                        $platformReturned->setHostname($value);
                    }
                    break;
                case 'type':
                    if (null !== $value && is_string($value)) {
                        $platformReturned->setType($value);
                    }
                    break;
                case 'address':
                    if (null !== $value && is_string($value)) {
                        $platformReturned->setAddress($value);
                    }
                    break;
                case 'parent_id':
                    $value = (int) $value;
                    if (null !== $value && $value > 0) {
                        $platformReturned->setParentId($value);
                    }
                    break;
                case 'server_id':
                    $value = (int) $value;
                    if (null !== $value && $value > 0) {
                        $platformReturned->setServerId($value);
                    }
                    break;
            }
        }

        return $platformReturned;
    }

    /**
     * Return a pending platform pending
     * @param array<int|string> $platformData
     * @return PlatformPending
     */
    private static function createPlatformPending(array $platformData): PlatformPending
    {
        $platformReturned = new PlatformPending();
        foreach ($platformData as $key => $value) {
            switch ($key) {
                case 'id':
                    $value = (int) $value;
                    if (null !== $value && $value > 0) {
                        $platformReturned->setId($value);
                    }
                    break;
                case 'name':
                    if (null !== $value && is_string($value)) {
                        $platformReturned->setName($value);
                    }
                    break;
                case 'hostname':
                    if (null !== $value && is_string($value)) {
                        $platformReturned->setHostname($value);
                    }
                    break;
                case 'type':
                    if (null !== $value && is_string($value)) {
                        $platformReturned->setType($value);
                    }
                    break;
                case 'address':
                    if (null !== $value && is_string($value)) {
                        $platformReturned->setAddress($value);
                    }
                    break;
                case 'parent_id':
                    $value = (int) $value;
                    if (null !== $value && $value > 0) {
                        $platformReturned->setParentId($value);
                    }
                    break;
                case 'server_id':
                    $value = (int) $value;
                    if (null !== $value && $value > 0) {
                        $platformReturned->setServerId($value);
                    }
                    break;
            }
        }

        return $platformReturned;
    }
}
