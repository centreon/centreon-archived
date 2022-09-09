<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace CentreonClapi\Repository;

class AclGroupRepository
{
    /**
     * @param \PDO $db
     */
    public function __construct(private \PDO $db)
    {
    }

    /**
     * Get Acl Group Ids by their action id.
     *
     * @param int $actionId
     * @return int[]
     */
    public function getAclGroupIdsByActionId(int $actionId): array
    {
        $aclGroupIds = [];
        $statement = $this->db->prepare(
            "SELECT DISTINCT acl_group_id FROM acl_group_actions_relations
                WHERE acl_action_id = :aclActionId"
        );
        $statement->bindValue(":aclActionId", $actionId, \PDO::PARAM_INT);
        $statement->execute();
        while ($result = $statement->fetch()) {
            $aclGroupIds[] = (int) $result["acl_group_id"];
        };

        return $aclGroupIds;
    }

    /**
     * Get User ids by their acl group.
     *
     * @param integer $aclGroup
     * @return int[]
     */
    public function getUsersIdsByAclGroupIds(array $aclGroupIds): array
    {
        if (empty($aclGroupIds)) {
            return [];
        }

        $queryValues = [];
        foreach ($aclGroupIds as $index => $aclGroupId) {
            $sanitizedAclGroupId = filter_var($aclGroupId, FILTER_VALIDATE_INT);
            if ($sanitizedAclGroupId === false) {
                throw new \InvalidArgumentException("Invalid ID");
            }
            $queryValues[":acl_group_id_" . $index] = $sanitizedAclGroupId;
        }

        $aclGroupIdQueryString = "(" . implode(", ", array_keys($queryValues)) . ")";
        $statement = $this->db->prepare(
            "SELECT DISTINCT `contact_contact_id` FROM `acl_group_contacts_relations`
                WHERE `acl_group_id`
                IN $aclGroupIdQueryString"
        );
        foreach ($queryValues as $bindParameter => $bindValue) {
            $statement->bindValue($bindParameter, $bindValue, \PDO::PARAM_INT);
        }
        $statement->execute();
        $userIds = [];
        while ($result = $statement->fetch()) {
            $userIds[] = (int) $result["contact_contact_id"];
        }

        return $userIds;
    }
}
