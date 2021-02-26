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

namespace Centreon\Domain\PlatformTopology\Exception;

/**
 * This class is designed to represent a business exception in the 'Platform status' context.
 *
 * @package Centreon\Domain\PlatformTopology\Exception
 */
class PlatformTopologyConflictException extends \Exception
{
    /**
     * Fail to found the platform on the central type parent
     * @param string $name
     * @param string $address
     * @return self
     */
    public static function notFoundOnCentral(string $name, string $address): self
    {
        return new self(
            sprintf(
                _("The platform '%s'@'%s' cannot be found on the Central."),
                $name,
                $address
            )
        );
    }
}
