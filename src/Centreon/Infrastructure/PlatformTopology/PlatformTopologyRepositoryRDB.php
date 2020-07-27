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

namespace Centreon\Infrastructure\PlatformTopology;

use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
use Centreon\Domain\PlatformTopology\PlatformTopology;;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * This class is designed to manage the repository of the platform topology requests
 *
 * @package Centreon\Infrastructure\PlatformTopology
 */
class PlatformTopologyRepositoryRDB extends AbstractRepositoryDRB implements PlatformTopologyRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * Initialized by the dependency injector.
     *
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function setSqlRequestTranslator(SqlRequestParametersTranslator $sqlRequestTranslator): void
    {
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(
                RequestParameters::CONCORDANCE_MODE_STRICT
            );
    }

    /**
     * {@inheritDoc}
     */
    public function addPlatformToTopology(PlatformTopology $platformTopology): void
    {
        $request = $this->translateDbName('
            INSERT INTO `:db`.platform_topology (`ip_address`, `hostname`, `server_type`, `parent`)
            VALUES (:serverAddress, :serverName, :serverType, :serverParent)
        ');

        $statement = $this->db->prepare($request);
        $statement->bindValue(':serverAddress', $platformTopology->getServerAddress(), \PDO::PARAM_STR);
        $statement->bindValue(':serverName', $platformTopology->getServerName(), \PDO::PARAM_STR);
        $statement->bindValue(':serverType', $platformTopology->getServerType(), \PDO::PARAM_INT);
        $statement->bindValue(':serverParent', $platformTopology->getServerParentAddress(), \PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function checkUniquenessInPlatformTopology(
        string $serverAddress,
        string $serverName
    ): bool
    {
        $request = $this->translateDbName('
            SELECT * FROM `:db`.platform_topology WHERE ip.address = :serverAddress AND hostname = :serverName
        ');

        $this->sqlRequestTranslator->addSearchValue(':serverAddress', [\PDO::PARAM_STR => $serverAddress]);
        $this->sqlRequestTranslator->addSearchValue(':serverName', [\PDO::PARAM_STR => $serverName]);

        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();

        return (false !== $statement->fetch(\PDO::FETCH_ASSOC));
    }
}
