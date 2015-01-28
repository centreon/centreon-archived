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

use Centreon\Internal\Di;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class AclactionRepository
{
    /**
     * Update acl action rules
     *
     * @param int $aclActionId
     * @param array $ruleParams
     */
    public static function updateRules($aclActionId, $ruleParams)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = "DELETE FROM cfg_acl_actions_rules WHERE acl_action_rule_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($aclActionId));
        $sql = "INSERT INTO cfg_acl_actions_rules (acl_action_rule_id, acl_action_name) VALUES (?, ?)";
        $db->beginTransaction();
        $stmt = $db->prepare($sql);
        foreach ($ruleParams as $key => $value) {
            if ((preg_match('/^service_/', $key) || preg_match('/^host_/', $key)) && $value == 1) {
                $stmt->execute(array($aclActionId, $key));
            }
        }
        $db->commit();
    }

    /** 
     * Get rules from action id
     *
     * @param int $actionId
     */
    public static function getRulesFromActionId($actionId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("SELECT acl_action_name FROM cfg_acl_actions_rules WHERE acl_action_rule_id = ?");
        $stmt->execute(array($actionId));
        $arr = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $arr[$row['acl_action_name']] = 1;
        }
        return $arr;
    }
}
