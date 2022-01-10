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
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\Security\Repository\WriteSecurityPolicyRepositoryInterface;

class DbWriteSecurityPolicyRepository extends AbstractRepositoryDRB implements WriteSecurityPolicyRepositoryInterface
{
    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function updateSecurityPolicy(SecurityPolicy $securityPolicy): void
    {
        $configuration = json_encode([
            "password_security_policy" => [
                "password_length" => $securityPolicy->getPasswordMinimumLength(),
                "has_uppercase_characters" => $securityPolicy->hasUppercase(),
                "has_lowercase_characters" => $securityPolicy->hasLowercase(),
                "has_numbers" => $securityPolicy->hasNumber(),
                "has_special_characters" => $securityPolicy->hasSpecialCharacter(),
                "attempts" => $securityPolicy->getAttempts(),
                "blocking_duration" => $securityPolicy->getBlockingDuration(),
                "password_expiration" => $securityPolicy->getPasswordExpiration(),
                "delay_before_new_password" => $securityPolicy->getDelayBeforeNewPassword(),
                "can_reuse_passwords" => $securityPolicy->canReusePasswords(),
            ],
        ]);
        $statement = $this->db->prepare(
            $this->translateDbName(
                "UPDATE `provider_configuration`
                SET `configuration` = :localProviderConfiguration
                WHERE `name` = 'local'"
            )
        );
        $statement->bindValue(':localProviderConfiguration', $configuration, \PDO::PARAM_STR);
        $statement->execute();
    }
}
