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
            $this->translateDbName(
                "UPDATE `:db`.password_security_policy SET " .
                "password_length = :passwordLength, uppercase_characters = :uppercase, " .
                "lowercase_characters = :lowercase, integer_characters = :integer, special_characters = :special, " .
                "attempts = :attempts, blocking_duration = :blockingDuration, " .
                "password_expiration = :passwordExpiration, delay_before_new_password = :delayBeforeNewPassword, " .
                "can_reuse_passwords = :canReusePasswords"
            )
        );
        $statement->bindValue(':passwordLength', $securityPolicy->getPasswordMinimumLength(), \PDO::PARAM_INT);
        $statement->bindValue(':uppercase', $securityPolicy->hasUppercase() ? '1' : '0', \PDO::PARAM_STR);
        $statement->bindValue(':lowercase', $securityPolicy->hasLowercase() ? '1' : '0', \PDO::PARAM_STR);
        $statement->bindValue(':integer', $securityPolicy->hasNumber() ? '1' : '0', \PDO::PARAM_STR);
        $statement->bindValue(':special', $securityPolicy->hasSpecialCharacter() ? '1' : '0', \PDO::PARAM_STR);
        $statement->bindValue(':attempts', $securityPolicy->getAttempts(), \PDO::PARAM_INT);
        $statement->bindValue(':blockingDuration', $securityPolicy->getBlockingDuration(), \PDO::PARAM_INT);
        $statement->bindValue(':passwordExpiration', $securityPolicy->getPasswordExpiration(), \PDO::PARAM_INT);
        $statement->bindValue(':delayBeforeNewPassword', $securityPolicy->getDelayBeforeNewPassword(), \PDO::PARAM_INT);
        $statement->bindValue(':canReusePasswords', $securityPolicy->canReusePasswords() ? '1' : '0', \PDO::PARAM_STR);
        $statement->execute();
    }
}
