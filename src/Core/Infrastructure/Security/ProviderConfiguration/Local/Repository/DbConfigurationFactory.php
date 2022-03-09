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

namespace Core\Infrastructure\Security\ProviderConfiguration\Local\Repository;

use Core\Domain\Security\ProviderConfiguration\Local\Model\Configuration;
use Core\Domain\Security\ProviderConfiguration\Local\Model\SecurityPolicy;

class DbConfigurationFactory
{
    /**
     * @param array<string,mixed> $configuration
     * @param array<string,mixed> $customConfiguration
     * @param array<string,mixed> $excludedUserAliases
     * @return Configuration
     */
    public static function createFromRecord(
        array $configuration,
        array $customConfiguration,
        array $excludedUserAliases
    ): Configuration {
        $excludedUserAliases = array_map(
            fn ($user) => $user['contact_alias'],
            $excludedUserAliases
        );

        $securityPolicy = new SecurityPolicy(
            $customConfiguration['password_security_policy']['password_length'],
            $customConfiguration['password_security_policy']['has_uppercase_characters'],
            $customConfiguration['password_security_policy']['has_lowercase_characters'],
            $customConfiguration['password_security_policy']['has_numbers'],
            $customConfiguration['password_security_policy']['has_special_characters'],
            $customConfiguration['password_security_policy']['can_reuse_passwords'],
            $customConfiguration['password_security_policy']['attempts'],
            $customConfiguration['password_security_policy']['blocking_duration'],
            $customConfiguration['password_security_policy']['password_expiration_delay'],
            $excludedUserAliases,
            $customConfiguration['password_security_policy']['delay_before_new_password'],
        );

        $localConfiguration =  (new Configuration($securityPolicy))
            ->setId((int) $configuration['id'])
            ->setName($configuration['name'])
            ->setType($configuration['type'])
            ->setActive((int) $configuration['is_active'] === 1)
            ->setForced((int) $configuration['is_forced'] === 1);

        return $localConfiguration;
    }
}
