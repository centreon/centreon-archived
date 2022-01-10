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
     * @param string $data
     * @return SecurityPolicy
     * @throws AssertionException
     */
    public static function createFromRecord(string $data): SecurityPolicy
    {
        $securityPolicyData = json_decode($data, true)['password_security_policy'];

        return new SecurityPolicy(
            $securityPolicyData['password_length'],
            $securityPolicyData['has_uppercase_characters'],
            $securityPolicyData['has_lowercase_characters'],
            $securityPolicyData['has_numbers'],
            $securityPolicyData['has_special_characters'],
            $securityPolicyData['can_reuse_passwords'],
            $securityPolicyData['attempts'],
            $securityPolicyData['blocking_duration'],
            $securityPolicyData['password_expiration'],
            $securityPolicyData['delay_before_new_password'],
        );
    }
}
