<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Infrastructure\PlatformInformation;

use Centreon\Domain\Entity\EntityCreator;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\PlatformInformation\PlatformInformation;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationRepositoryInterface;
use Centreon\Domain\Repository\RepositoryException;

/**
 * This class is designed to manage the repository of the platform topology requests
 *
 * @package Centreon\Infrastructure\PlatformTopology
 */
class PlatformInformationRepositoryRDB extends AbstractRepositoryDRB implements PlatformInformationRepositoryInterface
{
    /**
     * PlatformTopologyRepositoryRDB constructor.
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function findPlatformInformation(): ?PlatformInformation
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                SELECT * FROM `:db`.informations
            ')
        );
        $result = [];
        $platformInformation = null;
        if ($statement->execute()) {
            while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                // Renaming one key to be more explicit
                if ('authorizedMaster' === $row['key']) {
                    $row['key'] = 'centralServerAddress';
                }

                //Renaming informations.apiCredentials to PlatformInformation encryptedApiCredentials property.
                if ('apiCredentials' === $row['key']) {
                    $row['key'] = 'encryptedApiCredentials';
                }

                if ('isCentral' === $row['key'] || 'isRemote' === $row['key'] || 'apiPeerValidation' === $row['key']) {
                    $row['value'] = $row['value'] === 'yes';
                }

                // Converting each camelCase key as snake_case
                $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $row['key']));
                $result[$key] = $row['value'];
            }

            if (!empty($result)) {
                /**
                 * @var PlatformInformation $platformInformation
                 */
                $platformInformation = EntityCreator::createEntityByArray(
                    PlatformInformation::class,
                    $result
                );
            }
        }

        return $platformInformation;
    }

    /**
     * @inheritDoc
     */
    public function updatePlatformInformation(PlatformInformation $platformInformation): void
    {
        try {
            $this->db->beginTransaction();
            $insertQuery =  'INSERT INTO `:db`.informations (`key`, `value`) VALUES ';

            /**
             * Store the parameters used to bindValue into the insertStatement
             */
            $queryParameters = [];

            /**
             * Store the key to delete into the deleteStatement
             */
            $deletedKeys = [];

            if ($platformInformation->isRemote() === true) {
                /**
                 * Those 2 keys aren't put into queryParameters, so we add them directly to the deletedKey array
                 */
                array_push($deletedKeys, "'isRemote'", "'isCentral'");
                $insertQuery .= "('isRemote', 'yes'),  ('isCentral', 'no'),";

                if ($platformInformation->getCentralServerAddress() !== null) {
                    $queryParameters['authorizedMaster'] = [
                        \PDO::PARAM_STR => $platformInformation->getCentralServerAddress()
                    ];
                    $insertQuery .= "('authorizedMaster', :authorizedMaster),";
                }
                if ($platformInformation->getApiUsername() !== null) {
                    $queryParameters['apiUsername'] = [
                        \PDO::PARAM_STR => $platformInformation->getApiUsername()
                    ];
                    $insertQuery .= "('apiUsername', :apiUsername),";
                }
                if ($platformInformation->getEncryptedApiCredentials() !== null) {
                    $queryParameters['apiCredentials'] = [
                        \PDO::PARAM_STR => $platformInformation->getEncryptedApiCredentials()
                    ];
                    $insertQuery .= "('apiCredentials', :apiCredentials),";
                }
                if ($platformInformation->getApiScheme() !== null) {
                    $queryParameters['apiScheme'] = [
                        \PDO::PARAM_STR => $platformInformation->getApiScheme()
                    ];
                    $insertQuery .= "('apiScheme', :apiScheme),";
                }
                if ($platformInformation->getApiPort() !== null) {
                    $queryParameters['apiPort'] = [
                        \PDO::PARAM_INT => $platformInformation->getApiPort()
                    ];
                    $insertQuery .= "('apiPort', :apiPort),";
                }
                if ($platformInformation->getApiPath() !== null) {
                    $queryParameters['apiPath'] = [
                        \PDO::PARAM_STR => $platformInformation->getApiPath()
                    ];
                    $insertQuery .= "('apiPath', :apiPath),";
                }
                if ($platformInformation->hasApiPeerValidation() !== null) {
                    /**
                     * This key isn't put into queryParameters, so we add it directly to the deletedKey array
                     */
                    $deletedKeys[] = "'apiPeerValidation'";
                    if ($platformInformation->hasApiPeerValidation() === true) {
                        $insertQuery .= "('apiPeerValidation', 'yes'),";
                    } else {
                        $insertQuery .= "('apiPeerValidation', 'no'),";
                    }
                }
            } else {
                array_push($deletedKeys, "'isRemote'", "'isCentral'");
                $insertQuery .= "('isCentral', 'yes'),  ('isRemote', 'no'),";
            }
            $insertStatement = $this->db->prepare($this->translateDbName(rtrim($insertQuery, ',')));
            foreach ($queryParameters as $key => $bindParams) {
                /**
                 * each key of queryParameters used into the insertStatement also needs to be deleted before
                 * being reinserted. So they're stored into $deletedKeys that is used into the delete query
                 */
                $deletedKeys[] = "'$key'";
                foreach ($bindParams as $paramType => $paramValue) {
                    $insertStatement->bindValue(':' . $key, $paramValue, $paramType);
                }
            }
            $deleteKeyList = implode(',', $deletedKeys);

            /**
             * Delete only the updated key
             */
            $this->db->query($this->translateDbName("DELETE FROM `:db`.informations WHERE `key` IN ($deleteKeyList)"));

            /**
             * Insert updated values
             */
            $insertStatement->execute();

            $this->db->commit();
        } catch (\Exception $ex) {
            $this->db->rollBack();
            throw $ex;
        }
    }
}
