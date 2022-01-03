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

namespace Core\Infrastructure\Security\Repository;

use Core\Domain\Security\Model\SecurityPolicy;
use Centreon\Domain\Common\Assertion\AssertionException;

class DbSecurityPolicyFactory
{
    /**
     * @param array<string, mixed> $data
     * @return SecurityPolicy
     * @throws AssertionException
     */
    public static function createFromRecord(array $data): SecurityPolicy
    {
        return new SecurityPolicy(
            (int) $data['password_length'],
            (bool) $data['uppercase_characters'],
            (bool) $data['lowercase_characters'],
            (bool) $data['integer_characters'],
            (bool) $data['special_characters'],
            (bool) $data['can_reuse_password'],
            $data['attempts'] !== null ? (int) $data['attempts'] : null,
            $data['blocking_duration'] !== null ? (int) $data['blocking_duration'] : null,
            $data['password_expiration'] !== null ? (int) $data['password_expiration'] : null,
            $data['delay_before_new_password'] !== null ? (int) $data['delay_before_new_password'] : null,
        );
    }
}
