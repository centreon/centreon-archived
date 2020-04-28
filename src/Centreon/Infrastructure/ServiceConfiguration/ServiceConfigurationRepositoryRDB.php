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
use Centreon\Domain\Repository\RepositoryException;
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
        try {
            $request = $this->translateDbName(
                'SELECT service_id AS id, service_template_model_stm_id AS template_id, display_name AS name,
                service_description AS description, service_locked AS is_locked, service_register AS is_registred,
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
        } catch (\Throwable $ex) {
            throw new RepositoryException('Error while searching for the service', 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findOnDemandServiceMacros(int $serviceId): array
    {
        try {
            $request = $this->translateDbName('
                SELECT svc_macro_id AS id, svc_macro_name AS name, svc_macro_value AS `value`, macro_order AS `order`,
                is_password, description
                FROM `:db`.on_demand_macro_service
                WHERE svc_svc_id = :service_id
            ');
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
        } catch (\Throwable $ex) {
            throw new RepositoryException('Error while searching for the on-demand service macros', 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findCommandLine(int $serviceId): ?string
    {
        try {
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
                INNER JOIN centreon.command 
                    ON command.command_id = inherite.command_command_id'
            );
            $statement = $this->db->prepare($request);
            $statement->bindValue(':service_id', $serviceId, \PDO::PARAM_INT);
            $statement->execute();

            if (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                return (string)$record['command_line'];
            }
        } catch (\Throwable $ex) {
            throw new RepositoryException('Error while searching for the command of service', 0, $ex);
        }
        return null;
    }
}
