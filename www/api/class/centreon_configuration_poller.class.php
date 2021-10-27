<?php

/*
 * Copyright 2005-2020 Centreon
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
 * this program; if not, see <htcommand://www.gnu.org/licenses>.
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
 * For more information : command@centreon.com
 *
 */

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once __DIR__ . "/centreon_configuration_objects.class.php";

class CentreonConfigurationPoller extends CentreonConfigurationObjects
{
    /**
     * @var CentreonDB
     */
    protected $pearDB;

    /**
     * CentreonConfigurationPoller constructor.
     */
    public function __construct()
    {
        $this->pearDB = new CentreonDB('centreon');
        parent::__construct();
    }

    /**
     * @return array
     * @throws RestBadRequestException
     */
    public function getList()
    {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $queryValues = array();

        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
        }

        // Check for select2 'q' argument
        if (isset($this->arguments['q'])) {
            $queryValues['name'] = '%' . (string)$this->arguments['q'] . '%';
        } else {
            $queryValues['name'] = '%%';
        }

        $queryPoller = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ns.id, ns.name FROM nagios_server ns ';

        if (isset($this->arguments['t'])) {
            if ($this->arguments['t'] == 'remote') {
                $queryPoller .= "JOIN remote_servers rs ON (ns.id = rs.server_id) ";
                // Exclude selected master Remote Server
                if (isset($this->arguments['e'])) {
                    $queryPoller .= 'WHERE ns.id <> :masterId ';
                    $queryValues['masterId'] = (int)$this->arguments['e'];
                }
            } elseif ($this->arguments['t'] == 'poller') {
                $queryPoller .= "LEFT JOIN remote_servers rs ON (ns.id = rs.server_id) "
                    . "WHERE rs.ip IS NULL "
                    . "AND ns.localhost = '0' ";
            } elseif ($this->arguments['t'] == 'central') {
                $queryPoller .= "WHERE ns.localhost = '0' ";
            }
        } else {
            $queryPoller .= '';
        }

        if (stripos($queryPoller, 'WHERE') === false) {
            $queryPoller .= 'WHERE ns.name LIKE :name ';
        } else {
            $queryPoller .= 'AND ns.name LIKE :name ';
        }
        $queryPoller .= 'AND ns.ns_activate = "1" ';

        if (!$isAdmin) {
            $queryPoller .= $acl->queryBuilder('AND', 'id', $acl->getPollerString('ID', $this->pearDB));
        }
        $queryPoller .= 'ORDER BY name ';
        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            if (
                !is_numeric($this->arguments['page'])
                || !is_numeric($this->arguments['page_limit'])
                || $this->arguments['page_limit'] < 1
            ) {
                throw new \RestBadRequestException('Error, limit must be an integer greater than zero');
            }
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $queryPoller .= 'LIMIT :offset, :limit';
            $queryValues['offset'] = (int)$offset;
            $queryValues['limit'] = (int)$this->arguments['page_limit'];
        }

        $stmt = $this->pearDB->prepare($queryPoller);
        $stmt->bindParam(':name', $queryValues['name'], PDO::PARAM_STR);
        // bind exluded master Remote Server
        if (isset($this->arguments['t'])
            && $this->arguments['t'] == 'remote'
            && isset($this->arguments['e'])
        ) {
            $stmt->bindParam(':masterId', $queryValues['masterId'], PDO::PARAM_STR);
        }
        if (isset($queryValues['offset'])) {
            $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        }
        $stmt->execute();
        $pollerList = array();
        while ($data = $stmt->fetch()) {
            $pollerList[] = array(
                'id' => $data['id'],
                'text' => $data['name']
            );
        }
        return array(
            'items' => $pollerList,
            'total' => (int) $this->pearDB->numberRows()
        );
    }
}
