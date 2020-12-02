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
 * this program; if not, see <htcontact://www.gnu.org/licenses>.
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

class CentreonConfigurationServicegroup extends CentreonConfigurationObjects
{
    /**
     * @var CentreonDB
     */
    protected $pearDBMonitoring;

    /**
     * CentreonConfigurationServicegroup constructor.
     */
    public function __construct()
    {
        $this->pearDBMonitoring = new CentreonDB('centstorage');
        parent::__construct();
    }

    /**
     * @return array
     * @throws RestBadRequestException
     */
    public function getList()
    {
        global $centreon;
        $isAdmin = $centreon->user->admin;
        $userId = $centreon->user->user_id;
        $queryValues = array();

        // Check for select2 'q' argument
        if (isset($this->arguments['q'])) {
            $queryValues['name'] = '%' . (string)$this->arguments['q'] . '%';
        } else {
            $queryValues['name'] = '%%';
        }
        $aclServicegroups = "";
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclServicegroups .= ' AND sg_id IN (' . $acl->getServiceGroupsString('ID') . ') ';
        }
        $queryContact = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT sg_id, sg_name, sg_activate FROM servicegroup ' .
            'WHERE sg_name LIKE :name ' .
            $aclServicegroups .
            'ORDER BY sg_name ';

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            if (
                !is_numeric($this->arguments['page'])
                || !is_numeric($this->arguments['page_limit'])
                || $this->arguments['page_limit'] < 1
            ) {
                throw new \RestBadRequestException('Error, limit must be an integer greater than zero');
            }
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $queryContact .= 'LIMIT :offset,:limit';
            $queryValues['offset'] = (int)$offset;
            $queryValues['limit'] = (int)$this->arguments['page_limit'];
        }

        $stmt = $this->pearDB->prepare($queryContact);
        $stmt->bindParam(':name', $queryValues['name'], PDO::PARAM_STR);
        if (isset($queryValues['offset'])) {
            $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        }
        $stmt->execute();
        $serviceList = array();
        while ($data = $stmt->fetch()) {
            $serviceList[] = [
                'id' => htmlentities($data['sg_id']),
                'text' => $data['sg_name'],
                'status' => (bool) $data['sg_activate'],
            ];
        }
        return array(
            'items' => $serviceList,
            'total' => (int) $this->pearDB->numberRows()
        );
    }

    /**
     * @return array
     * @throws RestBadRequestException
     */
    public function getServiceList()
    {
        global $centreon;
        // Check for select2 'q' argument
        $queryValues = array();
        $sgIdList = '';

        // Check for select2 'q' argument
        if (isset($this->arguments['sgid'])) {
            $listId = explode(',', $this->arguments['sgid']);
            foreach ($listId as $key => $idSg) {
                if (!is_numeric($idSg)) {
                    throw new \RestBadRequestException('Error, service group id must be numerical');
                }
                $sgIdList .= ':sgid' . $idSg . ',';
                $queryValues['sgid'][$idSg] = (int)$idSg;
            }
            $sgIdList = rtrim($sgIdList, ',');
        } else {
            $queryValues['sgid'][0] = '""';
            $sgIdList .= ':sgid0';
        }
        $isAdmin = $centreon->user->admin;
        $userId = $centreon->user->user_id;
        $aclServicegroups = "";
        $aclServices = "";

        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclServicegroups .= ' AND sg.sg_id IN (' . $acl->getServiceGroupsString('ID') . ') ';
            $aclServices .= ' AND s.service_id IN (' . $acl->getServicesString('ID', $this->pearDBMonitoring) . ') ';
        }

        $queryContact = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT s.service_id, s.service_description, h.host_name, ' .
            'h.host_id ' .
            'FROM servicegroup sg ' .
            'INNER JOIN servicegroup_relation sgr ON sgr.servicegroup_sg_id = sg.sg_id ' .
            'INNER JOIN service s ON s.service_id = sgr.service_service_id ' .
            'INNER JOIN host_service_relation hsr ON hsr.service_service_id = s.service_id ' .
            'INNER JOIN host h ON h.host_id = hsr.host_host_id ' .
            'WHERE sg.sg_id IN (' . $sgIdList . ') ' .
            $aclServicegroups . $aclServices;

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            if (
                !is_numeric($this->arguments['page'])
                || !is_numeric($this->arguments['page_limit'])
                || $this->arguments['page_limit'] < 1
            ) {
                throw new \RestBadRequestException('Error, limit must be an integer greater than zero');
            }
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $queryContact .= 'LIMIT :offset, :limit';
            $queryValues['offset'] = (int)$offset;
            $queryValues['limit'] = (int)$this->arguments['page_limit'];
        }
        $stmt = $this->pearDB->prepare($queryContact);
        foreach ($queryValues["sgid"] as $k => $v) {
            $stmt->bindValue(':sgid' . $k, $v, PDO::PARAM_INT);
        }
        if (isset($queryValues['offset'])) {
            $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        }
        $stmt->execute();
        $serviceList = array();
        while ($data = $stmt->fetch()) {
            $serviceList[] = array(
                'id' => $data['host_id'] . '_' . $data['service_id'],
                'text' => $data['host_name'] . ' - ' . $data['service_description']
            );
        }
        return array(
            'items' => $serviceList,
            'total' => (int) $this->pearDB->numberRows()
        );
    }
}
