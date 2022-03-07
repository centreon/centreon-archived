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

namespace Centreon\Infrastructure\Engine;

use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationRepositoryInterface;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * This class is designed to represent the MariaDb repository to manage Engine configuration.
 *
 * @package Centreon\Infrastructure\Engine
 */
class EngineConfigurationRepositoryRDB extends AbstractRepositoryDRB implements EngineConfigurationRepositoryInterface
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
     * @inheritDoc
     */
    public function findEngineConfigurationByHost(Host $host): ?EngineConfiguration
    {
        if ($host->getId() === null) {
            return null;
        }
        $request = $this->translateDbName(
            'SELECT * FROM `:db`.cfg_nagios cfg
            INNER JOIN `:db`.ns_host_relation nsr
                ON nsr.nagios_server_id = cfg.nagios_server_id
            WHERE nsr.host_host_id = :host_id'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
        $statement->execute();

        if (($records = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            return (new EngineConfiguration())
                ->setId((int) $records['nagios_id'])
                ->setName($records['nagios_name'])
                ->setIllegalObjectNameCharacters($records['illegal_object_name_chars'])
                ->setMonitoringServerId((int) $records['nagios_server_id'])
                ->setNotificationsEnabledOption((int) $records['enable_notifications']);
        }
        return null;
    }

    /**
     * {@inheritDoc}
     * @throws \PDOException
     */
    public function findEngineConfigurationByMonitoringServerId(int $monitoringServerId): ?EngineConfiguration
    {
        $statement = $this->db->prepare(
            $this->translateDbName(
                'SELECT cfg.nagios_id, cfg.nagios_name, cfg.illegal_object_name_chars, cfg.nagios_server_id
                FROM `:db`.cfg_nagios cfg
                INNER JOIN `:db`.ns_host_relation nsr
                    ON nsr.nagios_server_id = :monitoring_server_id'
            )
        );
        $statement->bindValue(':monitoring_server_id', $monitoringServerId, \PDO::PARAM_INT);
        $statement->execute();

        if (($records = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            return (new EngineConfiguration())
                ->setId((int) $records['nagios_id'])
                ->setName($records['nagios_name'])
                ->setIllegalObjectNameCharacters($records['illegal_object_name_chars'])
                ->setMonitoringServerId((int) $records['nagios_server_id']);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function findEngineConfigurationByName(string $engineName): ?EngineConfiguration
    {
        $request = $this->translateDbName(
            'SELECT cfg.* FROM `:db`.cfg_nagios cfg
            INNER JOIN `:db`.nagios_server server
                ON server.id = cfg.nagios_server_id
            WHERE server.name = :engine_name'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':engine_name', $engineName, \PDO::PARAM_STR);
        $statement->execute();

        if (($records = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            return (new EngineConfiguration())
                ->setId((int) $records['nagios_id'])
                ->setName($records['nagios_name'])
                ->setIllegalObjectNameCharacters($records['illegal_object_name_chars'])
                ->setMonitoringServerId((int) $records['nagios_server_id']);
        }
        return null;
    }
}
