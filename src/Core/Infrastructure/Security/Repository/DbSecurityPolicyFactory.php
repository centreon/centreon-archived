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

namespace Core\Infrastructure\Security\Repository;

use Core\Domain\Security\Model\SecurityPolicy;

class DbSecurityPolicyFactory
{
    /**
     * @param array<string,mixed> securityPolicyConfiguration
     * @return SecurityPolicy
     */
    public static function createFromRecord(array $securityPolicyConfiguration): SecurityPolicy
    {
        return new SecurityPolicy(
            $securityPolicyConfiguration['password_length'],
            $securityPolicyConfiguration['has_uppercase_characters'],
            $securityPolicyConfiguration['has_lowercase_characters'],
            $securityPolicyConfiguration['has_numbers'],
            $securityPolicyConfiguration['has_special_characters'],
            $securityPolicyConfiguration['can_reuse_passwords'],
            $securityPolicyConfiguration['attempts'],
            $securityPolicyConfiguration['blocking_duration'],
            $securityPolicyConfiguration['password_expiration'],
            $securityPolicyConfiguration['delay_before_new_password'],
        );
    }
}
