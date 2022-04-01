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

namespace Core\Application\Security\User\Repository;

use Core\Domain\Configuration\User\Model\User as ConfigurationUser;
use Core\Domain\Security\User\Model\User;
use Core\Infrastructure\Security\User\Repository\DbWriteUserRepository;

interface WriteUserRepositoryInterface
{
    /**
     * Update user blocking information (login attempts and blocking time)
     *
     * @param User $user
     */
    public function updateBlockingInformation(User $user): void;

    /**
     * Renew password of user.
     *
     * @param User $user
     */
    public function renewPassword(User $user): void;
}
