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
use Centreon\Domain\HostConfiguration\ExtendedHost;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Centreon\Domain\HostConfiguration\Model\HostCategory;
use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\HostConfiguration\Repository\Model\HostTemplateFactoryRdb;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
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
    public function addHost(Host $host): int
    {
        try {
            $this->db->beginTransaction();
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
            if ($host->getMonitoringServer() !== null) {
                $this->addMonitoringServer($hostId, $host->getMonitoringServer());
            }
            if ($host->getExtendedHost() !== null) {
                $this->addExtendedHost($hostId, $host->getExtendedHost());
            }
            $this->linkToTemplate($hostId, $host->getTemplates());
            $this->addHostMacro($hostId, $host->getMacros());

            $this->db->commit();

            return $hostId;
        } catch (\Exception $ex) {
            $this->db->rollBack();
            throw $ex;
        }
    }

    /**
     * Add a monitoring server.
     *
     * @param int $hostId Host id for which this monitoring server host will be associated
     * @param MonitoringServer $monitoringServer Monitoring server to be added
     * @throws RepositoryException
     * @throws \Exception
     */
    private function addMonitoringServer(int $hostId, MonitoringServer $monitoringServer): void
    {
        if ($monitoringServer->getId() !== null) {
            $request = $this->translateDbName(
                'INSERT INTO `:db`.ns_host_relation 
                (nagios_server_id, host_host_id)
                VALUES (:monitoring_server_id, :host_id)'
            );
            $statement = $this->db->prepare($request);
            $statement->bindValue(':monitoring_server_id', $monitoringServer->getId(), \PDO::PARAM_INT);
            $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
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
            $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
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
     * Add a host template.
     *
     * @param int $hostId Host id for which this templates will be associated
     * @param Host[] $hostTemplates Host template to be added
     * @throws RepositoryException
     * @throws \Exception
     */
    private function linkToTemplate(int $hostId, array $hostTemplates): void
    {
        if (empty($hostTemplates)) {
            return;
        }

        foreach ($hostTemplates as $order => $template) {
            if ($template->getId() !== null) {
                // Associate the host and host template using template id
                $request = $this->translateDbName(
                    'INSERT INTO `:db`.host_template_relation
                    (`host_host_id`, `host_tpl_id`, `order`)
                    VALUES (:host_id, :template_id, :order)'
                );
                $statement = $this->db->prepare($request);
                $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
                $statement->bindValue(':template_id', $template->getId(), \PDO::PARAM_INT);
                $statement->bindValue(':order', ((int) $order) + 1, \PDO::PARAM_INT);
                $statement->execute();
                if ($statement->rowCount() === 0) {
                    throw new RepositoryException(sprintf(_('Template with id %d not found'), $template->getId()));
                }
            } elseif (!empty($template->getName())) {
                // Associate the host and host template using template name
                $request = $this->translateDbName(
                    'INSERT INTO `:db`.host_template_relation
                    (`host_host_id`, `host_tpl_id`, `order`)
                    SELECT :host_id, host.host_id, :order
                    FROM `:db`.host
                    WHERE host.host_name = :template_name'
                );
                $statement = $this->db->prepare($request);
                $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
                $statement->bindValue(':template_name', $template->getName(), \PDO::PARAM_STR);
                $statement->bindValue(':order', ((int) $order), \PDO::PARAM_INT);
                $statement->execute();
                if ($statement->rowCount() === 0) {
                    throw new RepositoryException(sprintf(_('Template %s not found'), $template->getName()));
                }
            }
        }
    }

    /**
     * Add host macros.
     *
     * @param int $hostId Host id for which this macros will be associated
     * @param HostMacro[] $hostMacros Macros to be added
     * @throws \Exception
     */
    private function addHostMacro(int $hostId, array $hostMacros): void
    {
        if (empty($hostMacros)) {
            return;
        }
        $request = $this->translateDbName(
            'INSERT INTO `:db`.on_demand_macro_host
            (host_host_id, host_macro_name, host_macro_value, is_password, description, macro_order)
            VALUES (:host_id, :name, :value, :is_password, :description, :order)'
        );
        $statement = $this->db->prepare($request);

        foreach ($hostMacros as $order => $macro) {
            $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
            $statement->bindValue(':name', $macro->getName(), \PDO::PARAM_STR);
            $statement->bindValue(':value', $macro->getValue(), \PDO::PARAM_STR);
            $statement->bindValue(':is_password', $macro->isPassword(), \PDO::PARAM_INT);
            $statement->bindValue(':description', $macro->getDescription(), \PDO::PARAM_STR);
            $statement->bindValue(':order', ((int) $order), \PDO::PARAM_INT);
            $statement->execute();
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
        $request = $this->translateDbName(
            'WITH RECURSIVE template AS (
                SELECT htr.*, 0 AS level
                FROM `:db`.host_template_relation htr 
                WHERE htr.host_host_id  = :host_id
                UNION
                SELECT htr2.*, template.level + 1
                FROM `:db`.host_template_relation htr2
                INNER JOIN template 
                    ON template.host_tpl_id = htr2.host_host_id
                INNER JOIN `:db`.host 
                    ON host.host_id = htr2.host_tpl_id 
                    AND host.host_register = 1
            )
            SELECT host_host_id AS template_host_id, host_tpl_id AS template_id, `order` AS template_order, 
                `level` AS template_level, host.host_id AS id, host.host_name AS name, host.host_alias AS alias,
                host.host_register AS type, host.host_activate AS is_activated
            FROM template
            INNER JOIN `:db`.host 
                ON host.host_id = template.host_tpl_id
            ORDER BY `level`, host_host_id, `order`'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
        $statement->execute();

        /**
         * Add a host template by browsing all templates recursively until we find the parent template.
         *
         * @param int $hostParentId Id of the host template for which we want to add the given template.
         * @param Host $hostTemplate Host template to be added
         * @param Host[] $templates Host templates where we will try to find the parent
         *                          of the host template to be added
         */
        $addTemplateToHost =
            function (int $hostParentId, Host $hostTemplate, array $templates) use (&$addTemplateToHost) {
                foreach ($templates as $template) {
                    if ($template->getId() === $hostParentId) {
                        $template->addTemplate($hostTemplate);
                    } else {
                        $addTemplateToHost($hostParentId, $hostTemplate, $template->getTemplates());
                    }
                }
            };

        $hostTemplates = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $hostTemplate = EntityCreator::createEntityByArray(
                Host::class,
                $record
            );
            if ((int) $record['template_host_id'] === $host->getId()) {
                $hostTemplates[] = $hostTemplate;
            } else {
                $addTemplateToHost((int) $record['template_host_id'], $hostTemplate, $hostTemplates);
            }
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
    public function findOnDemandHostMacros(int $hostId, bool $isUsingInheritance = false): array
    {
        if ($isUsingInheritance) {
            $request = $this->translateDbName(
                'WITH RECURSIVE inherite AS (
                    SELECT relation.host_host_id, relation.host_tpl_id, relation.order, demand.host_macro_id,
                        demand.host_macro_name, 0 AS level
                    FROM `:db`.host
                    LEFT JOIN `:db`.host_template_relation relation
                        ON relation.host_host_id  = host.host_id
                    LEFT JOIN on_demand_macro_host demand
                        ON demand.host_host_id = host.host_id
                    WHERE host.host_id = :host_id
                    UNION ALL
                    SELECT relation.host_host_id, relation.host_tpl_id, relation.order, demand.host_macro_id,
                        demand.host_macro_name, inherite.level + 1
                    FROM `:db`.host
                    INNER JOIN inherite
                        ON inherite.host_tpl_id = host.host_id
                    LEFT JOIN `:db`.host_template_relation relation
                        ON relation.host_host_id  = host.host_id
                    LEFT JOIN on_demand_macro_host demand
                        ON demand.host_host_id = host.host_id
                )
                SELECT macro.host_macro_id AS id, macro.host_macro_name AS name,
                    macro.host_macro_value AS `value`, macro.macro_order AS `order`, macro.host_host_id AS host_id,
                    CASE
                        WHEN is_password IS NULL THEN \'0\'
                        ELSE is_password
                    END is_password, description
                FROM (
                    SELECT * FROM inherite
                    WHERE host_macro_id IS NOT NULL
                    GROUP BY inherite.level, inherite.order, host_macro_name
                ) AS tpl
                INNER JOIN `:db`.on_demand_macro_host macro
                    ON macro.host_macro_id = tpl.host_macro_id
                GROUP BY tpl.host_macro_name'
            );
        } else {
            $request = $this->translateDbName(
                'SELECT host_macro_id AS id, host_macro_name AS name, host_macro_value AS `value`,
                    macro_order AS `order`, host_host_id AS host_id,
                    CASE
                        WHEN is_password IS NULL THEN \'0\'
                        ELSE is_password
                    END is_password, description
                FROM `:db`.on_demand_macro_host
                WHERE host_host_id = :host_id'
            );
        }

        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $statement->execute();

        $hostMacros = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $hostMacros[] = EntityCreator::createEntityByArray(
                HostMacro::class,
                $record
            );
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

        if (empty($names)) {
            return [];
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
            LEFT JOIN `centreon`.view_img_dir_relation iconR
                ON iconR.img_img_id = icon.img_id
            LEFT JOIN `centreon`.view_img_dir iconD
                ON iconD.dir_id = iconR.dir_dir_parent_id
            LEFT JOIN `:db`.view_img smi
                ON smi.img_id = ext.ehi_statusmap_image
            LEFT JOIN centreon.host_template_relation htr
                ON htr.host_host_id = h.host_id
            LEFT JOIN centreon.options AS opt
                ON opt.key = \'nagios_path_img\''
        );
        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest)
            ? $searchRequest . ' AND host_register = \'0\' GROUP BY h.host_id'
            : ' WHERE host_register = \'0\' GROUP BY h.host_id';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY h.host_id ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();
        $statement = $this->db->query($request);

        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $hostTemplates = [];
        if ($statement !== false) {
            while (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $hostTemplates[] = HostTemplateFactoryRdb::create($result);
            }
        }
        return $hostTemplates;
    }
}
