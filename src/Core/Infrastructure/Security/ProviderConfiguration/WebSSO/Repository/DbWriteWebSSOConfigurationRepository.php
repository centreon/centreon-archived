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

namespace Core\Infrastructure\Security\ProviderConfiguration\WebSSO\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Domain\Security\ProviderConfiguration\WebSSO\Model\WebSSOConfiguration;
use Core\Application\Security\ProviderConfiguration\WebSSO\Repository\WriteWebSSOConfigurationRepositoryInterface;

class DbWriteWebSSOConfigurationRepository extends AbstractRepositoryDRB implements
    WriteWebSSOConfigurationRepositoryInterface
{
    use LoggerTrait;

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
    public function updateConfiguration(WebSSOConfiguration $configuration): void
    {
        $this->info('Updating WebSSO Configuration in DBMS');
        $statement = $this->db->prepare(
            $this->translateDbName(
                "UPDATE `:db`.`provider_configuration` SET
                `custom_configuration` = :customConfiguration, `is_active` = :isActive, `is_forced` = :isForced
                WHERE `name`='web-sso'"
            )
        );
        $statement->bindValue(
            ':customConfiguration',
            json_encode($this->buildCustomConfigurationFromWebSSOConfiguration($configuration)),
            \PDO::PARAM_STR
        );
        $statement->bindValue(':isActive', $configuration->isActive() ? '1' : '0', \PDO::PARAM_STR);
        $statement->bindValue(':isForced', $configuration->isForced() ? '1' : '0', \PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * @param WebSSOConfiguration $configuration
     * @return array<string,mixed>
     */
    private function buildCustomConfigurationFromWebSSOConfiguration(WebSSOConfiguration $configuration): array
    {
        return [
            'trusted_client_addresses' => $configuration->getTrustedClientAddresses(),
            'blacklist_client_addresses' => $configuration->getBlacklistClientAddresses(),
            'login_header_attribute' => $configuration->getLoginHeaderAttribute(),
            'pattern_matching_login' => $configuration->getPatternMatchingLogin(),
            'pattern_replace_login' => $configuration->getPatternReplaceLogin()
        ];
    }
}
