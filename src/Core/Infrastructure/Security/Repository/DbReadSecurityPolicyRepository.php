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

use Centreon\Domain\Log\LoggerTrait;
use Core\Domain\Security\Model\SecurityPolicy;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Infrastructure\Security\Repository\DbSecurityPolicyFactory;
use Core\Infrastructure\Security\Repository\SecurityPolicyException;
use Core\Application\Security\Repository\ReadSecurityPolicyRepositoryInterface;
use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;

class DbReadSecurityPolicyRepository extends AbstractRepositoryDRB implements ReadSecurityPolicyRepositoryInterface
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
    public function findSecurityPolicy(): ?SecurityPolicy
    {
        $statement = $this->db->query(
            "SELECT `configuration` FROM `provider_configuration` WHERE `name` = 'local'"
        );

        $securityPolicy = null;
        if ($statement !== false && $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->validateReadSecurityPolicy($result['configuration']);
            $securityPolicy = DbSecurityPolicyFactory::createFromRecord(
                json_decode($result['configuration'], true)['password_security_policy']
            );
        }

        return $securityPolicy;
    }

    /**
     * Validate the password security policy format
     *
     * @param string $configuration provider configuration
     * @throws SecurityPolicyException
     */
    private function validateReadSecurityPolicy(string $configuration): void
    {
        $decodedConfiguration = json_decode($configuration, true);

        if (is_array($decodedConfiguration) === false) {
            $this->critical('Password security policy configuration is not a valid json');
            SecurityPolicyException::errorWhileReadingPasswordSecurityPolicy();
        }

        $decodedConfiguration = Validator::arrayToObjectRecursive($decodedConfiguration);
        $validator = new Validator();
        $validator->validate(
            $decodedConfiguration,
            (object) [
                '$ref' => 'file://' . __DIR__ . '/SeurityPolicySchema.json',
            ],
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );

        if ($validator->isValid() === false) {
            $message = '';
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            $this->critical($message);
            SecurityPolicyException::errorWhileReadingPasswordSecurityPolicy();
        }
    }
}
