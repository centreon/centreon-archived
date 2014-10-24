<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
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
            return self::isFlagSet($this->getUserAcl($data['route']), $data['acl']);
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
