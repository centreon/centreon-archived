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

namespace Centreon\Domain\PlatformTopology\Model;

class PlatformPendingFactory
{
    public function __construct()
    {
    }

    public function createPlatformPending(array $platformData): PlatformPending
    {
        $platformToRegister = new PlatformPending();
        foreach ($platformData as $key => $value) {
            switch ($key) {
                case 'address':
                    $platformToRegister->setAddress($value);
                    break;
                case 'hostname':
                    $platformToRegister->setHostname($value);
                    break;
                case 'name':
                    $platformToRegister->setName($value);
                    break;
                case 'type':
                    $platformToRegister->setType($value);
                    break;
                case 'parent_address':
                    $platformToRegister->setParentAddress($value);
                    break;
                case 'pending':
                    $platformToRegister->setPending(true);
                    break;
            }
        }
        return $platformToRegister;
    }
}