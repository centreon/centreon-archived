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
        $statement = $this->db->prepare(
            "UPDATE `:db`.password_security_policy SET " .
            "password_length = :passwordLength, uppercase_characters = :uppercase, lowercase_characters = :lowercase " .
            "integer_characters = :integer, special_characters = :special, attempts = :attempts, " .
            "blocking_duration = :blockingDuration, password_expiration = :passwordExpiration, " .
            "delay_before_new_password = :delayBeforeNewPassword, can_reuse_password = :canReusePassword"
        );
        $statement->bindValue(':passwordLength', $securityPolicy->getPasswordMinimumLength(), \PDO::PARAM_INT);
        $statement->bindValue(':uppercase', (string) $securityPolicy->hasUppercase(), \PDO::PARAM_STR);
        $statement->bindValue(':lowercase', (string) $securityPolicy->hasLowercase(), \PDO::PARAM_STR);
        $statement->bindValue(':integer', (string) $securityPolicy->hasNumber(), \PDO::PARAM_STR);
        $statement->bindValue(':special', (string) $securityPolicy->hasSpecialCharacter(), \PDO::PARAM_STR);
        $statement->bindValue(':attempts', $securityPolicy->getAttempts(), \PDO::PARAM_INT);
        $statement->bindValue(':blockingDuration', $securityPolicy->getBlockingDuration(), \PDO::PARAM_INT);
        $statement->bindValue(':passwordExpiration', $securityPolicy->getPasswordExpiration(), \PDO::PARAM_INT);
        $statement->bindValue(':delayBeforeNewPassword', $securityPolicy->getDelayBeforeNewPassword(), \PDO::PARAM_INT);
        $statement->bindValue(':canReusePassword', (string) $securityPolicy->canReusePassword(), \PDO::PARAM_INT);
        $statement->execute();
    }
}
