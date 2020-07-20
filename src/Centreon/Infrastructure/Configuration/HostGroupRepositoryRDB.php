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

namespace Centreon\Infrastructure\Monitoring;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Configuration\HostGroup\Interfaces\HostGroupRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * Database repository for the real time monitoring of services and host.
 *
 * @package Centreon\Infrastructure\Monitoring
 */
final class HostGroupRepositoryRDB extends AbstractRepositoryDRB implements HostGroupRepositoryInterface
{
    /**
     * @var AccessGroup[] List of access group used to filter the requests
     */
    private $accessGroups = [];

    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * MonitoringRepositoryRDB constructor.
     *
     * @param DatabaseConnection $pdo
     */
    public function __construct(DatabaseConnection $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * @inheritDoc
     */
    public function filterByAccessGroups(?array $accessGroups): MonitoringRepositoryInterface
    {
        $this->accessGroups = $accessGroups;
        return $this;
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
    public function findHostGroupsByIds(array $hostGroupIds): array
    {
        $hostGroups = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $hostGroups;
        }

        $hostGroupConcordanceArray = [
            'id' => 'hg.hostgroup_id',
            'name' => 'hg.name'
        ];

        // To allow to find host groups relating to host information
        $hostConcordanceArray = [
            'host.id' => 'h.host_id',
            'host.name' => 'h.name',
            'host.alias' => 'h.alias',
            'host.address' => 'h.address',
            'host.display_name' => 'h.display_name',
            'host.state' => 'h.state',
            'poller.id' => 'h.instance_id'
        ];

        $searchParameters = $this->sqlRequestTranslator->getRequestParameters()->extractSearchNames();

        $shouldJoinHost = false;
        if (count(array_intersect($searchParameters, array_keys($hostConcordanceArray))) > 0) {
            $shouldJoinHost = true;
            $hostGroupConcordanceArray = array_merge($hostGroupConcordanceArray, $hostConcordanceArray);
        }

        //if the filter is for specific host id, remove it from search parameters
        if (null !== $hostId) {
            $shouldJoinHost = true;
            unset($hostConcordanceArray['host.id']);
        }

        $this->sqlRequestTranslator->setConcordanceArray($hostGroupConcordanceArray);

        $sqlExtraParameters = [];
        $subRequest = '';
        if (!$this->isAdmin()) {
            $sqlExtraParameters = [':contact_id' => [\PDO::PARAM_INT => $this->contact->getId()]];

            // Not an admin, we must to filter on contact
            $subRequest .=
                ' INNER JOIN `:db`.acl_resources_hg_relations hgr
                    ON hgr.hg_hg_id = hg.hostgroup_id
                INNER JOIN `:db`.acl_resources res
                    ON res.acl_res_id = hgr.acl_res_id
                    AND res.acl_res_activate = \'1\'
                INNER JOIN `:db`.acl_res_group_relations rgr
                    ON rgr.acl_res_id = res.acl_res_id
                INNER JOIN `:db`.acl_groups grp
                    ON grp.acl_group_id IN ('
                . $this->accessGroupIdToString($this->accessGroups)
                . ') AND grp.acl_group_activate = \'1\'
                    AND grp.acl_group_id = rgr.acl_group_id
                LEFT JOIN `:db`.acl_group_contacts_relations gcr
                    ON gcr.acl_group_id = grp.acl_group_id
                LEFT JOIN `:db`.acl_group_contactgroups_relations gcgr
                    ON gcgr.acl_group_id = grp.acl_group_id
                LEFT JOIN `:db`.contactgroup_contact_relation cgcr
                    ON cgcr.contactgroup_cg_id = gcgr.cg_cg_id
                    AND cgcr.contact_contact_id = :contact_id 
                    OR gcr.contact_contact_id = :contact_id';
        }

        // This join will only be added if a search parameter corresponding to one of the host parameter
        if ($shouldJoinHost) {
            $subRequest .=
                ' INNER JOIN `:dbstg`.hosts_hostgroups hhg 
                    ON hhg.hostgroup_id = hg.hostgroup_id
                INNER JOIN `:dbstg`.hosts h
                    ON h.host_id = hhg.host_id
                    AND h.enabled = \'1\'
                    AND h.name NOT LIKE \'_Module_BAM%\'';

            if (!$this->isAdmin()) {
                $subRequest .=
                    ' INNER JOIN `:dbstg`.`centreon_acl` acl
                        ON acl.host_id = h.host_id
                        AND acl.service_id IS NULL
                        AND acl.group_id = grp.acl_group_id';
            }
        }

        $request = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT hg.* FROM `:dbstg`.`hostgroups` hg ' . $subRequest;
        $request = $this->translateDbName($request);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();

        //if host id is provided, filter results by it
        if (null !== $hostId) {
            $searchByHostIdQuery = !is_null($searchRequest) ? ' AND h.host_id = :hostId' : ' WHERE h.host_id = :hostId';
        } else {
            $searchByHostIdQuery = '';
        }

        $request .= !is_null($searchRequest) ? $searchRequest : '';
        $request .= $searchByHostIdQuery;

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest) ? $sortRequest : ' ORDER BY hg.name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        // We bind extra parameters according to access rights
        foreach ($sqlExtraParameters as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        if (null !== $hostId) {
            //bind the host id to search for it if provided
            $statement->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        }

        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hostGroups[] = EntityCreator::createEntityByArray(
                HostGroup::class,
                $result
            );
        }

        return $hostGroups;
    }

    private function isAdmin(): bool
    {
        return ($this->contact !== null)
            ? $this->contact->isAdmin()
            : false;
    }

    /**
     * @inheritDoc
     */
    public function setContact(ContactInterface $contact): MonitoringRepositoryInterface
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * @return bool Return FALSE if the contact is an admin or has at least one access group.
     */
    private function hasNotEnoughRightsToContinue(): bool
    {
        return ($this->contact !== null)
            ? !($this->contact->isAdmin() || count($this->accessGroups) > 0)
            : count($this->accessGroups) == 0;
    }
}
