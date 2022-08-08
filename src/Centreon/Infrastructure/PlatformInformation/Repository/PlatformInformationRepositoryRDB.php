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

namespace Centreon\Infrastructure\PlatformInformation\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\PlatformInformation\Model\PlatformInformation;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationRepositoryInterface;
use Centreon\Infrastructure\PlatformInformation\Repository\Model\PlatformInformationFactoryRDB;

/**
 * This class is designed to manage the repository of the platform topology requests
 *
 * @package Centreon\Infrastructure\PlatformTopology
 */
class PlatformInformationRepositoryRDB extends AbstractRepositoryDRB implements PlatformInformationRepositoryInterface
{
    /**
     * Encryption Key.
     *
     * @var string|null
     */
    private $encryptionFirstKey;

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
    public function setEncryptionFirstKey(?string $encryptionFirstKey): void
    {
        $this->encryptionFirstKey = $encryptionFirstKey;
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

                if ('isRemote' === $row['key'] || 'apiPeerValidation' === $row['key']) {
                    $row['value'] = $row['value'] === 'yes';
                }

                $result[$row['key']] = $row['value'];
            }
            if (!empty($result)) {
                /**
                 * @var PlatformInformation $platformInformation
                 */
                $platformInformationFactoryRDB = new PlatformInformationFactoryRDB($this->encryptionFirstKey);
                $platformInformation = $platformInformationFactoryRDB->create($result);
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

            $deletedKeys = [];

            array_push($deletedKeys, "'isRemote'", "'isCentral'", "'apiPeerValidation'");
            if ($platformInformation->isRemote() === true) {
                $insertQuery .= "('isRemote', 'yes'), ('isCentral', 'no'), ";
                $this->prepareInsertQueryString($platformInformation, $insertQuery, $queryParameters);
            } else {
                /**
                 * delete all keys related to a remote configuration, reinsert isCentral and isRemote.
                 */
                array_push(
                    $deletedKeys,
                    "'authorizedMaster'",
                    "'apiUsername'",
                    "'apiCredentials'",
                    "'apiScheme'",
                    "'apiPort'",
                    "'apiPath'"
                );
                $insertQuery .= "('isCentral', 'yes'),  ('isRemote', 'no')";
            }

            $insertStatement = $this->db->prepare($this->translateDbName($insertQuery));
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
            $deletedKeyList = implode(',', $deletedKeys);
            /**
             * Delete only the updated key
             */
            $this->db->query($this->translateDbName("DELETE FROM `:db`.informations WHERE `key` IN ($deletedKeyList)"));

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

    /**
     * this method is designed to prepare the insertQuery, based on the provided Information.
     *
     * @param PlatformInformation $platformInformation
     * @param string $insertQuery
     * @param array<string,array<int,mixed>> $queryParameters
     * @return void
     */
    private function prepareInsertQueryString(
        PlatformInformation $platformInformation,
        string &$insertQuery,
        array &$queryParameters
    ): void {
        $queryValues = [];
        if ($platformInformation->getCentralServerAddress() !== null) {
            $queryParameters['authorizedMaster'] = [
                \PDO::PARAM_STR => $platformInformation->getCentralServerAddress()
            ];
            $queryValues[] = "('authorizedMaster', :authorizedMaster)";
        }
        if ($platformInformation->getApiUsername() !== null) {
            $queryParameters['apiUsername'] = [
                \PDO::PARAM_STR => $platformInformation->getApiUsername()
            ];
            $queryValues[] = "('apiUsername', :apiUsername)";
        }
        if ($platformInformation->getEncryptedApiCredentials() !== null) {
            $queryParameters['apiCredentials'] = [
                \PDO::PARAM_STR => $platformInformation->getEncryptedApiCredentials()
            ];
            $queryValues[] = "('apiCredentials', :apiCredentials)";
        }
        if ($platformInformation->getApiScheme() !== null) {
            $queryParameters['apiScheme'] = [
                \PDO::PARAM_STR => $platformInformation->getApiScheme()
            ];
            $queryValues[] = "('apiScheme', :apiScheme)";
        }
        if ($platformInformation->getApiPort() !== null) {
            $queryParameters['apiPort'] = [
                \PDO::PARAM_INT => $platformInformation->getApiPort()
            ];
            $queryValues[] = "('apiPort', :apiPort)";
        }
        if ($platformInformation->getApiPath() !== null) {
            $queryParameters['apiPath'] = [
                \PDO::PARAM_STR => $platformInformation->getApiPath()
            ];
            $queryValues[] = "('apiPath', :apiPath)";
        }
        if ($platformInformation->hasApiPeerValidation() !== null) {
            /**
             * This key isn't put into queryParameters, so we add it directly to the deletedKey array
             */
            $deletedKeys[] = "'apiPeerValidation'";
            if ($platformInformation->hasApiPeerValidation() === true) {
                $queryValues[] = "('apiPeerValidation', 'yes')";
            } else {
                $queryValues[] = "('apiPeerValidation', 'no')";
            }
        }

        $insertQuery .= implode(', ', $queryValues);
    }
}
