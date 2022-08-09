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

namespace Core\Security\ProviderConfiguration\Domain\Local\Model;

final class CustomConfiguration implements LocalCustomConfigurationInterface
{
    /** @var SecurityPolicy  */
    private SecurityPolicy $securityPolicy;

    /**
     * @param SecurityPolicy $securityPolicy
     * @return $this
     */
    public static function createFromSecurityPolicy(SecurityPolicy $securityPolicy): self
    {
        $self = new self();
        $self->securityPolicy = $securityPolicy;

        return $self;
    }

    /**
     * @param array $json
     * @param array $excludedUserAliases
     * @return CustomConfiguration
     */
    public static function createFromJsonArray(array $json, array $excludedUserAliases): self
    {
        $securityPolicy = new SecurityPolicy(
            $json['password_security_policy']['password_length'],
            $json['password_security_policy']['has_uppercase_characters'],
            $json['password_security_policy']['has_lowercase_characters'],
            $json['password_security_policy']['has_numbers'],
            $json['password_security_policy']['has_special_characters'],
            $json['password_security_policy']['can_reuse_passwords'],
            $json['password_security_policy']['attempts'],
            $json['password_security_policy']['blocking_duration'],
            $json['password_security_policy']['password_expiration_delay'],
            $excludedUserAliases,
            $json['password_security_policy']['delay_before_new_password'],
        );

        $self = new self();
        $self->securityPolicy = $securityPolicy;

        return $self;
    }

    /**
     * @return SecurityPolicy
     */
    public function getSecurityPolicy(): SecurityPolicy
    {
        return $this->securityPolicy;
    }
}
