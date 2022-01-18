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

use Centreon\Domain\Log\LoggerTrait;
use Core\Domain\Security\ProviderConfiguration\Local\Model\Configuration;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Infrastructure\Security\ProviderConfiguration\Local\Repository\DbConfigurationFactory;
use Core\Infrastructure\Security\ProviderConfiguration\Local\Repository\ConfigurationException;
use Core\Application\Security\ProviderConfiguration\Local\Repository\ReadConfigurationRepositoryInterface;
use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;

class DbReadConfigurationRepository extends AbstractRepositoryDRB implements ReadConfigurationRepositoryInterface
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
    public function findConfiguration(): ?Configuration
    {
        $statement = $this->db->query(
            "SELECT `custom_configuration`
            FROM `provider_configuration`
            WHERE `name` = 'local'"
        );

        $configuration = null;
        if ($statement !== false && $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->validateReadCustomConfiguration($result['custom_configuration']);
            $result['custom_configuration'] = json_decode($result['custom_configuration'], true);
            $configuration = DbConfigurationFactory::createFromRecord($result);
        }

        return $configuration;
    }

    /**
     * Validate the custom configuration format
     *
     * @param string $configuration provider custom configuration
     * @throws ConfigurationException
     */
    private function validateReadCustomConfiguration(string $configuration): void
    {
        $decodedConfiguration = json_decode($configuration, true);

        if (is_array($decodedConfiguration) === false) {
            $this->critical('Local provider custom configuration is not a valid json');
            throw ConfigurationException::errorWhileReadingConfiguration();
        }

        $decodedConfiguration = Validator::arrayToObjectRecursive($decodedConfiguration);
        $validator = new Validator();
        $validator->validate(
            $decodedConfiguration,
            (object) [
                '$ref' => 'file://' . __DIR__ . '/CustomConfigurationSchema.json',
            ],
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );

        if ($validator->isValid() === false) {
            $message = '';
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            $this->critical($message);
            throw ConfigurationException::errorWhileReadingConfiguration();
        }
    }
}
