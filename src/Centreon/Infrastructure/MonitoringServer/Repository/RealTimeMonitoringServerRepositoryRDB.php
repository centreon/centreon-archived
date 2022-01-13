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

namespace Centreon\Infrastructure\MonitoringServer\Repository;

use Assert\AssertionFailedException;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\MonitoringServer\Model\RealTimeMonitoringServer;
use Centreon\Infrastructure\RequestParameters\Interfaces\NormalizerInterface;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Domain\MonitoringServer\Interfaces\RealTimeMonitoringServerRepositoryInterface;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Centreon\Infrastructure\MonitoringServer\Repository\Model\RealTimeMonitoringServerFactoryRdb;

/**
 * This class is designed to represent the MariaDb repository to manage host category.
 *
 * @package Centreon\Infrastructure\MonitoringServer\Repository
 */
class RealTimeMonitoringServerRepositoryRDB extends AbstractRepositoryDRB implements
    RealTimeMonitoringServerRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @param DatabaseConnection $db
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
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
    public function findAll(): array
    {
        return $this->findAllRequest([]);
    }

    /**
     * @inheritDoc
     */
    public function findByIds(array $ids): array
    {
        return $this->findAllRequest($ids);
    }

    /**
     * @inheritDoc
     */
    public function findAllowedMonitoringServers(ContactInterface $contact): array
    {
        $request = $this->translateDbName(
            'SELECT SQL_CALC_FOUND_ROWS *
            FROM `:db`.nagios_server as ns
            INNER JOIN `:db`.acl_resources_poller_relations arpr
                ON ns.id = arpr.poller_id
            INNER JOIN `:db`.acl_resources res
                ON arpr.acl_res_id = res.acl_res_id
            INNER JOIN `:db`.acl_res_group_relations argr
                ON res.acl_res_id = argr.acl_res_id
            INNER JOIN `:db`.acl_groups ag
                ON argr.acl_group_id = ag.acl_group_id
            LEFT JOIN `:db`.acl_group_contacts_relations agcr
                ON ag.acl_group_id = agcr.acl_group_id
            LEFT JOIN `:db`.acl_group_contactgroups_relations agcgr
                ON ag.acl_group_id = agcgr.acl_group_id
            LEFT JOIN `:db`.contactgroup_contact_relation cgcr
                ON cgcr.contactgroup_cg_id = agcgr.cg_cg_id
            WHERE (agcr.contact_contact_id = :contact_id OR cgcr.contact_contact_id = :contact_id)'
        );

        $statement = $this->db->prepare($request);
        $statement->bindValue(':contact_id', $contact->getId(), \PDO::PARAM_INT);
        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');

        /**
         * @var MonitoringServer[] $allowedMonitoringServers
         */
        $allowedMonitoringServers = [];

        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            // it means here that there is poller relations with this user
            if ((int) $total > 0) {
                while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                    $allowedMonitoringServers[] = EntityCreator::createEntityByArray(
                        MonitoringServer::class,
                        $record
                    );
                }
            } else {
                // if no relations found for this user it means that he can see all
                $statement = $this->db->prepare('SELECT * FROM nagios_server');
                $statement->execute();
                while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                    $allowedMonitoringServers[] = EntityCreator::createEntityByArray(
                        MonitoringServer::class,
                        $record
                    );
                }
            }
        }

        return $allowedMonitoringServers;
    }

    /**
     * Find all RealTime Monitoring Servers filtered by contact id.
     *
     * @param int[] $ids Monitoring Server ids
     * @return RealTimeMonitoringServer[]
     * @throws AssertionFailedException
     * @throws \InvalidArgumentException
     */
    private function findAllRequest(array $ids): array
    {
        $collector = new StatementCollector();
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'instances.instance_id',
            'name' => 'instances.name',
            'running' => 'instances.running',
        ]);
        $this->sqlRequestTranslator->addNormalizer(
            'running',
            new class implements NormalizerInterface
            {
                /**
                 * @inheritDoc
                 */
                public function normalize($valueToNormalize)
                {
                    if (is_bool($valueToNormalize)) {
                        return ($valueToNormalize === true) ? '1' : '0';
                    }
                    return $valueToNormalize;
                }
            }
        );

        $request = $this->translateDbName('SELECT SQL_CALC_FOUND_ROWS * FROM `:dbstg`.instances');

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= $searchRequest !== null ? $searchRequest : '';

        if (!empty($ids)) {
            $instanceIds = [];
            $request .= (empty($searchRequest)) ? ' WHERE ' : ' AND ';
            foreach ($ids as $index => $instanceId) {
                $key = ":instanceId_{$index}";
                $instanceIds[] = $key;
                $collector->addValue($key, $instanceId, \PDO::PARAM_INT);
            }
            $request .= 'instances.instance_id IN (' . implode(', ', $instanceIds) . ')';
        }

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY instances.name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();
        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $collector->addValue($key, $value, $type);
        }

        $collector->bind($statement);
        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $realTimeMonitoringServers = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $realTimeMonitoringServers[] = RealTimeMonitoringServerFactoryRdb::create($record);
        }

        return $realTimeMonitoringServers;
    }
}
