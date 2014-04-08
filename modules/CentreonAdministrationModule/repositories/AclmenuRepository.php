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
 */

namespace CentreonAdministration\Repository;

/**
 * @author Sylvestre Ho <sho@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class AclmenuRepository
{
    /**
     * Get ACL level by Acl Menu ID
     *
     * @param int $acl_menu_id
     * @return array
     */
    public static function getAclLevelByAclMenuId($acl_menu_id)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $sql = "SELECT menu_id, acl_level
            FROM acl_menu_menu_relations
            WHERE acl_menu_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($acl_menu_id));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $data = array();
        foreach ($rows as $row) {
            $data[$row['menu_id']] = $row['acl_level'];
        }
        return $data;
    }

    /**
     * Update Acl data
     *
     * @param int $acl_menu_id
     * @param array $menus
     */
    public static function updateAclLevel($acl_menu_id, $menus)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("DELETE FROM acl_menu_menu_relations WHERE acl_menu_id = ?");
        $stmt->execute(array($acl_menu_id));
        $sql = "INSERT INTO acl_menu_menu_relations (acl_menu_id, menu_id, acl_level) VALUES (?, ?, ?)";
        $db->beginTransaction();
        $stmt = $db->prepare($sql);
        foreach ($menus as $menuId => $aclLevel) {
            $stmt->execute(array($acl_menu_id, $menuId, $aclLevel));
        }
        $db->commit();
    }
}
