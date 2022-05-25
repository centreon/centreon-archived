<?php

/**
 * Copyright 2005-2022 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once __DIR__ . "/../List.class.php";

class CentreonWidgetParamsConnectorPoller extends CentreonWidgetParamsList
{
    public function __construct($db, $quickform, $userId)
    {
        parent::__construct($db, $quickform, $userId);
    }

    public function getListValues($paramId)
    {
        static $tab;

        if (! isset($tab)) {
            $userACL = new CentreonACL($this->userId);
            $isContactAdmin = $userACL->admin;
            $request = 'SELECT SQL_CALC_FOUND_ROWS id, name FROM nagios_server ns';

            if (! $isContactAdmin) {
                $request .= ' INNER JOIN acl_resources_poller_relations arpr
                ON ns.id = arpr.poller_id
                INNER JOIN acl_resources res
                    ON arpr.acl_res_id = res.acl_res_id
                INNER JOIN acl_res_group_relations argr
                    ON res.acl_res_id = argr.acl_res_id
                INNER JOIN acl_groups ag
                    ON argr.acl_group_id = ag.acl_group_id
                LEFT JOIN acl_group_contacts_relations agcr
                    ON ag.acl_group_id = agcr.acl_group_id
                LEFT JOIN acl_group_contactgroups_relations agcgr
                    ON ag.acl_group_id = agcgr.acl_group_id
                LEFT JOIN contactgroup_contact_relation cgcr
                    ON cgcr.contactgroup_cg_id = agcgr.cg_cg_id
                WHERE (agcr.contact_contact_id = :userId OR cgcr.contact_contact_id = :userId)';
            }

            $request .= ! $isContactAdmin ? ' AND' : ' WHERE';
            $request .= " ns_activate = '1' ORDER BY name";
            $statement = $this->db->prepare($request);

            if (! $isContactAdmin) {
                $statement->bindValue(':userId', $this->userId, \PDO::PARAM_INT);
            }
            $statement->execute();
            $entriesCount = $this->db->query('SELECT FOUND_ROWS()');

            if ($entriesCount !== false && ($total = $entriesCount->fetchColumn()) !== false) {
                // it means here that there is poller relations with this user
                if ((int) $total > 0) {
                    while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                        $tab[$record['id']] = $record['name'];
                    }
                } else {
                    // if no relations found for this user it means that he can see all poller available
                    $statement = $this->db->prepare(
                        "SELECT id, name FROM nagios_server WHERE ns_activate = '1'"
                    );
                    $statement->execute();

                    while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                        $tab[$record['id']] = $record['name'];
                    }
                }
            }
        }

        return $tab;
    }
}
