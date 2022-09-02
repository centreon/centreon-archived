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

namespace Centreon\Infrastructure\HostConfiguration\Repository;

use Assert\AssertionFailedException;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\Interfaces\HostSeverity\HostSeverityReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Model\HostSeverity;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\HostConfiguration\Repository\Model\HostSeverityFactoryRdb;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\Interfaces\NormalizerInterface;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * This class is designed to represent the MariaDb repository to manage host severity.
 *
 * @package Centreon\Infrastructure\HostConfiguration\Repository
 */
class HostSeverityRepositoryRDB extends AbstractRepositoryDRB implements HostSeverityReadRepositoryInterface
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
    public function findAll(): array
    {
        return $this->findAllRequest(null);
    }

    /**
     * @inheritDoc
     */
    public function findAllByContact(ContactInterface $contact): array
    {
        return $this->findAllRequest($contact->getId());
    }

    /**
     * Find all severities filtered by contact id.
     *
     * @param int|null $contactId Contact id related to host severities
     * @return HostSeverity[]
     * @throws AssertionFailedException
     * @throws \InvalidArgumentException
     */
    private function findAllRequest(?int $contactId): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'hc.hc_id',
            'name' => 'hc.hc_name',
            'alias' => 'hc.hc_alias',
            'level' => 'hc.level',
            'is_activated' => 'hc.hc_activate',
        ]);
        $this->sqlRequestTranslator->addNormalizer(
            'is_activated',
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
        if ($contactId === null) {
            $request = $this->translateDbName(
                'SELECT SQL_CALC_FOUND_ROWS hc.*, icon.img_id AS img_id, icon.img_name AS img_name,
                CONCAT(iconD.dir_name,\'/\',icon.img_path) AS img_path, icon.img_comment AS img_comment
                FROM `:db`.hostcategories hc
                LEFT JOIN `:db`.view_img icon
                    ON icon.img_id = hc.icon_id
                LEFT JOIN `:db`.view_img_dir_relation iconR
                    ON iconR.img_img_id = icon.img_id
                LEFT JOIN `:db`.view_img_dir iconD
                    ON iconD.dir_id = iconR.dir_dir_parent_id'
            );
        } else {
            $request = $this->translateDbName(
                'SELECT SQL_CALC_FOUND_ROWS hc.*, icon.img_id AS img_id, icon.img_name AS img_name,
                CONCAT(iconD.dir_name,\'/\',icon.img_path) AS img_path, icon.img_comment AS img_comment
                FROM `:db`.hostcategories hc
                LEFT JOIN `:db`.view_img icon
                    ON icon.img_id = hc.icon_id
                LEFT JOIN `:db`.view_img_dir_relation iconR
                    ON iconR.img_img_id = icon.img_id
                LEFT JOIN `:db`.view_img_dir iconD
                    ON iconD.dir_id = iconR.dir_dir_parent_id
                INNER JOIN `:db`.acl_resources_hc_relations arhr
                    ON hc.hc_id = arhr.hc_id
                INNER JOIN `:db`.acl_resources res
                    ON arhr.acl_res_id = res.acl_res_id
                INNER JOIN `:db`.acl_res_group_relations argr
                    ON res.acl_res_id = argr.acl_res_id
                INNER JOIN `:db`.acl_groups ag
                    ON argr.acl_group_id = ag.acl_group_id
                LEFT JOIN `:db`.acl_group_contacts_relations agcr
                    ON ag.acl_group_id = agcr.acl_group_id
                LEFT JOIN `:db`.acl_group_contactgroups_relations agcgr
                    ON ag.acl_group_id = agcgr.acl_group_id
                LEFT JOIn `:db`.contactgroup_contact_relation cgcr
                    ON cgcr.contactgroup_cg_id = agcgr.cg_cg_id'
            );
        }
        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest)
            ? $searchRequest . ' AND hc.level IS NOT NULL'
            : '  WHERE hc.level IS NOT NULL';

        if ($contactId !== null) {
            $request .= ' AND (agcr.contact_contact_id = :contact_id OR cgcr.contact_contact_id = :contact_id)';
        }

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY hc.hc_name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();
        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if ($contactId !== null) {
            $statement->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);
        }
        $statement->execute();
        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }
        $hostSeverities = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $hostSeverities[] = HostSeverityFactoryRdb::create($record);
        }
        return $hostSeverities;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $hostSeverityId): ?HostSeverity
    {
        try {
            return $this->findByIdRequest($hostSeverityId, null);
        } catch (AssertionFailedException $ex) {
            throw new RepositoryException($ex->getMessage(), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findByIdAndContact(int $hostSeverityId, ContactInterface $contact): ?HostSeverity
    {
        try {
            return $this->findByIdRequest($hostSeverityId, $contact->getId());
        } catch (AssertionFailedException $ex) {
            throw new RepositoryException($ex->getMessage(), 0, $ex);
        }
    }

    /**
     * Find a severity by id and contact id.
     *
     * @param int $hostSeverityId Id of the host severity to be found
     * @param int|null $contactId Contact id related to host severity
     * @return HostSeverity|null
     * @throws AssertionFailedException
     */
    private function findByIdRequest(int $hostSeverityId, ?int $contactId): ?HostSeverity
    {
        if ($contactId === null) {
            $statement = $this->db->prepare(
                $this->translateDbName(
                    'SELECT hc.*, icon.img_id AS img_id, icon.img_name AS img_name,
                    CONCAT(iconD.dir_name,\'/\',icon.img_path) AS img_path, icon.img_comment AS img_comment
                    FROM `:db`.hostcategories hc
                    LEFT JOIN `:db`.view_img icon
                        ON icon.img_id = hc.icon_id
                    LEFT JOIN `:db`.view_img_dir_relation iconR
                        ON iconR.img_img_id = icon.img_id
                    LEFT JOIN `:db`.view_img_dir iconD
                        ON iconD.dir_id = iconR.dir_dir_parent_id
                    WHERE hc_id = :id AND hc.level IS NOT NULL'
                )
            );
        } else {
            $statement = $this->db->prepare(
                $this->translateDbName(
                    'SELECT hc.*, icon.img_id AS img_id, icon.img_name AS img_name,
                    CONCAT(iconD.dir_name,\'/\',icon.img_path) AS img_path, icon.img_comment AS img_comment
                    FROM `:db`.hostcategories hc
                    LEFT JOIN `:db`.view_img icon
                        ON icon.img_id = hc.icon_id
                    LEFT JOIN `:db`.view_img_dir_relation iconR
                        ON iconR.img_img_id = icon.img_id
                    LEFT JOIN `:db`.view_img_dir iconD
                        ON iconD.dir_id = iconR.dir_dir_parent_id
                    INNER JOIN `:db`.acl_resources_hc_relations arhr
                        ON hc.hc_id = arhr.hc_id
                    INNER JOIN `:db`.acl_resources res
                        ON arhr.acl_res_id = res.acl_res_id
                    INNER JOIN `:db`.acl_res_group_relations argr
                        ON res.acl_res_id = argr.acl_res_id
                    INNER JOIN `:db`.acl_groups ag
                        ON argr.acl_group_id = ag.acl_group_id
                    LEFT JOIN `:db`.acl_group_contacts_relations agcr
                        ON ag.acl_group_id = agcr.acl_group_id
                    LEFT JOIN `:db`.acl_group_contactgroups_relations agcgr
                        ON ag.acl_group_id = agcgr.acl_group_id
                    LEFT JOIn `:db`.contactgroup_contact_relation cgcr
                        ON  cgcr.contactgroup_cg_id = agcgr.cg_cg_id
                    WHERE hc.hc_id = :id AND hc.level IS NOT NULL
                        AND (agcr.contact_contact_id = :contact_id OR cgcr.contact_contact_id = :contact_id)'
                )
            );
            $statement->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);
        }

        $statement->bindValue(':id', $hostSeverityId, \PDO::PARAM_INT);
        $statement->execute();

        if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            return HostSeverityFactoryRdb::create($result);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function findByHost(Host $host): ?HostSeverity
    {
        $statement = $this->db->prepare(
            $this->translateDbName(
                'SELECT hc.*, icon.img_id AS img_id, icon.img_name AS img_name,
                CONCAT(iconD.dir_name,\'/\',icon.img_path) AS img_path, icon.img_comment AS img_comment
                FROM `:db`.hostcategories hc
                LEFT JOIN `:db`.view_img icon
                    ON icon.img_id = hc.icon_id
                LEFT JOIN `:db`.view_img_dir_relation iconR
                    ON iconR.img_img_id = icon.img_id
                LEFT JOIN `:db`.view_img_dir iconD
                    ON iconD.dir_id = iconR.dir_dir_parent_id
                INNER JOIN `:db`.hostcategories_relation hc_rel
                    ON hc.hc_id = hc_rel.hostcategories_hc_id
                WHERE hc.level IS NOT NULL
                AND hc_rel.host_host_id = :host_id'
            )
        );
        $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
        $statement->execute();

        if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            return HostSeverityFactoryRdb::create($result);
        }
        return null;
    }
}
