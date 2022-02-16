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

namespace Centreon\Infrastructure\HostConfiguration\Repository;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\HostConfiguration\Exception\HostCategoryException;
use Centreon\Domain\HostConfiguration\Exception\HostGroupException;
use Centreon\Domain\HostConfiguration\Exception\HostSeverityException;
use Centreon\Domain\HostConfiguration\ExtendedHost;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\HostConfiguration\Repository\Model\HostTemplateFactoryRdb;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\RequestParametersTranslatorException;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * This class is designed to represent the MariaDb repository to manage host and host template
 *
 * @package Centreon\Infrastructure\HostConfiguration
 */
class HostConfigurationRepositoryRDB extends AbstractRepositoryDRB implements HostConfigurationRepositoryInterface
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
    public function addHost(Host $host): void
    {
        $request = $this->translateDbName(
            'INSERT INTO `:db`.host
            (host_name, host_alias, display_name, host_address, host_comment, geo_coords, host_activate,
            host_register, host_active_checks_enabled, host_passive_checks_enabled, host_checks_enabled,
            host_obsess_over_host, host_check_freshness, host_event_handler_enabled, host_flap_detection_enabled,
            host_process_perf_data, host_retain_status_information, host_retain_nonstatus_information,
            host_notifications_enabled)
            VALUES (:name, :alias, :display_name, :ip_address, :comment, :geo_coords, :is_activate, :host_register,
                    :active_check_status, :passive_check_status, :check_status, :obsess_over_status,
                    :freshness_check_status, :event_handler_status, :flap_detection_status, :process_perf_status,
                    :retain_status_information, :retain_nonstatus_information, :notifications_status)'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':name', $host->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':alias', $host->getAlias(), \PDO::PARAM_STR);
        $statement->bindValue(':display_name', $host->getDisplayName(), \PDO::PARAM_STR);
        $statement->bindValue(':ip_address', $host->getIpAddress(), \PDO::PARAM_STR);
        $statement->bindValue(':comment', $host->getComment(), \PDO::PARAM_STR);
        $statement->bindValue(':geo_coords', $host->getGeoCoords(), \PDO::PARAM_STR);
        $statement->bindValue(':is_activate', $host->isActivated(), \PDO::PARAM_STR);
        $statement->bindValue(':host_register', $host->getType(), \PDO::PARAM_STR);

        // We don't have these properties in the host object yet, so we set these default values
        $statement->bindValue(':active_check_status', '2', \PDO::PARAM_STR);
        $statement->bindValue(':passive_check_status', '2', \PDO::PARAM_STR);
        $statement->bindValue(':check_status', '2', \PDO::PARAM_STR);
        $statement->bindValue(':obsess_over_status', '2', \PDO::PARAM_STR);
        $statement->bindValue(':freshness_check_status', '2', \PDO::PARAM_STR);
        $statement->bindValue(':event_handler_status', '2', \PDO::PARAM_STR);
        $statement->bindValue(':flap_detection_status', '2', \PDO::PARAM_STR);
        $statement->bindValue(':process_perf_status', '2', \PDO::PARAM_STR);
        $statement->bindValue(':retain_status_information', '2', \PDO::PARAM_STR);
        $statement->bindValue(':retain_nonstatus_information', '2', \PDO::PARAM_STR);
        $statement->bindValue(':notifications_status', '2', \PDO::PARAM_STR);
        $statement->execute();

        $hostId = (int)$this->db->lastInsertId();
        $host->setId($hostId);

        if ($host->getMonitoringServer() !== null) {
            $this->linkMonitoringServerToHost($host, $host->getMonitoringServer());
        }
        if ($host->getExtendedHost() !== null) {
            $this->addExtendedHost($hostId, $host->getExtendedHost());
        }
        $this->linkHostTemplatesToHost($hostId, $host->getTemplates());
        $this->linkHostCategoriesToHost($host);
        $this->linkSeverityToHost($host);
        $this->linkHostGroupsToHost($host);
    }

    /**
     * Link a monitoring server to a host.
     *
     * @param Host $host Host for which this monitoring server host will be associated
     * @param MonitoringServer $monitoringServer Monitoring server to be added
     * @throws RepositoryException
     * @throws \Throwable
     */
    public function linkMonitoringServerToHost(Host $host, MonitoringServer $monitoringServer): void
    {
        Assertion::notNull($host->getId());
        if ($monitoringServer->getId() !== null) {
            $request = $this->translateDbName(
                'INSERT INTO `:db`.ns_host_relation
                (nagios_server_id, host_host_id)
                VALUES (:monitoring_server_id, :host_id)'
            );
            $statement = $this->db->prepare($request);
            $statement->bindValue(':monitoring_server_id', $monitoringServer->getId(), \PDO::PARAM_INT);
            $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
            $statement->execute();
            if ($statement->rowCount() === 0) {
                throw new RepositoryException(
                    sprintf(_('Monitoring server with id %d not found'), $monitoringServer->getId())
                );
            }
        } elseif (!empty($monitoringServer->getName())) {
            $request = $this->translateDbName(
                'INSERT INTO `:db`.ns_host_relation
                (nagios_server_id, host_host_id)
                SELECT nagios_server.id, :host_id
                FROM `:db`.nagios_server
                WHERE nagios_server.name = :monitoring_server_name'
            );
            $statement = $this->db->prepare($request);
            $statement->bindValue(':monitoring_server_name', $monitoringServer->getName(), \PDO::PARAM_STR);
            $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
            $statement->execute();
            if ($statement->rowCount() === 0) {
                throw new RepositoryException(
                    sprintf(_('Monitoring server %s not found'), $monitoringServer->getName())
                );
            }
        }
    }

    /**
     * Add extended host information.
     *
     * @param int $hostId Host id for which this extended host will be associated
     * @param ExtendedHost $extendedHost Extended host to be added
     * @throws \Exception
     */
    private function addExtendedHost(int $hostId, ExtendedHost $extendedHost): void
    {
        $request = $this->translateDbName(
            'INSERT INTO `:db`.extended_host_information
            (host_host_id, ehi_notes, ehi_notes_url, ehi_action_url)
            VALUES (:host_id, :notes, :url, :action_url)'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $statement->bindValue(':notes', $extendedHost->getNotes(), \PDO::PARAM_STR);
        $statement->bindValue(':url', $extendedHost->getNotesUrl(), \PDO::PARAM_STR);
        $statement->bindValue(':action_url', $extendedHost->getActionUrl(), \PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * Link a host to a given list of host templates.
     *
     * @param int $hostId Host id for which this templates will be associated
     * @param Host[] $hostTemplates Host template to be added
     * @throws RepositoryException
     * @throws \Exception
     */
    private function linkHostTemplatesToHost(int $hostId, array $hostTemplates): void
    {
        if (empty($hostTemplates)) {
            return;
        }

        foreach ($hostTemplates as $order => $template) {
            if ($template->getId() !== null) {
                // Associate the host and host template using template id
                $this->addHostTemplateToHostById($hostId, $template->getId(), ((int) $order) + 1);
            } elseif (!empty($template->getName())) {
                // Associate the host and host template using template name
                $this->addHostTemplateToHostByName($hostId, $template->getName(), ((int) $order) + 1);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function findHost(int $hostId): ?Host
    {
        $request = $this->translateDbName(
            'SELECT host.host_id, host.host_name, host.host_alias, host.display_name AS host_display_name,
            host.host_address AS host_ip_address, host.host_comment, host.geo_coords AS host_geo_coords,
            host.host_activate AS host_is_activated, nagios.id AS monitoring_server_id,
            nagios.name AS monitoring_server_name, ext.*
            FROM `:db`.host host
            LEFT JOIN `:db`.extended_host_information ext
                ON ext.host_host_id = host.host_id
            INNER JOIN `:db`.ns_host_relation host_server
                ON host_server.host_host_id = host.host_id
            INNER JOIN `:db`.nagios_server nagios
                ON nagios.id = host_server.nagios_server_id
            WHERE host.host_id = :host_id
            AND host.host_register = \'1\''
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $statement->execute();

        if (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            /**
             * @var Host $host
             */
            $host = EntityCreator::createEntityByArray(Host::class, $record, 'host_');
            /**
             * @var ExtendedHost $extendedHost
             */
            $extendedHost = EntityCreator::createEntityByArray(ExtendedHost::class, $record, 'ehi_');
            $host->setExtendedHost($extendedHost);
            /**
             * @var MonitoringServer $monitoringServer
             */
            $monitoringServer = EntityCreator::createEntityByArray(
                MonitoringServer::class,
                $record,
                'monitoring_server_'
            );
            $host->setMonitoringServer($monitoringServer);

            return $host;
        }
        return null;
    }


    /**
     * @inheritDoc
     */
    public function findHostTemplatesRecursively(Host $host): array
    {
        /*
         * CTE recurse request next release:
         *   WITH RECURSIVE template AS (
         *       SELECT htr.*, 0 AS level
         *       FROM `:db`.host_template_relation htr
         *       WHERE htr.host_host_id  = :host_id
         *       UNION
         *       SELECT htr2.*, template.level + 1
         *       FROM `:db`.host_template_relation htr2
         *       INNER JOIN template
         *           ON template.host_tpl_id = htr2.host_host_id
         *       INNER JOIN `:db`.host
         *           ON host.host_id = htr2.host_tpl_id
         *           AND host.host_register = 1
         *   )
         *   SELECT host_host_id AS template_host_id, host_tpl_id AS template_id, `order` AS template_order,
         *       `level` AS template_level, host.host_id AS id, host.host_name AS name, host.host_alias AS alias,
         *       host.host_register AS type, host.host_activate AS is_activated
         *   FROM template
         *   INNER JOIN `:db`.host
         *       ON host.host_id = template.host_tpl_id
         *   ORDER BY `level`, host_host_id, `order`
         */
        $request = $this->translateDbName(
            'SELECT
                host.host_id AS id,
                htr.`order` AS template_order,
                host.host_name AS name,
                host.host_alias AS alias,
                host.host_register AS type,
                host.host_activate AS is_activated
             FROM `:db`.host_template_relation htr, `:db`.host
             WHERE
                htr.host_host_id = :host_id AND
                htr.host_tpl_id = host.host_id AND
                host.host_register = 1
             ORDER BY htr.`order` ASC'
        );
        $statement = $this->db->prepare($request);

        $hostTemplates = [];
        $stack = [[$host->getId(), 0, null, []]];
        while (($hostTest = array_shift($stack))) {
            if (isset($hostTest[3][$hostTest[0]])) {
                continue;
            }
            $hostTest[3][$hostTest[0]] = 1;

            $statement = $this->db->prepare($request);
            $statement->bindValue(':host_id', $hostTest[0], \PDO::PARAM_INT);
            $statement->execute();

            $hostTpl = [];
            $currentLevel = $hostTest[1];
            while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $record['template_host_id'] = $hostTest[0];
                $record['template_level'] = $currentLevel;
                $hostTemplate = EntityCreator::createEntityByArray(
                    Host::class,
                    $record
                );
                if ($currentLevel === 0) {
                    $hostTemplates[] = $hostTemplate;
                } else {
                    $hostTest[2]->addTemplate($hostTemplate);
                }
                $hostTpl[] = [$record['id'], $currentLevel + 1, $hostTemplate, $hostTest[3]];
            }

            $stack = array_merge($hostTpl, $stack);
        }

        return $hostTemplates;
    }

    /**
     * @inheritDoc
     */
    public function getNumberOfHosts(): int
    {
        $request = $this->translateDbName('SELECT COUNT(*) AS total FROM `:db`.host WHERE host_register = \'1\'');
        $statement = $this->db->query($request);

        if ($statement !== false && ($result = $statement->fetchColumn()) !== false) {
            return (int) $result;
        }
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function hasHostWithSameName(string $hostName): bool
    {
        $request = $this->translateDbName('SELECT COUNT(*) FROM `:db`.host WHERE host_name = :host_name');
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_name', $hostName, \PDO::FETCH_ASSOC);
        $statement->execute();
        if (($result = $statement->fetchColumn()) !== false) {
            return ((int) $result) > 0;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function findCommandLine(int $hostId): ?string
    {
        /*
         * CTE recurse request next release:
         *    WITH RECURSIVE inherite AS (
         *       SELECT relation.host_host_id, relation.host_tpl_id, relation.order, host.command_command_id,
         *       0 AS level
         *       FROM `:db`.host
         *       LEFT JOIN `:db`.host_template_relation relation
         *           ON relation.host_host_id  = host.host_id
         *       WHERE host.host_id = :host_id
         *       UNION ALL
         *       SELECT relation.host_host_id, relation.host_tpl_id, relation.order, host.command_command_id,
         *       inherite.level + 1
         *       FROM `:db`.host
         *       INNER JOIN inherite
         *           ON inherite.host_tpl_id = host.host_id
         *       LEFT JOIN `:db`.host_template_relation relation
         *           ON relation.host_host_id  = host.host_id
         *    )
         *    SELECT command.command_line
         *    FROM inherite
         *      INNER JOIN `:db`.command
         *       ON command.command_id = inherite.command_command_id
         */
        $request = $this->translateDbName(
            'SELECT
                command.command_line, relation.templates
             FROM `:db`.host
                LEFT JOIN `:db`.command ON command.command_id = host.command_command_id,
                (SELECT GROUP_CONCAT(host_tpl_id) as templates
                 FROM `:db`.host_template_relation htr
                 WHERE htr.host_host_id = :host_id ORDER BY `order` ASC) as relation
             WHERE host.host_id = :host_id LIMIT 1'
        );
        $statement = $this->db->prepare($request);

        $loop = [];
        $stack = [$hostId];
        while (($hostTest = array_shift($stack))) {
            if (isset($loop[$hostTest])) {
                continue;
            }
            $loop[$hostTest] = 1;

            $statement = $this->db->prepare($request);
            $statement->bindValue(':host_id', $hostTest, \PDO::PARAM_INT);
            $statement->execute();

            $hostTpl = null;
            $record = $statement->fetch(\PDO::FETCH_ASSOC);
            if (!is_null($record['templates']) && is_null($hostTpl)) {
                $hostTpl = explode(',', $record['templates']);
            }
            if (!is_null($record['command_line'])) {
                return (string)$record['command_line'];
            }

            if (!is_null($hostTpl)) {
                $stack = array_merge($hostTpl, $stack);
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function findOnDemandHostMacros(int $hostId, bool $isUsingInheritance = false): array
    {
        /*
         * CTE recurse request next release:
         *   WITH RECURSIVE inherite AS (
         *           SELECT relation.host_host_id, relation.host_tpl_id, relation.order, demand.host_macro_id,
         *               demand.host_macro_name, 0 AS level
         *           FROM `:db`.host
         *           LEFT JOIN `:db`.host_template_relation relation
         *              ON relation.host_host_id  = host.host_id
         *           LEFT JOIN on_demand_macro_host demand
         *               ON demand.host_host_id = host.host_id
         *           WHERE host.host_id = :host_id
         *           UNION ALL
         *           SELECT relation.host_host_id, relation.host_tpl_id, relation.order, demand.host_macro_id,
         *               demand.host_macro_name, inherite.level + 1
         *           FROM `:db`.host
         *           INNER JOIN inherite
         *               ON inherite.host_tpl_id = host.host_id
         *           LEFT JOIN `:db`.host_template_relation relation
         *               ON relation.host_host_id  = host.host_id
         *           LEFT JOIN on_demand_macro_host demand
         *               ON demand.host_host_id = host.host_id
         *       )
         *       SELECT macro.host_macro_id AS id, macro.host_macro_name AS name,
         *           macro.host_macro_value AS `value`, macro.macro_order AS `order`, macro.host_host_id AS host_id,
         *           CASE
         *               WHEN is_password IS NULL THEN \'0\'
         *               ELSE is_password
         *           END is_password, description
         *       FROM (
         *           SELECT * FROM inherite
         *           WHERE host_macro_id IS NOT NULL
         *           GROUP BY inherite.level, inherite.order, host_macro_name
         *       ) AS tpl
         *       INNER JOIN `:db`.on_demand_macro_host macro
         *           ON macro.host_macro_id = tpl.host_macro_id
         *       GROUP BY tpl.host_macro_name
         */
        $request = $this->translateDbName(
            'SELECT
                host.host_id, macro.host_macro_id AS id, macro.host_macro_name AS name,
                macro.host_macro_value AS `value`, macro.macro_order AS `order`,
                macro.is_password, macro.description, relation.templates
             FROM `:db`.host
                LEFT JOIN `:db`.on_demand_macro_host macro ON macro.host_host_id = host.host_id,
                (SELECT GROUP_CONCAT(host_tpl_id) as templates
                 FROM `:db`.host_template_relation htr
                 WHERE htr.host_host_id = :host_id ORDER BY `order` ASC) as relation
             WHERE host.host_id = :host_id'
        );
        $statement = $this->db->prepare($request);

        $hostMacros = [];
        $macrosAdded = [];
        $loop = [];
        $stack = [$hostId];
        while (($hostTest = array_shift($stack))) {
            if (isset($loop[$hostTest])) {
                continue;
            }
            $loop[$hostTest] = 1;

            $statement = $this->db->prepare($request);
            $statement->bindValue(':host_id', $hostTest, \PDO::PARAM_INT);
            $statement->execute();
            $hostTpl = null;
            while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                if (!is_null($record['templates']) && is_null($hostTpl)) {
                    $hostTpl = explode(',', $record['templates']);
                }
                if (is_null($record['name']) || isset($macrosAdded[$record['name']])) {
                    continue;
                }
                $macrosAdded[$record['name']] = 1;
                $record['is_password'] = is_null($record['is_password']) ? 0 : $record['is_password'];
                $hostMacros[] = EntityCreator::createEntityByArray(
                    HostMacro::class,
                    $record
                );
            }
            if (!$isUsingInheritance) {
                break;
            }

            if (!is_null($hostTpl)) {
                $stack = array_merge($hostTpl, $stack);
            }
        }

        return $hostMacros;
    }

    /**
     * @inheritDoc
     */
    public function changeActivationStatus(int $hostId, bool $shouldBeActivated): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName('UPDATE `:db`.host SET host_activate = :activation_status WHERE host_id = :host_id')
        );
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $statement->bindValue(':activation_status', $shouldBeActivated ? '1' : '0', \PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * @inheritDoc
     */
    public function findHostNamesAlreadyUsed(array $namesToCheck): array
    {
        if (empty($namesToCheck)) {
            return [];
        }

        $names = [];
        foreach ($namesToCheck as $name) {
            $names[] = (string) $name;
        }

        $statement = $this->db->prepare(
            $this->translateDbName(
                sprintf(
                    'SELECT host_name FROM `:db`.host WHERE host_name IN (%s?)',
                    str_repeat('?,', count($names) - 1)
                )
            )
        );
        $statement->execute($names);
        $namesFound = [];
        while (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $namesFound[] = $result['host_name'];
        }
        return $namesFound;
    }

    /**
     * @inheritDoc
     */
    public function findHostTemplates(): array
    {
        try {
            $this->sqlRequestTranslator->setConcordanceArray(
                [
                    'id' => 'h.host_id',
                    'name' => 'h.host_name',
                    'alias' => 'h.host_alias'
                ]
            );
            $request = $this->translateDbName(
                'SELECT SQL_CALC_FOUND_ROWS h.*, ext.*, icon.img_id AS icon_id, icon.img_name AS icon_name,
                    CONCAT(iconD.dir_name,\'/\',icon.img_path) AS icon_path,
                    icon.img_comment AS icon_comment, smi.img_id AS smi_id, smi.img_name AS smi_name,
                    smi.img_path AS smi_path, smi.img_comment AS smi_comment,
                    GROUP_CONCAT(DISTINCT htr.host_tpl_id) AS parents
                FROM `:db`.host h
                LEFT JOIN `:db`.extended_host_information ext
                    ON h.host_id = ext.host_host_id
                LEFT JOIN `:db`.view_img icon
                    ON icon.img_id = ext.ehi_icon_image
                LEFT JOIN `:db`.view_img_dir_relation iconR
                    ON iconR.img_img_id = icon.img_id
                LEFT JOIN `:db`.view_img_dir iconD
                    ON iconD.dir_id = iconR.dir_dir_parent_id
                LEFT JOIN `:db`.view_img smi
                    ON smi.img_id = ext.ehi_statusmap_image
                LEFT JOIN `:db`.host_template_relation htr
                    ON htr.host_host_id = h.host_id
                LEFT JOIN `:db`.options AS opt
                    ON opt.key = \'nagios_path_img\''
            );
            // Search
            $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
            $request .= !is_null($searchRequest)
                ? $searchRequest . ' AND h.host_register = \'0\' GROUP BY h.host_id'
                : ' WHERE h.host_register = \'0\' GROUP BY h.host_id';

            // Sort
            $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
            $request .= !is_null($sortRequest)
                ? $sortRequest
                : ' ORDER BY h.host_id ASC';

            // Pagination
            $request .= $this->sqlRequestTranslator->translatePaginationToSql();
            $statement = $this->db->prepare($request);
            foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
                $type = key($data);
                $value = $data[$type];
                $statement->bindValue($key, $value, $type);
            }
            $statement->execute();

            $result = $this->db->query('SELECT FOUND_ROWS()');
            if ($result !== false && ($total = $result->fetchColumn()) !== false) {
                $this->sqlRequestTranslator->getRequestParameters()->setTotal((int)$total);
            }

            $hostTemplates = [];
            if ($statement !== false) {
                while (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                    $hostTemplates[] = HostTemplateFactoryRdb::create($result);
                }
            }
            return $hostTemplates;
        } catch (RequestParametersTranslatorException $ex) {
            throw new RepositoryException($ex->getMessage(), $ex->getCode(), $ex);
        } catch (\Throwable $ex) {
            throw $ex;
        }
    }

    /**
     * Link the categories to host only if their id is not null.
     *
     * @param Host $host
     * @throws HostCategoryException
     */
    private function linkHostCategoriesToHost(Host $host): void
    {
        foreach ($host->getCategories() as $category) {
            try {
                if ($category->getId() === null) {
                    continue;
                }
                $statement = $this->db->prepare(
                    $this->translateDbName('
                    INSERT INTO `:db`.hostcategories_relation (host_host_id, hostcategories_hc_id)
                    VALUES (:host_id, :category_id)
                ')
                );
                $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
                $statement->bindValue(':category_id', $category->getId(), \PDO::PARAM_INT);
                $statement->execute();
            } catch (\Throwable $ex) {
                throw HostCategoryException::notFoundException(['id' => $category->getId()], $ex);
            }
        }
    }

    /**
     * Link the groups to host only if their id is not null.
     *
     * @param Host $host
     * @throws HostGroupException
     */
    private function linkHostGroupsToHost(Host $host): void
    {
        foreach ($host->getGroups() as $group) {
            try {
                if ($group->getId() === null) {
                    continue;
                }
                $statement = $this->db->prepare(
                    $this->translateDbName(
                        'INSERT INTO `:db`.hostgroup_relation (host_host_id, hostgroup_hg_id)
                        VALUES (:host_id, :group_id)'
                    )
                );
                $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
                $statement->bindValue(':group_id', $group->getId(), \PDO::PARAM_INT);
                $statement->execute();
            } catch (\Throwable $ex) {
                throw HostGroupException::notFoundException(['id' => $group->getId()], $ex);
            }
        }
    }

    /**
     * Link the severity to host only if id is not null.
     *
     * @param Host $host
     * @throws HostSeverityException
     */
    private function linkSeverityToHost(Host $host): void
    {
        if ($host->getSeverity()?->getId() === null) {
            return;
        }

        try {
            $statement = $this->db->prepare(
                $this->translateDbName(
                    'INSERT INTO `:db`.hostcategories_relation (host_host_id, hostcategories_hc_id)
                    VALUES (:host_id, :severity_id)'
                )
            );
            $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
            $statement->bindValue(':severity_id', $host->getSeverity()->getId(), \PDO::PARAM_INT);
            $statement->execute();
        } catch (\Throwable $ex) {
            throw HostSeverityException::notFoundException(['id' => $host->getSeverity()->getId()], $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function updateHost(Host $host): void
    {
        $isAlreadyInTransaction = $this->db->inTransaction();
        if (!$isAlreadyInTransaction) {
            $this->db->beginTransaction();
        }
        try {
            $request = $this->translateDbName(
                'UPDATE `:db`.host SET
                    host_name = :name, host_alias = :alias, host_address = :ip_address, host_comment = :comment,
                    geo_coords = :geo_coords, host_activate = :is_activate, host_register = :host_register,
                    host_active_checks_enabled = :active_check_status,
                    host_passive_checks_enabled = :passive_check_status, host_checks_enabled = :check_status,
                    host_obsess_over_host = :obsess_over_status, host_check_freshness = :freshness_check_status,
                    host_event_handler_enabled = :event_handler_status,
                    host_flap_detection_enabled = :flap_detection_status, host_process_perf_data = :process_perf_status,
                    host_retain_status_information = :retain_status_information,
                    host_retain_nonstatus_information = :retain_nonstatus_information,
                    host_notifications_enabled = :notifications_status
                    WHERE host_id = :id'
            );

            $statement = $this->db->prepare($request);
            $statement->bindValue(':id', $host->getId(), \PDO::PARAM_INT);
            $statement->bindValue(':name', $host->getName(), \PDO::PARAM_STR);
            $statement->bindValue(':alias', $host->getAlias(), \PDO::PARAM_STR);
            $statement->bindValue(':ip_address', $host->getIpAddress(), \PDO::PARAM_STR);
            $statement->bindValue(':comment', $host->getComment(), \PDO::PARAM_STR);
            $statement->bindValue(':geo_coords', $host->getGeoCoords(), \PDO::PARAM_STR);
            $statement->bindValue(':is_activate', $host->isActivated(), \PDO::PARAM_STR);
            $statement->bindValue(':host_register', '1', \PDO::PARAM_STR);
            $statement->bindValue(':active_check_status', Host::OPTION_DEFAULT, \PDO::PARAM_STR);
            $statement->bindValue(':passive_check_status', Host::OPTION_DEFAULT, \PDO::PARAM_STR);
            $statement->bindValue(':check_status', Host::OPTION_DEFAULT, \PDO::PARAM_STR);
            $statement->bindValue(':obsess_over_status', Host::OPTION_DEFAULT, \PDO::PARAM_STR);
            $statement->bindValue(':freshness_check_status', Host::OPTION_DEFAULT, \PDO::PARAM_STR);
            $statement->bindValue(':event_handler_status', Host::OPTION_DEFAULT, \PDO::PARAM_STR);
            $statement->bindValue(':flap_detection_status', Host::OPTION_DEFAULT, \PDO::PARAM_STR);
            $statement->bindValue(':process_perf_status', Host::OPTION_DEFAULT, \PDO::PARAM_STR);
            $statement->bindValue(':retain_status_information', Host::OPTION_DEFAULT, \PDO::PARAM_STR);
            $statement->bindValue(':retain_nonstatus_information', Host::OPTION_DEFAULT, \PDO::PARAM_STR);
            $statement->bindValue(':notifications_status', Host::OPTION_DEFAULT, \PDO::PARAM_STR);
            $statement->execute();
            $this->updateMonitoringServerRelation($host->getId(), $host->getMonitoringServer()->getId());

            // Update links between host groups and hosts
            $this->removeHostGroupsToHost($host);
            $this->linkHostGroupsToHost($host);

            // Update host templates
            $this->removeHostTemplatesFromHost($host);
            $this->linkHostTemplatesToHost($host->getId(), $host->getTemplates());

            // Update Host Categories
            $this->removeHostCategoriesFromHost($host);
            $this->linkHostCategoriesToHost($host);

            $this->updateHostSeverity($host);

            if (!$isAlreadyInTransaction) {
                $this->db->commit();
            }
        } catch (\Throwable $ex) {
            if (!$isAlreadyInTransaction) {
                $this->db->rollBack();
            }
            throw $ex;
        }
    }

    /**
     * Removes all links between host groups and hosts.
     *
     * @param Host $host
     */
    private function removeHostGroupsToHost(Host $host): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName('DELETE FROM `:db`.hostgroup_relation WHERE host_host_id = :host_id')
        );
        $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Add or update the link between a host and a monitoring server to which it is linked.
     *
     * @param int $hostId
     * @param int $monitoringServerId
     */
    private function updateMonitoringServerRelation(int $hostId, int $monitoringServerId): void
    {
        $request = $this->translateDbName(
            "DELETE FROM `:db`.ns_host_relation
            WHERE nagios_server_id = :monitoring_server_id AND host_host_id = :host_id"
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':monitoring_server_id', $monitoringServerId, \PDO::PARAM_INT);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $statement->execute();

        $request = $this->translateDbName(
            "INSERT INTO `:db`.ns_host_relation
            (nagios_server_id, host_host_id) VALUES (:monitoring_server_id, :host_id)"
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':monitoring_server_id', $monitoringServerId, \PDO::PARAM_INT);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Link a template to a host by template id
     *
     * @param integer $hostId
     * @param integer $templateId
     * @param integer $order
     * @return void
     * @throws \Exception
     */
    private function addHostTemplateToHostById(int $hostId, int $templateId, int $order): void
    {
        $request = $this->translateDbName(
            'INSERT INTO `:db`.host_template_relation
            (`host_host_id`, `host_tpl_id`, `order`)
            VALUES (:host_id, :template_id, :order)'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $statement->bindValue(':template_id', $templateId, \PDO::PARAM_INT);
        $statement->bindValue(':order', $order, \PDO::PARAM_INT);
        $statement->execute();
        if ($statement->rowCount() === 0) {
            throw new RepositoryException(
                sprintf(_('Error while linking template (id: %d) to host (id: %d)'), $templateId, $hostId)
            );
        }
    }

    /**
     * link a template to a host by template name
     *
     * @param integer $hostId
     * @param string $templateName
     * @param integer $order
     * @return void
     * @throws \Exception
     */
    private function addHostTemplateToHostByName(int $hostId, string $templateName, int $order): void
    {
        $request = $this->translateDbName(
            'INSERT INTO `:db`.host_template_relation
            (`host_host_id`, `host_tpl_id`, `order`)
            SELECT :host_id, host.host_id, :order
            FROM `:db`.host
            WHERE host.host_name = :template_name'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $statement->bindValue(':template_name', $templateName, \PDO::PARAM_STR);
        $statement->bindValue(':order', $order, \PDO::PARAM_INT);
        $statement->execute();
        if ($statement->rowCount() === 0) {
            throw new RepositoryException(
                sprintf(_('Error while linking template %s to host (id: %d)'), $templateName, $hostId)
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function findHostTemplatesByHost(Host $host): array
    {
        $request = $this->translateDbName(
            'SELECT
                host.host_id AS id,
                htr.`order` AS template_order,
                host.host_name AS name,
                host.host_alias AS alias,
                host.host_register AS type,
                host.host_activate AS is_activated
            FROM `:db`.host_template_relation htr, `:db`.host
            WHERE
                htr.host_host_id = :host_id AND
                htr.host_tpl_id = host.host_id AND
                host.host_register = 1
            ORDER BY htr.`order` ASC'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
        $statement->execute();

        $hostTemplates = [];
        if ($statement !== false) {
            while (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $hostTemplates[] = EntityCreator::createEntityByArray(
                    Host::class,
                    $result
                );
            }
        }
        return $hostTemplates;
    }

    /**
     * Removes all links between a given host and its host templates
     *
     * @param Host $host
     */
    private function removeHostTemplatesFromHost(Host $host): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName('DELETE FROM `:db`.host_template_relation WHERE host_host_id = :host_id')
        );
        $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Removes all links between host categories and hosts.
     *
     * @param Host $host
     */
    private function removeHostCategoriesFromHost(Host $host): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName(
                'DELETE `:db`.hostcategories_relation
                FROM `:db`.hostcategories_relation
                INNER JOIN `:db`.hostcategories ON hostcategories.hc_id = hostcategories_relation.hostcategories_hc_id
                WHERE hostcategories_relation.host_host_id = :host_id
                AND hostcategories.level IS NULL'
            )
        );
        $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Add or update the link between a host and a host severity
     *
     * @param Host $host
     */
    private function updateHostSeverity(Host $host): void
    {
        if ($host->getSeverity()?->getId() === null) {
            return;
        }

        $request = $this->translateDbName(
            "DELETE `:db`.hostcategories_relation
            FROM `:db`.hostcategories_relation
            JOIN `:db`.hostcategories
            ON hostcategories.hc_id = hostcategories_relation.hostcategories_hc_id
            WHERE hostcategories.level IS NOT NULL
            AND hostcategories_relation.host_host_id = :host_id"
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
        $statement->execute();

        $this->linkSeverityToHost($host);
    }
}
