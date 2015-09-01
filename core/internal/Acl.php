<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

namespace Centreon\Internal;

class Acl
{
    const ADD = 1;
    const DELETE = 2;
    const UPDATE = 4;
    const VIEW = 8;
    const ADVANCED = 16;

    private $routes;
    private $isAdmin;
    private $userId;

    /**
     * Constructor
     *
     * @param \Centreon\User $userId
     */
    public function __construct($user)
    {
        $this->userId = $user->getId();
        $this->isAdmin = $user->isAdmin();
    }

    /**
     * Checks whether or not a flag is set
     *
     * @param int $values
     * @param int $flag
     * @return bool
     */
    public static function isFlagSet($values, $flag)
    {
        return (($values & $flag) === $flag);
    }

    /**
     * Get user ACL
     *
     * @param string $route
     */
    public function getUserAcl($route)
    {
        static $rules = null;

        if (is_null($rules)) {
            $rules = array();
            $db = Di::getDefault()->get('db_centreon');
            $stmt = $db->prepare(
                "SELECT DISTINCT acl_level, url 
                FROM cfg_acl_menu_menu_relations ammr, cfg_acl_groups_menus_relations agmr, cfg_menus m
                WHERE ammr.acl_menu_id = agmr.acl_menu_id
                AND ammr.menu_id = m.menu_id
                AND agmr.acl_group_id IN (
                    SELECT acl_group_id 
                    FROM cfg_acl_group_contacts_relations agcr
                    WHERE agcr.contact_contact_id = :contactid
                    UNION
                    SELECT acl_group_id
                    FROM cfg_acl_group_contactgroups_relations agcgr, cfg_contactgroups_contacts_relations ccr
                    WHERE agcgr.cg_cg_id = ccr.contactgroup_cg_id
                    AND ccr.contact_contact_id = :contactid
                ) "
            );
            $stmt->bindParam(':contactid', $this->userId);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $aclFlag = 0;
            foreach ($rows as $row) {
                if (!isset($rules[$row['url']])) {
                    $rules[$row['url']] = 0;
                }
                $rules[$row['url']] = $rules[$row['url']] | $row['acl_level'];
            }
        }
        foreach ($rules as $uri => $acl) {
            if (strstr($route, $uri)) {
                return $acl;
            }
        }
    }

    /**
     * Check whether user is allowed to access route
     *
     * @param array $data
     * @return bool
     */
    public function routeAllowed($data)
    {
        if ($this->isAdmin) {
            return true;
        }
        if ($data['route'] && $data['acl']) {
            //return self::isFlagSet($this->getUserAcl($data['route']), $data['acl']);
        }
        return true;
    }

    /**
     * Convert ACL flags
     *
     * @return int
     */
    public static function convertAclFlags($aclFlags)
    {
        $flag = 0;
        foreach ($aclFlags as $flag) {
            switch (strtolower($flag)) {
                case "add":
                    $f = self::ADD;
                    break;
                case "delete":
                    $f = self::DELETE;
                    break;
                case "update":
                    $f = self::UPDATE;
                    break;
                case "view":
                    $f = self::VIEW;
                    break;
            }
            $flag = $flag | $f;
        }
        return $flag;
    }
}
