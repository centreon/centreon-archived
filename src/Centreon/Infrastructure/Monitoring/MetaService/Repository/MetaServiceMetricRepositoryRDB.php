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

namespace Centreon\Infrastructure\Monitoring\MetaService\Repository;

use Assert\AssertionFailedException;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Infrastructure\Monitoring\MetaService\Repository\Model\MetaServiceMetricFactoryRdb;
use Centreon\Domain\Monitoring\MetaService\Interfaces\MetaServiceMetric\MetaServiceMetricRepositoryInterface;

/**
 * This class is designed to represent the MariaDb repository to manage meta service metrics.
 *
 * @package Centreon\Infrastructure\Monitoring\MetaService\Repository
 */
class MetaServiceMetricRepositoryRDB extends AbstractRepositoryDRB implements
    MetaServiceMetricRepositoryInterface
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
    public function findByMetaIdAndContact(int $metaId, ContactInterface $contact): ?array
    {
        return $this->findMetricsRequest($metaId, $contact->getId());
    }

    /**
     * @inheritDoc
     */
    public function findByMetaId(int $metaId): ?array
    {
        return $this->findMetricsRequest($metaId, null);
    }

    /**
     * @inheritDoc
     */
    public function findByContactAndSqlRegexp(
        string $metricName,
        string $regexpString,
        ContactInterface $contact
    ): ?array {
        return $this->findMetricsBySqlRegexpRequest($metricName, $regexpString, $contact->getId());
    }

    /**
     * @inheritDoc
     */
    public function findBySqlRegexp(string $metricName, string $regexpString): ?array
    {
        return $this->findMetricsBySqlRegexpRequest($metricName, $regexpString, null);
    }

    private function findMetricsRequest(int $metaId, ?int $contactId): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'msr.metric_id',
            'name' => 'm.metric_name',
            'service_description' => 'idd.service_description',
            'host_name' => 'idd.host_name',
        ]);

        $request = "SELECT msr.metric_id,
                msr.host_id,
                idd.host_name,
                idd.service_id,
                idd.service_description,
                m.metric_name,
                m.unit_name,
                m.current_value
            FROM `:db`.`meta_service_relation` msr
            INNER JOIN `:dbstg`.`metrics` m
                ON msr.metric_id = m.metric_id
            INNER JOIN `:dbstg`.`index_data` idd
                ON m.index_id = idd.id";

        if ($contactId !== null) {
            $request .= " INNER JOIN `:db`.acl_resources_host_relations arhr
                    ON msr.host_id = arhr.host_host_id
                INNER JOIN `:db`.acl_resources res
                    ON arhr.acl_res_id = res.acl_res_id
                INNER JOIN `:db`.acl_res_group_relations argr
                    ON res.acl_res_id = argr.acl_res_id
                INNER JOIN `:db`.acl_groups ag
                    ON argr.acl_group_id = ag.acl_group_id
                LEFT JOIN `:db`.acl_group_contacts_relations agcr
                    ON ag.acl_group_id = agcr.acl_group_id
                    AND agcr.contact_contact_id = :contact_id
                LEFT JOIN `:db`.acl_group_contactgroups_relations agcgr
                    ON ag.acl_group_id = agcgr.acl_group_id
                LEFT JOIN `:db`.contactgroup_contact_relation cgcr
                    ON cgcr.contactgroup_cg_id = agcgr.cg_cg_id
                    AND cgcr.contact_contact_id = :contact_id";
        }

        $request = $this->translateDbName($request);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest)
            ? $searchRequest . ' AND msr.meta_id = :id'
            : ' WHERE msr.meta_id = :id';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY m.metric_name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();
        $statement = $this->db->prepare($request);

        $statement->bindValue(':id', $metaId, \PDO::PARAM_INT);

        if ($contactId !== null) {
            $statement->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);
        }
        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $metaServicesMetrics = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $metaServicesMetrics[] = MetaServiceMetricFactoryRdb::create($record);
        }
        return $metaServicesMetrics;
    }

    private function findMetricsBySqlRegexpRequest(string $metricName, string $regexpString, ?int $contactId): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'm.metric_id',
            'name' => 'm.metric_name',
            'service_description' => 'idd.service_description',
            'host_name' => 'idd.host_name',
        ]);

        $request = "SELECT m.metric_id,
                idd.host_id,
                idd.host_name,
                idd.service_id,
                idd.service_description,
                m.metric_name,
                m.unit_name,
                m.current_value
            FROM `:dbstg`.metrics m, `:dbstg`.index_data idd";

        // apply ACL filter
        if ($contactId !== null) {
            $request .= " INNER JOIN `:db`.acl_resources_host_relations arhr
                    ON idd.host_id = arhr.host_host_id
                INNER JOIN `:db`.acl_resources res
                    ON arhr.acl_res_id = res.acl_res_id
                INNER JOIN `:db`.acl_res_group_relations argr
                    ON res.acl_res_id = argr.acl_res_id
                INNER JOIN `:db`.acl_groups ag
                    ON argr.acl_group_id = ag.acl_group_id
                LEFT JOIN `:db`.acl_group_contacts_relations agcr
                    ON ag.acl_group_id = agcr.acl_group_id
                    AND agcr.contact_contact_id = :contact_id
                LEFT JOIN `:db`.acl_group_contactgroups_relations agcgr
                    ON ag.acl_group_id = agcgr.acl_group_id
                LEFT JOIN `:db`.contactgroup_contact_relation cgcr
                    ON cgcr.contactgroup_cg_id = agcgr.cg_cg_id
                    AND cgcr.contact_contact_id = :contact_id";
        }

        $request = $this->translateDbName($request);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest)
            ? $searchRequest . ' AND idd.service_description LIKE :regexp AND idd.id = m.index_id'
            : ' WHERE idd.service_description LIKE :regexp AND idd.id = m.index_id';

        $request .= ' AND m.metric_name = :metricName';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY m.metric_name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();
        $statement = $this->db->prepare($request);

        $statement->bindValue(':regexp', $regexpString, \PDO::PARAM_STR);

        $statement->bindValue(':metricName', $metricName, \PDO::PARAM_STR);

        if ($contactId !== null) {
            $statement->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);
        }
        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $metaServicesMetrics = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $metaServicesMetrics[] = MetaServiceMetricFactoryRdb::create($record);
        }
        return $metaServicesMetrics;
    }
}
