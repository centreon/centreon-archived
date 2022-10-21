<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Security\ProviderConfiguration\Domain\OpenId\Model;

use Core\Security\AccessGroup\Domain\Model\AccessGroup;

class AuthorizationRule
{
    /**
     * @param string $claimValue
     * @param AccessGroup $accessGroup
     * @param int $priority
     */
    public function __construct(private string $claimValue, private AccessGroup $accessGroup, private int $priority)
    {
    }

    /**
     * @return string
     */
    public function getClaimValue(): string
    {
        return $this->claimValue;
    }

    /**
     * @return AccessGroup
     */
    public function getAccessGroup(): AccessGroup
    {
        return $this->accessGroup;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }
}
