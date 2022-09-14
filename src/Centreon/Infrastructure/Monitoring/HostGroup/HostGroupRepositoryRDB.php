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

namespace Centreon\Infrastructure\Monitoring\HostGroup;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Monitoring\HostGroup\Interfaces\HostGroupRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;

/**
 * Database repository for the real time monitoring of hostgroups.
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
    public function filterByAccessGroups(?array $accessGroups): HostGroupRepositoryInterface
    {
        $this->accessGroups = $accessGroups;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findHostGroupsByNames(array $hostGroupNames): array
    {
        $hostGroups = [];

        if ($this->hasNotEnoughRightsToContinue() || empty($hostGroupNames)) {
            return $hostGroups;
        }

        $bindValues = [];
        $subRequest = '';
        if (!$this->isAdmin()) {
            $bindValues[':contact_id'] = [\PDO::PARAM_INT => $this->contact->getId()];

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

        $request = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT hg.* FROM `:dbstg`.`hostgroups` hg ' . $subRequest;
        $request = $this->translateDbName($request);

        $bindHostGroupNames = [];
        foreach ($hostGroupNames as $index => $hostGroupName) {
            $bindHostGroupNames[':host_group_name_' . $index] = [\PDO::PARAM_STR => $hostGroupName];
        }
        $bindValues = array_merge($bindValues, $bindHostGroupNames);
        $request .= ' WHERE hg.name IN (' . implode(',', array_keys($bindHostGroupNames)) . ')';

        // Sort
        $request .= ' ORDER BY hg.name ASC';

        $statement = $this->db->prepare($request);

        // We bind extra parameters according to access rights
        foreach ($bindValues as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hostGroups[] = EntityCreator::createEntityByArray(
                HostGroup::class,
                $result
            );
        }

        return $hostGroups;
    }

    /**
     * @inheritDoc
     */
    public function findHostGroupsByIds(array $hostGroupIds): array
    {
        $hostGroups = [];

        if ($this->hasNotEnoughRightsToContinue() || empty($hostGroupIds)) {
            return $hostGroups;
        }

        $bindValues = [];
        $subRequest = '';
        if (!$this->isAdmin()) {
            $bindValues[':contact_id'] = [\PDO::PARAM_INT => $this->contact->getId()];

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

        $request = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT hg.* FROM `:dbstg`.`hostgroups` hg ' . $subRequest;
        $request = $this->translateDbName($request);

        $bindHostGroupIds = [];
        foreach ($hostGroupIds as $index => $hostGroupId) {
            $bindHostGroupIds[':host_group_id_' . $index] = [\PDO::PARAM_INT => $hostGroupId];
        }
        $bindValues = array_merge($bindValues, $bindHostGroupIds);
        $request .= ' WHERE hg.hostgroup_id IN (' . implode(',', array_keys($bindHostGroupIds)) . ')';

        // Sort
        $request .= ' ORDER BY hg.name ASC';

        $statement = $this->db->prepare($request);

        // We bind extra parameters according to access rights
        foreach ($bindValues as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hostGroups[] = EntityCreator::createEntityByArray(
                HostGroup::class,
                $result
            );
        }

        return $hostGroups;
    }

    /**
     * Check if the contact is admin
     *
     * @return bool
     */
    private function isAdmin(): bool
    {
        return ($this->contact !== null)
            ? $this->contact->isAdmin()
            : false;
    }

    /**
     * {@inheritDoc}
     */
    public function setContact(ContactInterface $contact): HostGroupRepositoryInterface
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
