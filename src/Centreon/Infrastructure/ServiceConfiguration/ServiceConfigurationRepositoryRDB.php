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
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Domain\ServiceConfiguration\HostTemplateService;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationRepositoryInterface;
use Centreon\Domain\ServiceConfiguration\Service;
use Centreon\Domain\ServiceConfiguration\ServiceMacro;
use Centreon\Infrastructure\AccessControlList\AccessControlListRepositoryTrait;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * @todo Add ACL control
 *
 * @package Centreon\Infrastructure\ServiceConfiguration
 */
class ServiceConfigurationRepositoryRDB extends AbstractRepositoryDRB implements ServiceConfigurationRepositoryInterface
{
    use AccessControlListRepositoryTrait;

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
    public function addServicesToHost(Host $host, array $servicesToBeCreated): void
    {
        // We avoid to start again a database transaction
        $isAlreadyInTransaction = $this->db->inTransaction();
        if (!$isAlreadyInTransaction) {
            $this->db->beginTransaction();
        }
        $addServiceStatement = $this->db->prepare(
            $this->translateDbName(
                'INSERT INTO `:db`.service
                (service_template_model_stm_id, command_command_id, service_description, service_alias, service_locked, 
                service_activate, service_register)
                VALUES (:template_id, :command_id, :description, :alias, :is_locked, :is_activated, :service_type)'
            )
        );

        $addServiceExtensionStatement = $this->db->prepare(
            $this->translateDbName(
                'INSERT INTO `:db`.extended_service_information 
                (service_service_id, esi_notes, esi_notes_url, esi_action_url,
                esi_icon_image, esi_icon_image_alt, graph_id)
                VALUES (:service_id, :notes, :notes_url, :action_url, :icon_image, :icon_image_alt, :graph_id)'
            )
        );

        $addRelationStatement = $this->db->prepare(
            $this->translateDbName(
                'INSERT INTO `:db`.host_service_relation 
                (host_host_id, service_service_id)
                VALUES (:host_id, :service_id)'
            )
        );

        try {
            foreach ($servicesToBeCreated as $serviceTobeCreated) {
                /**
                 * Create service
                 */
                $addServiceStatement->bindValue(':template_id', $serviceTobeCreated->getTemplateId(), \PDO::PARAM_INT);
                $addServiceStatement->bindValue(':command_id', $serviceTobeCreated->getCommandId(), \PDO::PARAM_INT);
                $addServiceStatement->bindValue(':description', $serviceTobeCreated->getDescription(), \PDO::PARAM_STR);
                $addServiceStatement->bindValue(':alias', $serviceTobeCreated->getTemplateId(), \PDO::PARAM_STR);
                $addServiceStatement->bindValue(':is_locked', $serviceTobeCreated->isLocked(), \PDO::PARAM_BOOL);
                $addServiceStatement->bindValue(':is_activated', $serviceTobeCreated->isActivated(), \PDO::PARAM_STR);
                $addServiceStatement->bindValue(
                    ':service_type',
                    $serviceTobeCreated->getServiceType(),
                    \PDO::PARAM_STR
                );
                $addServiceStatement->execute();
                $serviceId = (int)$this->db->lastInsertId();

                /**
                 * Create service extension
                 */
                $eService = $serviceTobeCreated->getExtendedService();
                $addServiceExtensionStatement->bindValue(':service_id', $serviceId, \PDO::PARAM_INT);
                $addServiceExtensionStatement->bindValue(':notes', $eService->getNotes(), \PDO::PARAM_STR);
                $addServiceExtensionStatement->bindValue(':notes_url', $eService->getNotesUrl(), \PDO::PARAM_STR);
                $addServiceExtensionStatement->bindValue(':action_url', $eService->getActionUrl(), \PDO::PARAM_STR);
                $addServiceExtensionStatement->bindValue(':icon_image', $eService->getIconId(), \PDO::PARAM_INT);
                $addServiceExtensionStatement->bindValue(
                    ':icon_image_alt',
                    $eService->getIconAlternativeText(),
                    \PDO::PARAM_STR
                );
                $addServiceExtensionStatement->bindValue(':graph_id', $eService->getGraphId(), \PDO::PARAM_INT);
                $addServiceExtensionStatement->execute();

                /**
                 * Add relation between service and host
                 */
                $addRelationStatement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
                $addRelationStatement->bindValue(':service_id', $serviceId, \PDO::PARAM_INT);
                $addRelationStatement->execute();
            }
        } catch (\Throwable $ex) {
            if (!$isAlreadyInTransaction) {
                $this->db->rollBack();
            }
            throw $ex;
        }
        if (!$isAlreadyInTransaction) {
            $this->db->commit();
        }
    }

    /**
     * @inheritDoc
     */
    public function findService(int $serviceId): ?Service
    {
        $request = $this->translateDbName(
            'SELECT service_id AS id, service_template_model_stm_id AS template_id, display_name AS name,
            service_description AS description, service_locked AS is_locked, service_register AS service_type,
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
        /* CTE recurse request next release:
         *       WITH RECURSIVE inherite AS (
         *           SELECT srv.service_id, srv.service_template_model_stm_id AS template_id,
         *               demand.svc_macro_id AS macro_id, demand.svc_macro_name AS name, 0 AS level
         *           FROM `:db`.service srv
         *           LEFT JOIN `:db`.on_demand_macro_service demand
         *               ON srv.service_id = demand.svc_svc_id
         *           WHERE service_id = :service_id
         *           UNION
         *           SELECT srv.service_id, srv.service_template_model_stm_id AS template_id,
         *               demand.svc_macro_id AS macro_id, demand.svc_macro_name AS name, inherite.level + 1
         *           FROM `:db`.service srv
         *           INNER JOIN inherite
         *               ON inherite.template_id = srv.service_id
         *           LEFT JOIN `:db`.on_demand_macro_service demand
         *               ON srv.service_id = demand.svc_svc_id
         *       )
         *       SELECT demand.svc_macro_id AS id, demand.svc_macro_name AS name,
         *         demand.svc_macro_value AS `value`,
         *         demand.macro_order AS `order`, demand.description, demand.svc_svc_id AS service_id
         *           CASE
         *               WHEN demand.is_password IS NULL THEN \'0\'
         *               ELSE demand.is_password
         *          END is_password
         *       FROM inherite
         *       INNER JOIN `:db`.on_demand_macro_service demand
         *          ON demand.svc_macro_id = inherite.macro_id
         *       WHERE inherite.name IS NOT NULL
         */
        $request = $this->translateDbName(
            'SELECT
                srv.service_id AS service_id, demand.svc_macro_id AS id, 
                svc_macro_name AS name, svc_macro_value AS `value`,
                macro_order AS `order`, is_password, description, service_template_model_stm_id
             FROM `:db`.service srv
                LEFT JOIN `:db`.on_demand_macro_service demand ON srv.service_id = demand.svc_svc_id
             WHERE srv.service_id = :service_id'
        );
        $statement = $this->db->prepare($request);

        $serviceMacros = [];
        $loop = [];
        $macrosAdded = [];
        while (!is_null($serviceId)) {
            if (isset($loop[$serviceId])) {
                break;
            }
            $loop[$serviceId] = 1;
            $statement->bindValue(':service_id', $serviceId, \PDO::PARAM_INT);
            $statement->execute();
            while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $serviceId = $record['service_template_model_stm_id'];
                if (is_null($record['name']) || isset($macrosAdded[$record['name']])) {
                    continue;
                }
                $macrosAdded[$record['name']] = 1;
                $record['is_password'] = is_null($record['is_password']) ? 0 : $record['is_password'];
                $serviceMacros[] = EntityCreator::createEntityByArray(
                    ServiceMacro::class,
                    $record
                );
            }
            if (!$isUsingInheritance) {
                break;
            }
        }

        return $serviceMacros;
    }

    /**
     * @inheritDoc
     */
    public function findCommandLine(int $serviceId): ?string
    {
        /*
         * CTE recurse request next release:
         *   WITH RECURSIVE inherite AS (
         *     SELECT service_id, service_template_model_stm_id, command_command_id
         *     FROM `:db`.service
         *     WHERE service_id = :service_id
         *     UNION
         *     SELECT service.command_command_id, service.service_template_model_stm_id, service.command_command_id
         *     FROM `:db`.service
         *     INNER JOIN inherite
         *         ON inherite.service_template_model_stm_id = service.service_id
         *         AND inherite.command_command_id IS NULL
         *   )
         *   SELECT command.command_line
         *   FROM inherite
         *   INNER JOIN `:db`.command
         *       ON command.command_id = inherite.command_command_id
         */
        $request = $this->translateDbName(
            'SELECT
                srv.service_id AS service_id, service_template_model_stm_id,
                command.command_line
             FROM `:db`.service srv
                LEFT JOIN `:db`.command ON srv.command_command_id = command.command_id
             WHERE srv.service_id = :service_id LIMIT 1'
        );
        $statement = $this->db->prepare($request);

        $serviceMacros = [];
        $loop = [];
        while (!is_null($serviceId)) {
            if (isset($loop[$serviceId])) {
                break;
            }
            $loop[$serviceId] = 1;
            $statement->bindValue(':service_id', $serviceId, \PDO::PARAM_INT);
            $statement->execute();
            $record = $statement->fetch(\PDO::FETCH_ASSOC);
            if (!is_null($record['command_line'])) {
                return (string)$record['command_line'];
            }
            $serviceId = $record['service_template_model_stm_id'];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function findHostTemplateServices(array $hostTemplateIds): array
    {
        if (empty($hostTemplateIds)) {
            return [];
        }
        $request = $this->translateDbName(
            'SELECT host.host_id, host.host_name, host.host_alias, host.host_register AS host_type,
                host.host_activate AS host_is_activated,
                service.service_id, service.service_description, service.service_alias,
                service.service_register AS service_service_type, service.service_activate AS service_is_activated
            FROM `:db`.host_service_relation hsr
            INNER JOIN `:db`.host 
                ON host.host_id = hsr.host_host_id
                AND host.host_register = \'0\'
            INNER JOIN `:db`.service
                ON service.service_id = hsr.service_service_id
                AND service.service_register = \'0\'
            WHERE hsr.host_host_id IN (' . str_repeat('?,', count($hostTemplateIds) - 1) . '?)'
        );
        $statement = $this->db->prepare($request);
        $statement->execute($hostTemplateIds);

        $hostTemplateServices = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $hostTemplate = EntityCreator::createEntityByArray(
                Host::class,
                $record,
                'host_'
            );
            $serviceTemplate = EntityCreator::createEntityByArray(
                Service::class,
                $record,
                'service_'
            );
            $hostTemplateServices[] = (new HostTemplateService())
                ->setHostTemplate($hostTemplate)
                ->setServiceTemplate($serviceTemplate);
        }
        return $hostTemplateServices;
    }

    /**
     * @inheritDoc
     */
    public function findServicesByHost(Host $host): array
    {
        $request = $this->translateDbName(
            'SELECT service.service_id, service.service_description, service.service_alias,
                service.service_register AS service_service_type, service.service_activate AS service_activated,
                service.service_activate AS service_is_activated
            FROM `:db`.service
            INNER JOIN `:db`.host_service_relation hsr
                ON hsr.service_service_id = service.service_id
                AND service.service_register = \'1\'
            WHERE hsr.host_host_id = :host_id'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
        $statement->execute();

        $services = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $services[] = EntityCreator::createEntityByArray(
                Service::class,
                $record,
                'service_'
            );
        }
        return $services;
    }

    /**
     * @inheritDoc
     */
    public function removeServicesOnHost(int $hostId): void
    {
        $request = $this->translateDbName(
            "DELETE service FROM `:db`.service
            INNER JOIN `:db`.host_service_relation hsr
                ON hsr.service_service_id = service.service_id
            WHERE hsr.host_host_id = :host_id"
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $statement->execute();
    }
}
