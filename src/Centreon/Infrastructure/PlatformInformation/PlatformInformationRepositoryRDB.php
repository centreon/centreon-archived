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
                if ('apiCredentials' === $row['key']) {
                    $row['key'] = 'encryptedApiCredentials';
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

    public function updatePlatformInformation(PlatformInformation $platformInformation): ?PlatformInformation
    {
        try {
            $this->db->beginTransaction();
            $deleteQuery = $this->db->prepare($this->translateDbName('DELETE FROM `:db`.informations'));
            $deleteQuery->execute();
            $insertQuery =  'INSERT INTO `:db`.informations (`key`, `value`) VALUES ';

            $queryParameters = [];
            if ($platformInformation->getVersion() !== null) {
                $queryParameters[':version'] = [
                    \PDO::PARAM_STR => $platformInformation->getVersion()
                ];
                $insertQuery .= "('version', :version), ";
            }
            if ($platformInformation->getAppKey() !== null) {
                $queryParameters[':appKey'] = [
                    \PDO::PARAM_STR => $platformInformation->getAppKey()
                ];
                $insertQuery .= "('appKey', :appKey), ";
            }
            if ($platformInformation->isRemote() === true) {
                $insertQuery .= "('isRemote', 'yes'),  ('isCentral', 'no'), ";

                if ($platformInformation->getCentralServerAddress() !== null) {
                    $queryParameters[':authorizedMaster'] = [
                        \PDO::PARAM_STR => $platformInformation->getCentralServerAddress()
                    ];
                    $insertQuery .= "('authorizedMaster', :authorizedMaster), ";
                }
                if ($platformInformation->getApiUsername() !== null) {
                    $queryParameters[':apiUsername'] = [
                        \PDO::PARAM_STR => $platformInformation->getApiUsername()
                    ];
                    $insertQuery .= "('apiUsername', :apiUsername), ";
                }
                if ($platformInformation->getEncryptedApiCredentials() !== null) {
                    $queryParameters[':apiCredentials'] = [
                        \PDO::PARAM_STR => $platformInformation->getEncryptedApiCredentials()
                    ];
                    $insertQuery .= "('apiCredentials', :apiCredentials), ";
                }
                if ($platformInformation->getApiScheme() !== null) {
                    $queryParameters[':apiScheme'] = [
                        \PDO::PARAM_STR => $platformInformation->getApiScheme()
                    ];
                    $insertQuery .= "('apiScheme', :apiScheme), ";
                }
                if ($platformInformation->getApiPort() !== null) {
                    $queryParameters[':apiPort'] = [
                        \PDO::PARAM_INT => $platformInformation->getApiPort()
                    ];
                    $insertQuery .= "('apiPort', :apiPort), ";
                }
                if ($platformInformation->getApiPath() !== null) {
                    $queryParameters[':apiPath'] = [
                        \PDO::PARAM_STR => $platformInformation->getApiPath()
                    ];
                    $insertQuery .= "('apiPath', :apiPath), ";
                }
                if ($platformInformation->hasApiPeerValidation() === true) {
                    $insertQuery .= "('apiPeerValidation', 'yes')";
                } else {
                    $insertQuery .= "('apiPeerValidation', 'no')";
                }
            } else {
                $insertQuery .= "('isCentral', 'yes'),  ('isRemote', 'no')";
            }

            $statement = $this->db->prepare($this->translateDbName($insertQuery));
            foreach ($queryParameters as $token => $bindParams) {
                foreach ($bindParams as $paramType => $paramValue) {
                    $statement->bindValue($token, $paramValue, $paramType);
                }
            }
            $statement->execute();
        } catch (\Exception $ex) {
            $this->db->rollBack();
            throw new RepositoryException('An error occured while updating the platform');
        }
        $this->db->commit();

        return $this->findPlatformInformation();
    }
}
