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

namespace Centreon\Infrastructure\ServiceConfiguration;

use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationRepositoryInterface;
use Centreon\Domain\ServiceConfiguration\Service;
use Centreon\Domain\ServiceConfiguration\ServiceMacro;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

class ServiceConfigurationRepositoryRDB extends AbstractRepositoryDRB implements ServiceConfigurationRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    public function __construct(DatabaseConnection $db, SqlRequestParametersTranslator $sqlRequestTranslator)
    {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT);
    }

    /**
     * @inheritDoc
     */
    public function findService(int $serviceId): ?Service
    {
        $request = $this->translateDbName(
            'SELECT service_id AS id, service_template_model_stm_id AS template_id, display_name AS name,
            service_description AS description, service_locked AS is_locked, service_register AS is_registered,
            service_activate AS is_activated
            FROM `:db`.service
            WHERE service_id = :service_id'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':service_id', $serviceId, \PDO::PARAM_INT);
        $statement->execute();
        if (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            return EntityCreator::createEntityByArray(
                Service::class,
                $record
            );
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function findOnDemandServiceMacros(int $serviceId, bool $isUsingInheritance = false): array
    {
        if ($isUsingInheritance) {
            $request = $this->translateDbName(
                'WITH RECURSIVE inherite AS (
                    SELECT srv.service_id, srv.service_template_model_stm_id AS template_id,
                        demand.svc_macro_id AS macro_id, demand.svc_macro_name AS name, 0 AS level
                    FROM `:db`.service srv
                    LEFT JOIN `:db`.on_demand_macro_service demand
                        ON srv.service_id = demand.svc_svc_id
                    WHERE service_id = :service_id
                    UNION
                    SELECT srv.service_id, srv.service_template_model_stm_id AS template_id,
                        demand.svc_macro_id AS macro_id, demand.svc_macro_name AS name, inherite.level + 1
                    FROM `:db`.service srv
                    INNER JOIN inherite
                        ON inherite.template_id = srv.service_id
                    LEFT JOIN `:db`.on_demand_macro_service demand
                        ON srv.service_id = demand.svc_svc_id
                )
                SELECT demand.svc_macro_id AS id, demand.svc_macro_name AS name, demand.svc_macro_value AS `value`,
                  demand.macro_order AS `order`, demand.description, demand.svc_svc_id AS service_id,
                    CASE
                        WHEN demand.is_password IS NULL THEN \'0\'
                        ELSE demand.is_password
                    END is_password
                FROM inherite
                INNER JOIN `:db`.on_demand_macro_service demand
                    ON demand.svc_macro_id = inherite.macro_id
                WHERE inherite.name IS NOT NULL
                GROUP BY inherite.name'
            );
        } else {
            $request = $this->translateDbName(
                'SELECT svc_macro_id AS id, svc_macro_name AS name, svc_macro_value AS `value`,
                    macro_order AS `order`, is_password, description, svc_svc_id AS service_id
                FROM `:db`.on_demand_macro_service
                WHERE svc_svc_id = :service_id'
            );
        }

        $statement = $this->db->prepare($request);
        $statement->bindValue(':service_id', $serviceId, \PDO::PARAM_INT);
        $statement->execute();

        $serviceMacros = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $serviceMacros[] = EntityCreator::createEntityByArray(
                ServiceMacro::class,
                $record
            );
        }
        return $serviceMacros;
    }

    /**
     * @inheritDoc
     */
    public function findCommandLine(int $serviceId): ?string
    {
        $request = $this->translateDbName(
            'WITH RECURSIVE inherite AS (
            SELECT service_id, service_template_model_stm_id, command_command_id
            FROM `:db`.service
            WHERE service_id = :service_id
            UNION
            SELECT service.command_command_id, service.service_template_model_stm_id, service.command_command_id
            FROM `:db`.service
            INNER JOIN inherite
                ON inherite.service_template_model_stm_id = service.service_id
                AND inherite.command_command_id IS NULL
            )
            SELECT command.command_line
            FROM inherite
            INNER JOIN `:db`.command
                ON command.command_id = inherite.command_command_id'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':service_id', $serviceId, \PDO::PARAM_INT);
        $statement->execute();

        if (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            return (string)$record['command_line'];
        }

        return null;
    }
}
