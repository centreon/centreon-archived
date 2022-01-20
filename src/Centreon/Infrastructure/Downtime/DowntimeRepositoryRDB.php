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

namespace Centreon\Infrastructure\Downtime;

use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Downtime\Interfaces\DowntimeRepositoryInterface;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * Downtime repository for MySQL
 *
 * @package Centreon\Infrastructure\Downtime
 */
class DowntimeRepositoryRDB extends AbstractRepositoryDRB implements DowntimeRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @var AccessGroup[] List of access group used to filter the requests
     */
    private $accessGroups;
    /**
     * @var array<string, string>
     */
    private $downtimeConcordanceArray;

    public function __construct(
        DatabaseConnection $db,
        SqlRequestParametersTranslator $sqlRequestTranslator
    ) {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;

        $this->downtimeConcordanceArray = [
            // Relation for downtime
            'id' => 'dwt.downtime_id',
            'entry_time' => 'dwt.entry_time',
            'is_cancelled' => 'dwt.cancelled',
            'comment' => 'dwt.comment_data',
            'deletion_time' => 'dwt.deletion_time',
            'duration' => 'dwt.duration',
            'end_time' => 'dwt.end_time',
            'is_fixed' => 'dwt.fixed',
            'start_time' => 'dwt.start_time',
            // Relation for host
            'host.id' => 'hosts.host_id',
            'host.name' => 'hosts.name',
            'host.alias' => 'hosts.alias',
            'host.address' => 'hosts.address',
            'host.display_name' => 'hosts.display_name',
            'host.state' => 'h.state',
            // Relation for poller
            'poller.id' => 'hosts.instance_id',
            // Relation for contact
            'contact.id' => 'contact.contact_id',
            'contact.name' => 'contact.contact_name',
        ];
    }

    /**
     * @inheritDoc
     */
    public function forAccessGroups(array $accessGroups): DowntimeRepositoryInterface
    {
        $this->accessGroups = $accessGroups;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function findHostDowntimesForNonAdminUser(): array
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return [];
        }

        // Internal call for non admin user
        return $this->findHostDowntimes(false);
    }

    /**
     * @inheritDoc
     */
    public function findHostDowntimesForAdminUser(): array
    {
        // Internal call for an admin user
        return $this->findHostDowntimes(true);
    }

    /**
     * @return bool Return TRUE if the contact is an admin or has at least one access group.
     */
    private function hasNotEnoughRightsToContinue(): bool
    {
        return count($this->accessGroups) == 0;
    }

    /**
     * @param bool $isAdmin Indicates whether user is an admin
     * @return Downtime[]
     * @throws \Exception
     */
    private function findHostDowntimes(bool $isAdmin = false): array
    {
        $this->sqlRequestTranslator->setConcordanceArray($this->downtimeConcordanceArray);

        $aclRequest = '';

        if ($isAdmin === false) {
            $aclRequest =
                ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = dwt.host_id
                  AND acl.service_id IS NULL
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN ('
                    . $this->accessGroupIdToString($this->accessGroups) . ')';
        }

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT dwt.*, contact.contact_id AS author_id
            FROM `:dbstg`.downtimes dwt
            INNER JOIN `:dbstg`.hosts
                ON hosts.host_id = dwt.host_id
                AND dwt.service_id = 0
            LEFT JOIN `:db`.`contact`
                ON contact.contact_alias = dwt.author'
            . $aclRequest;

        return $this->processRequest($request);
    }

    /**
     * @inheritDoc
     */
    public function findOneDowntimeForAdminUser(int $downtimeId): ?Downtime
    {
        // Internal call for an admin user
        return $this->findOneDowntime($downtimeId, true);
    }

    /**
     * @inheritDoc
     */
    public function findOneDowntimeForNonAdminUser(int $downtimeId): ?Downtime
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return null;
        }

        // Internal call for non admin user
        return $this->findOneDowntime($downtimeId, false);
    }

    /**
     * Find one downtime linked to a host tacking into account or not the ACLs of host.
     *
     * @param int $downtimeId Downtime id
     * @param bool $isAdmin Indicates whether user is an admin
     * @return Downtime|null Return NULL if the downtime has not been found
     * @throws \Exception
     */
    private function findOneDowntime(int $downtimeId, bool $isAdmin = false): ?Downtime
    {
        $aclRequest = '';

        if ($isAdmin === false) {
            $aclRequest =
                ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = dwt.host_id
                  AND (acl.service_id = dwt.service_id OR acl.service_id IS NULL)
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN ('
                . $this->accessGroupIdToString($this->accessGroups) . ')';
        }

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT dwt.*, contact.contact_id AS author_id
            FROM `:dbstg`.downtimes dwt
            LEFT JOIN `:db`.`contact`
                ON contact.contact_alias = dwt.author'
            . $aclRequest
            . ' WHERE dwt.downtime_id = :downtime_id';

        $request = $this->translateDbName($request);

        $prepare = $this->db->prepare($request);
        $prepare->bindValue(':downtime_id', $downtimeId, \PDO::PARAM_INT);
        $prepare->execute();

        if (false !== ($row = $prepare->fetch(\PDO::FETCH_ASSOC))) {
            return EntityCreator::createEntityByArray(
                Downtime::class,
                $row
            );
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function findDowntimesForAdminUser(): array
    {
        // Internal call for an admin user
        return $this->findDowntimes(true);
    }

    /**
     * @inheritDoc
     */
    public function findDowntimesForNonAdminUser(): array
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return [];
        }

        // Internal call for non admin user
        return $this->findDowntimes(false);
    }

    /**
     * Find all downtimes.
     *
     * @param bool $isAdmin Indicates whether user is an admin
     * @return Downtime[]
     * @throws \Exception
     */
    private function findDowntimes(bool $isAdmin): array
    {
        $serviceConcordanceArray = [
            // Relation for service
            'service.id' => 'srv.service_id',
            'service.display_name' => 'srv.display_name'
        ];

        /*
         * If the search parameters contain at least one key corresponding to the services, we must:
         *  1. taking into account the search for service
         *  2. add the join to the services table
         */
        $downtimeConcordanceArray = $this->downtimeConcordanceArray;
        $searchParameters = $this->sqlRequestTranslator->getRequestParameters()->extractSearchNames();
        $shouldJoinService = false;
        if (count(array_intersect($searchParameters, array_keys($serviceConcordanceArray))) > 0) {
            $shouldJoinService = true;
            $downtimeConcordanceArray = array_merge($downtimeConcordanceArray, $serviceConcordanceArray);
        }

        $this->sqlRequestTranslator->setConcordanceArray($downtimeConcordanceArray);

        $aclRequest = '';

        if ($isAdmin === false) {
            $aclRequest =
                ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = dwt.host_id
                  AND (acl.service_id = dwt.service_id OR acl.service_id IS NULL)
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN ('
                . $this->accessGroupIdToString($this->accessGroups) . ') ';
        }

        $serviceRequest = ($shouldJoinService)
            ? ' INNER JOIN `:dbstg`.services srv
                 ON srv.service_id = dwt.service_id
                 AND srv.host_id = hosts.host_id '
            : '';

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT dwt.*, contact.contact_id AS author_id
            FROM `:dbstg`.downtimes dwt
            LEFT JOIN `:db`.`contact`
              ON contact.contact_alias = dwt.author
            INNER JOIN `:dbstg`.hosts
              ON hosts.host_id = dwt.host_id'
            . $serviceRequest
            . $aclRequest;

        return $this->processRequest($request);
    }

    /**
     * @inheritDoc
     */
    public function findDowntimesByHostForAdminUser(int $hostId, bool $withServices = false): array
    {
        // Internal call for an admin user
        return $this->findDowntimesByHost($hostId, $withServices, true);
    }

    /**
     * @inheritDoc
     */
    public function findDowntimesByHostForNonAdminUser(int $hostId, bool $withServices = false): array
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return [];
        }

        // Internal call for non admin user
        return $this->findDowntimesByHost($hostId, $withServices, false);
    }

    /**
     * Find all downtimes for a host.
     *
     * @param int $hostId Host id for which we want to find downtimes.
     * @param bool $withServices
     * @param bool $isAdmin Indicates whether user is an admin
     * @return Downtime[]
     * @throws \Exception
     */
    private function findDowntimesByHost(int $hostId, bool $withServices, bool $isAdmin = false): array
    {
        $concordanceArray = [
            // Relation for downtime
            'id' => 'dwt.downtime_id',
            'entry_time' => 'dwt.entry_time',
            'is_cancelled' => 'dwt.cancelled',
            'comment' => 'dwt.comment_data',
            'deletion_time' => 'dwt.deletion_time',
            'duration' => 'dwt.duration',
            'end_time' => 'dwt.end_time',
            'is_fixed' => 'dwt.fixed',
            'start_time' => 'dwt.start_time',
            // Relation for contact
            'contact.id' => 'contact.contact_id',
            'contact.name' => 'contact.contact_name',
        ];

        $this->sqlRequestTranslator->setConcordanceArray($concordanceArray);

        $aclRequest = '';

        if ($isAdmin === false) {
            $aclRequest =
                ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = dwt.host_id'
                . ($withServices ? '' : ' AND acl.service_id IS NULL')
                . ' INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN ('
                . $this->accessGroupIdToString($this->accessGroups) . ')';
        }

        $request = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT
            dwt.downtime_id,
            dwt.entry_time,
            dwt.host_id,
            dwt.service_id,
            dwt.author,
            dwt.cancelled,
            `cmts`.data AS `comment_data`,
            dwt.deletion_time,
            dwt.duration,
            dwt.end_time,
            dwt.fixed,
            dwt.instance_id,
            dwt.internal_id,
            dwt.start_time,
            dwt.actual_start_time,
            dwt.actual_end_time,
            dwt.started,
            dwt.triggered_by,
            dwt.type,
            contact.contact_id AS `author_id`
            FROM `:dbstg`.downtimes dwt
            LEFT JOIN `:db`.`contact`
                ON contact.contact_alias = dwt.author
            LEFT JOIN `:dbstg`.`comments` AS `cmts`
                ON `cmts`.host_id = dwt.host_id AND `cmts`.service_id = dwt.service_id
            INNER JOIN `:dbstg`.hosts
              ON hosts.host_id = dwt.host_id
              AND dwt.host_id = :host_id'
                . ($withServices ? '' : ' AND dwt.service_id = 0')
            . $aclRequest;

        $this->sqlRequestTranslator->addSearchValue(
            ':host_id',
            [\PDO::PARAM_INT => $hostId]
        );

        return $this->processRequest($request);
    }

    /**
     * @inheritDoc
     */
    public function findServicesDowntimesForNonAdminUser(): array
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return [];
        }

        // Internal call for non admin user
        return $this->findServicesDowntimes(false);
    }

    /**
     * @inheritDoc
     */
    public function findServicesDowntimesForAdminUser(): array
    {
        // Internal call for an admin user
        return $this->findServicesDowntimes(true);
    }

    /**
     * Find all downtimes of all services.
     *
     * @param bool $isAdmin $isAdmin Indicates whether user is an admin
     * @return Downtime[]
     * @throws \Exception
     */
    private function findServicesDowntimes(bool $isAdmin): array
    {
        $this->sqlRequestTranslator->setConcordanceArray($this->downtimeConcordanceArray);

        $aclRequest = '';

        if ($isAdmin === false) {
            $aclRequest =
                ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = dwt.host_id
                  AND acl.service_id = dwt.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN ('
                . $this->accessGroupIdToString($this->accessGroups) . ')';
        }

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT dwt.*, contact.contact_id AS author_id
            FROM `:dbstg`.downtimes dwt
            INNER JOIN `:dbstg`.hosts
                ON hosts.host_id = dwt.host_id
            INNER JOIN `:dbstg`.services srv
                ON srv.service_id = dwt.service_id
                AND srv.host_id = hosts.host_id
            LEFT JOIN `:db`.`contact`
                ON contact.contact_alias = dwt.author'
            . $aclRequest;

        return $this->processRequest($request);
    }

    /**
     * @inheritDoc
     */
    public function findDowntimesByServiceForNonAdminUser(int $hostId, int $serviceId): array
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return [];
        }

        // Internal call for non admin user
        return $this->findDowntimesByService($hostId, $serviceId, false);
    }

    /**
     * @inheritDoc
     */
    public function findDowntimesByServiceForAdminUser(int $hostId, int $serviceId): array
    {
        // Internal call for an admin user
        return $this->findDowntimesByService($hostId, $serviceId, true);
    }

    /**
     *  Find all downtimes for a service (linked to a host).
     *
     * @param int $hostId Host id linked to this service
     * @param int $serviceId Service id for which we want to find downtimes
     * @param bool $isAdmin Indicates whether user is an admin
     * @return Downtime[]
     * @throws \Exception
     */
    private function findDowntimesByService(int $hostId, int $serviceId, bool $isAdmin): array
    {
        $concordanceArray = [
            // Relation for downtime
            'id' => 'dwt.downtime_id',
            'entry_time' => 'dwt.entry_time',
            'is_cancelled' => 'dwt.cancelled',
            'comment' => 'dwt.comment_data',
            'deletion_time' => 'dwt.deletion_time',
            'duration' => 'dwt.duration',
            'end_time' => 'dwt.end_time',
            'is_fixed' => 'dwt.fixed',
            'start_time' => 'dwt.start_time',
        ];

        $this->sqlRequestTranslator->setConcordanceArray($concordanceArray);

        $aclRequest = '';

        if ($isAdmin === false) {
            $aclRequest =
                ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = dwt.host_id
                  AND acl.service_id = dwt.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN ('
                . $this->accessGroupIdToString($this->accessGroups) . ')';
        }

        $request = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT
            dwt.downtime_id,
            dwt.entry_time,
            dwt.host_id,
            dwt.service_id,
            dwt.author,
            dwt.cancelled,
            `cmts`.data AS `comment_data`,
            dwt.deletion_time,
            dwt.duration,
            dwt.end_time,
            dwt.fixed,
            dwt.instance_id,
            dwt.internal_id,
            dwt.start_time,
            dwt.actual_start_time,
            dwt.actual_end_time,
            dwt.started,
            dwt.triggered_by,
            dwt.type,
            contact.contact_id AS `author_id`
            FROM `:dbstg`.downtimes dwt
            INNER JOIN `:dbstg`.hosts
                ON hosts.host_id = dwt.host_id
            INNER JOIN `:dbstg`.services srv
                ON srv.service_id = dwt.service_id
                AND srv.host_id = hosts.host_id
                AND dwt.host_id = :host_id
                AND srv.service_id = :service_id
            LEFT JOIN `:db`.`contact`
                ON contact.contact_alias = dwt.author
            LEFT JOIN `:dbstg`.`comments` AS `cmts`
                ON `cmts`.host_id = dwt.host_id AND `cmts`.service_id = dwt.service_id
                AND `cmts`.deletion_time IS NULL'
            . $aclRequest;

        $this->sqlRequestTranslator->addSearchValue(':host_id', [\PDO::PARAM_INT => $hostId]);
        $this->sqlRequestTranslator->addSearchValue(':service_id', [\PDO::PARAM_INT => $serviceId]);
        return $this->processRequest($request);
    }

    /**
     * Execute the request and retrieve the downtimes list
     *
     * @param string $request Request to execute
     * @return Downtime[]
     * @throws \Exception
     */
    private function processRequest(string $request): array
    {
        $request = $this->translateDbName($request);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest) ? $sortRequest : ' ORDER BY dwt.downtime_id DESC';

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
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $hostDowntimes = [];

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hostDowntimes[] = EntityCreator::createEntityByArray(
                Downtime::class,
                $result
            );
        }

        return $hostDowntimes;
    }
}
