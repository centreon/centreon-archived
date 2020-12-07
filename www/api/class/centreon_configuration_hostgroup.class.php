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

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once __DIR__ . "/centreon_configuration_objects.class.php";

class CentreonConfigurationHostgroup extends CentreonConfigurationObjects
{

    /**
     * @var CentreonDB
     */
    protected $pearDBMonitoring;

    /**
     * CentreonConfigurationHostgroup constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pearDBMonitoring = new CentreonDB('centstorage');
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getList()
    {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $aclHostGroups = '';
        $queryValues = array();

        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclHostGroups .= 'AND hg.hg_id IN (' . $acl->getHostGroupsString('ID') . ') ';
        }

        // Check for select2 'q' argument
        if (isset($this->arguments['q'])) {
            $queryValues['hgName'] = '%' . (string)$this->arguments['q'] . '%';
        } else {
            $queryValues['hgName'] = '%%';
        }

        $queryHostGroup = "SELECT SQL_CALC_FOUND_ROWS DISTINCT "
            . "hg.hg_name, hg.hg_id, hg.hg_activate FROM hostgroup hg "
            . "WHERE hg.hg_name LIKE :hgName " . $aclHostGroups . "ORDER BY hg.hg_name ";

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            if (
                !is_numeric($this->arguments['page'])
                || !is_numeric($this->arguments['page_limit'])
                || $this->arguments['page_limit'] < 1
            ) {
                throw new \RestBadRequestException('Error, limit must be an integer greater than zero');
            }
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $queryHostGroup .= 'LIMIT :offset, :limit';
            $queryValues['offset'] = (int)$offset;
            $queryValues['limit'] = (int)$this->arguments['page_limit'];
        }
        $stmt = $this->pearDB->prepare($queryHostGroup);
        $stmt->bindParam(':hgName', $queryValues["hgName"], PDO::PARAM_STR);
        if (isset($queryValues['offset'])) {
            $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        }
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $hostGroupList = array();
        while ($data = $stmt->fetch()) {
            $hostGroupList[] = [
                'id' => htmlentities($data['hg_id']),
                'text' => $data['hg_name'],
                'status' => (bool) $data['hg_activate'],
            ];
        }

        return array(
            'items' => $hostGroupList,
            'total' => (int) $this->pearDB->numberRows()
        );
    }

    /**
     * @return array
     * @throws RestBadRequestException
     */
    public function getHostList()
    {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $aclHostGroups = '';
        $aclHosts = '';
        $queryValues = array();
        $hgIdList = '';

        /* Get ACL if user is not admin */

        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclHostGroups .= ' AND hg.hg_id IN (' . $acl->getHostGroupsString('ID') . ') ';
            $aclHosts .= ' AND h.host_id IN (' . $acl->getHostsString('ID', $this->pearDBMonitoring) . ') ';
        }

        // Check for select2 'q' argument
        if (isset($this->arguments['hgid'])) {
            $listId = explode(',', $this->arguments['hgid']);
            foreach ($listId as $key => $idHg) {
                if (!is_numeric($idHg)) {
                    throw new \RestBadRequestException('Error, host group id must be numerical');
                }
                $hgIdList .= ':hgid' . $idHg . ',';
                $queryValues['hgid'][$idHg] = (int)$idHg;
            }
            $hgIdList = rtrim($hgIdList, ',');
        } else {
            $queryValues['hgid'][0] = '""';
            $hgIdList .= ':hgid0';
        }

        $queryHostGroup = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT h.host_name , h.host_id FROM hostgroup hg ' .
            'INNER JOIN hostgroup_relation hgr ON hg.hg_id = hgr.hostgroup_hg_id ' .
            'INNER JOIN host h ON  h.host_id = hgr.host_host_id ' .
            'WHERE hg.hg_id IN (' . $hgIdList . ') ' .
            $aclHostGroups .
            $aclHosts;

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            if (
                !is_numeric($this->arguments['page'])
                || !is_numeric($this->arguments['page_limit'])
                || $this->arguments['page_limit'] < 1
            ) {
                throw new \RestBadRequestException('Error, limit must be an integer greater than zero');
            }
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $queryHostGroup .= 'LIMIT :offset, :limit';
            $queryValues['offset'] = (int)$offset;
            $queryValues['limit'] = (int)$this->arguments['page_limit'];
        }
        $stmt = $this->pearDB->prepare($queryHostGroup);

        foreach ($queryValues["hgid"] as $k => $v) {
            $stmt->bindValue(':hgid' . $k, $v, PDO::PARAM_INT);
        }
        if (isset($queryValues['offset'])) {
            $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        }
        $stmt->execute();
        $hostList = array();
        while ($data = $stmt->fetch()) {
            $hostList[] = array(
                'id' => htmlentities($data['host_id']),
                'text' => $data['host_name']
            );
        }

        return array(
            'items' => $hostList,
            'total' => (int) $this->pearDB->numberRows()
        );
    }
}
