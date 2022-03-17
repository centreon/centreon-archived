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

namespace Core\Infrastructure\Security\ProviderConfiguration\Local\Repository;

use Core\Domain\Security\ProviderConfiguration\Local\Model\Configuration as LocalProviderConfiguration;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\Security\ProviderConfiguration\Local\Repository\WriteConfigurationRepositoryInterface;

class DbWriteConfigurationRepository extends AbstractRepositoryDRB implements WriteConfigurationRepositoryInterface
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
    public function updateConfiguration(
        LocalProviderConfiguration $localConfiguration,
        array $excludedUserIds
    ): void {
        $beginInTransaction = $this->db->inTransaction();

        try {
            if ($beginInTransaction === false) {
                $this->db->beginTransaction();
            }

            $this->updateCustomConfiguration($localConfiguration);

            $this->updateExcludedUsers($excludedUserIds);

            if ($beginInTransaction === false && $this->db->inTransaction()) {
                $this->db->commit();
            }
        } catch (\Exception $e) {
            if ($beginInTransaction === false && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Update custom configuration
     *
     * @param LocalProviderConfiguration $localConfiguration
     */
    private function updateCustomConfiguration(LocalProviderConfiguration $localConfiguration): void
    {
        $configuration = json_encode([
            "password_security_policy" => [
                "password_length" => $localConfiguration->getSecurityPolicy()->getPasswordMinimumLength(),
                "has_uppercase_characters" => $localConfiguration->getSecurityPolicy()->hasUppercase(),
                "has_lowercase_characters" => $localConfiguration->getSecurityPolicy()->hasLowercase(),
                "has_numbers" => $localConfiguration->getSecurityPolicy()->hasNumber(),
                "has_special_characters" => $localConfiguration->getSecurityPolicy()->hasSpecialCharacter(),
                "attempts" => $localConfiguration->getSecurityPolicy()->getAttempts(),
                "blocking_duration" => $localConfiguration->getSecurityPolicy()->getBlockingDuration(),
                "password_expiration_delay" => $localConfiguration->getSecurityPolicy()->getPasswordExpirationDelay(),
                "delay_before_new_password" => $localConfiguration->getSecurityPolicy()->getDelayBeforeNewPassword(),
                "can_reuse_passwords" => $localConfiguration->getSecurityPolicy()->canReusePasswords(),
            ],
        ]);

        $statement = $this->db->prepare(
            $this->translateDbName(
                "UPDATE `:db`.`provider_configuration`
                SET `custom_configuration` = :localProviderConfiguration
                WHERE `name` = 'local'"
            )
        );
        $statement->bindValue(':localProviderConfiguration', $configuration, \PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * Update excluded users
     *
     * @param int[] $excludedUserIds
     */
    private function updateExcludedUsers(array $excludedUserIds): void
    {
        $this->deleteExcludedUsers();

        $this->addExcludedUsers($excludedUserIds);
    }

    /**
     * Delete excluded users
     */
    private function deleteExcludedUsers(): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName(
                "DELETE pceu FROM `:db`.`password_expiration_excluded_users` pceu
                INNER JOIN `:db`.`provider_configuration` pc ON pc.`id` = pceu.`provider_configuration_id`
                  AND pc.`name` = 'local'"
            )
        );

        $statement->execute();
    }

    /**
     * Add excluded users
     *
     * @param int[] $excludedUserIds
     */
    private function addExcludedUsers(array $excludedUserIds): void
    {
        if (empty($excludedUserIds)) {
            return;
        }

        $query = "INSERT INTO `:db`.`password_expiration_excluded_users`
            (`provider_configuration_id`, `user_id`) ";

        $subQueries = [];
        foreach ($excludedUserIds as $userId) {
            $subQueries[] = "(SELECT pc.`id`, :user_{$userId} FROM `:db`.`provider_configuration` pc
                WHERE pc.`name` = 'local')";
        }

        $query .= implode(' UNION ', $subQueries);

        $statement = $this->db->prepare(
            $this->translateDbName($query)
        );

        foreach ($excludedUserIds as $userId) {
            $statement->bindValue(":user_{$userId}", $userId, \PDO::PARAM_INT);
        }

        $statement->execute();
    }
}
