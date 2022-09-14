<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Infrastructure\Repository;

use JsonSchema\Validator;
use Centreon\Domain\Log\LoggerTrait;
use JsonSchema\Constraints\Constraint;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\Repository\RepositoryException;

class AbstractRepositoryDRB
{
    use LoggerTrait;

    /**
     * @var DatabaseConnection
     */
    protected $db;

    /**
     * Replace all instances of :dbstg and :db by the real db names.
     * The table names of the database are defined in the services.yaml
     * configuration file.
     *
     * @param string $request Request to translate
     * @return string Request translated
     */
    protected function translateDbName(string $request): string
    {
        return str_replace(
            [':dbstg', ':db'],
            [$this->db->getStorageDbName(), $this->db->getCentreonDbName()],
            $request
        );
    }

    /**
     * Formats the access group ids in string. (values are separated by coma)
     *
     * @param AccessGroup[] $accessGroups
     * @return string
     */
    public function accessGroupIdToString(array $accessGroups): string
    {
        $ids = [];
        foreach ($accessGroups as $accessGroup) {
            $ids[] = $accessGroup->getId();
        }
        return implode(',', $ids);
    }

    /**
     * Validate the Json of a property
     *
     * @param string $jsonRecord The JSON Property to validate
     * @param string $jsonSchemaFilePath The JSON Schema Validation file
     * @throws RepositoryException
     */
    protected function validateJsonRecord(string $jsonRecord, string $jsonSchemaFilePath): void
    {
        $decodedRecord = json_decode($jsonRecord, true);

        if (is_array($decodedRecord) === false) {
            $this->critical('The property get from dbms is not a valid json');
            throw new RepositoryException('Invalid Json format');
        }

        $decodedRecord = Validator::arrayToObjectRecursive($decodedRecord);
        $validator = new Validator();
        $validator->validate(
            $decodedRecord,
            (object) [
                '$ref' => 'file://' . $jsonSchemaFilePath,
            ],
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );

        if ($validator->isValid() === false) {
            $message = '';
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            $this->critical($message);
            throw new RepositoryException('Some properties doesn\'t match the json schema :' . $message);
        }
    }
}
