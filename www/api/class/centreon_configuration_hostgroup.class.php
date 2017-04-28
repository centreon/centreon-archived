<?php
/*
 * Copyright 2005-2015 Centreon
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
require_once dirname(__FILE__) . "/centreon_configuration_objects.class.php";

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
     */
    public function getList()
    {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $aclHostgroups = '';
        $queryValues = array();

        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclHostgroups .= 'AND hg.hg_id IN (' . $acl->getHostGroupsString('ID') . ') ';
        }

        // Check for select2 'q' argument
        if (false === isset($this->arguments['q'])) {
            $q = '';
        } else {
            $q = $this->arguments['q'];
        }
        $queryValues[] = (string)'%' . $q . '%';

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $range = 'LIMIT ?,?';
            $queryValues[] = (int)$offset;
            $queryValues[] = (int)$this->arguments['page_limit'];
        } else {
            $range = '';
        }

        $queryHostgroup = "SELECT SQL_CALC_FOUND_ROWS DISTINCT hg.hg_name, hg.hg_id "
            . "FROM hostgroup hg "
            . "WHERE hg.hg_name LIKE ? "
            . $aclHostgroups
            . "ORDER BY hg.hg_name "
            . $range;

        $stmt = $this->pearDB->prepare($queryHostgroup);
        $dbResult = $this->pearDB->execute($stmt, $queryValues);
        $total = $this->pearDB->numberRows();

        $hostgroupList = array();
        while ($data = $dbResult->fetchRow()) {
            $hostgroupList[] = array('id' => htmlentities($data['hg_id']), 'text' => $data['hg_name']);
        }

        return array(
            'items' => $hostgroupList,
            'total' => $total
        );
    }

    public function getHostList()
    {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $aclHostgroups = '';
        $aclHosts = '';
        $queryValues = array();

        /* Get ACL if user is not admin */

        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclHostgroups .= ' AND hg.hg_id IN (' . $acl->getHostGroupsString('ID') . ') ';
            $aclHosts .= ' AND h.host_id IN (' . $acl->getHostsString('ID', $this->pearDBMonitoring) . ') ';
        }


        // Check for select2 'q' argument
        if (false === isset($this->arguments['hgid'])) {
            $hgid = '';
        } else {
            $hgid = $this->arguments['hgid'];
        }
        $queryValues[] = (string)$hgid;

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $range = 'LIMIT ?,?';
            $queryValues[] = (int)$offset;
            $queryValues[] = (int)$this->arguments['page_limit'];
        } else {
            $range = '';
        }

        $queryHostgroup = "SELECT SQL_CALC_FOUND_ROWS DISTINCT h.host_name , h.host_id "
            . "FROM hostgroup hg "
            . "INNER JOIN hostgroup_relation hgr ON hg.hg_id = hgr.hostgroup_hg_id "
            . "INNER JOIN host h ON  h.host_id = hgr.host_host_id "
            . "WHERE hg.hg_id IN (?) "
            . $aclHostgroups
            . $aclHosts
            . $range;

        $stmt = $this->pearDB->prepare($queryHostgroup);
        $DBRESULT = $this->pearDB->execute($stmt, $queryValues);


        $total = $this->pearDB->numberRows();

        $hostList = array();
        while ($data = $DBRESULT->fetchRow()) {
            $hostList[] = array(
                'id' => htmlentities($data['host_id']),
                'text' => $data['host_name']
            );
        }

        return array(
            'items' => $hostList,
            'total' => $total
        );
    }
}
