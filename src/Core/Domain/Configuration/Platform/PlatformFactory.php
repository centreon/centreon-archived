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

namespace Core\Domain\Configuration\Platform;

class PlatformFactory
{
    /**
     * @param array{
     *    address: string,
     *    hostname: string|null
     *    name: string,
     *    type: string,
     *    parent: string|null,
     *  }
     * @return NewPlatform
     */
    public static function createNewPlatform(array $data): NewPlatform
    {
        //Check that Address is a correctly formed ip
        if (
            ! filter_var($data['address'], FILTER_VALIDATE_IP)
            && ! filter_var($data['address'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
        ) {
            throw new \Exception('invalid address');
        }
        //Check that type is valid
        if (! in_array($data['type'], AbstractPlatform::ALLOWED_TYPES)) {
            throw new \Exception('invalid type');
        }
        return new NewPlatform($data['address'],$data['hostname'], $data['name'], $data['type']);
    }

    public static function createPlatform(): Platform
    {
        return new Platform();
    }
}