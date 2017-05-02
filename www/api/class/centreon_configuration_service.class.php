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

class CentreonConfigurationService extends CentreonConfigurationObjects
{
    /**
     * @var CentreonDB
     */
    protected $pearDBMonitoring;

    /**
     * CentreonConfigurationService constructor.
     */
    public function __construct()
    {
        global $pearDBO;
        parent::__construct();
        $this->pearDBMonitoring = new CentreonDB('centstorage');
        $pearDBO = $this->pearDBMonitoring;
    }

    /**
     * @return array
     */
    public function getList()
    {

        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $aclServices = '';
        $aclMetaServices = '';
        $range = array();

        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclServices .= 'AND s.service_id IN (' . $acl->getServicesString('ID', $this->pearDBMonitoring) . ') ';
            $aclMetaServices .= 'AND ms.service_id IN (' . $acl->getMetaServiceString('ID',
                    $this->pearDBMonitoring) . ') ';
        }

        // Check for select2 'q' argument
        if (false === isset($this->arguments['q'])) {
            $q = '';
        } else {
            $q = $this->arguments['q'];
        }

        // Check for service enable
        if (false === isset($this->arguments['e'])) {
            $e = '';
        } else {
            $e = $this->arguments['e'];
        }

        // Check for service type
        if (false === isset($this->arguments['t'])) {
            $t = 'host';
        } else {
            $t = $this->arguments['t'];
        }

        // Check for service type
        $g = false;
        if (isset($this->arguments['g'])) {
            $g = $this->arguments['g'];
            if ($g == '1') {
                $g = true;
            }
        }

        // Check for service type
        if (isset($this->arguments['s']) && ('s' === $this->arguments['s'])) {
            $s = $this->arguments['s'];
        } elseif (isset($this->arguments['s']) && ('m' === $this->arguments['s'])) {
            $s = $this->arguments['s'];
        } else {
            $s = 'all';
        }

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $range[] = (int)$offset;
            $range[] = (int)$this->arguments['page_limit'];
        }

        switch ($t) {
            default:
            case 'host':
                $serviceList = $this->getServicesByHost($q, $aclServices, $range, $g, $aclMetaServices, $s, $e);
                break;
            case 'hostgroup':
                $serviceList = $this->getServicesByHostgroup($q, $aclServices, $range);
                break;
        }
        return $serviceList;
    }

    /**
     * @param $q
     * @param $aclServices
     * @param array $range
     * @param bool $hasGraph
     * @param $aclMetaServices
     * @param $s
     * @param $e
     * @return array
     */
    private function getServicesByHost($q, $aclServices, $range = array(), $hasGraph = false, $aclMetaServices, $s, $e)
    {
        $queryValues = array();
        if ($e == 'enable'):
            $enableQuery = 'AND s.service_activate = \'1\' AND h.host_activate = \'1\' ';
            $enableQueryMeta = 'AND ms.service_activate = \'1\' AND mh.host_activate = \'1\' ';
        elseif ($e == 'disable'):
            $enableQuery = 'AND ( s.service_activate = \'0\' OR h.host_activate = \'0\' ) ';
            $enableQueryMeta = 'AND ( ms.service_activate = \'0\' OR mh.host_activate = \'0\') ';
        else:
            $enableQuery = '';
            $enableQueryMeta = '';
        endif;

        switch ($s) {
            case 'all':
                $queryService = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT fullname, service_id, host_id ' .
                    'FROM ( ' .
                    '( SELECT DISTINCT CONCAT(h.host_name, " - ", s.service_description) ' .
                    'as fullname, s.service_id, h.host_id ' .
                    'FROM host h, service s, host_service_relation hsr ' .
                    'WHERE hsr.host_host_id = h.host_id ' .
                    'AND hsr.service_service_id = s.service_id ' .
                    'AND h.host_register = "1" ' .
                    'AND s.service_register = "1" ' .
                    'AND CONCAT(h.host_name, " - ", s.service_description) LIKE ? ';
                $queryValues[] = (string)'%' . $q . '%';
                $queryService .= $enableQuery . $aclServices . ') ' .
                    'UNION ALL ( ' .
                    'SELECT DISTINCT CONCAT("Meta - ", ms.display_name) as fullname, ms.service_id, mh.host_id ' .
                    'FROM host mh, service ms ' .
                    'WHERE mh.host_name = "_Module_Meta" ' .
                    'AND mh.host_register = "2" ' .
                    'AND ms.service_register = "2" ' .
                    'AND CONCAT("Meta - ", ms.display_name) LIKE ? ';
                $queryValues[] = (string)'%' . $q . '%';
                $queryService .= $enableQueryMeta . $aclMetaServices . ') ' .
                    ')  as t_union ' .
                    'ORDER BY fullname ';

                if (isset($range)) {
                    $queryRange = 'LIMIT ?,?';
                    $queryValues[] = (int)$range[0];
                    $queryValues[] = (int)$range[1];
                } else {
                    $queryRange = '';
                }

                $queryService .= $queryRange;

                $stmt = $this->pearDB->prepare($queryService);
                $dbResult = $this->pearDB->execute($stmt, $queryValues);

                break;
            case 's':
                $queryService = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT CONCAT(h.host_name, " - ", ' .
                    's.service_description) as fullname, s.service_id, h.host_id ' .
                    'FROM host h, service s, host_service_relation hsr ' .
                    'WHERE hsr.host_host_id = h.host_id ' .
                    'AND hsr.service_service_id = s.service_id ' .
                    'AND h.host_register = "1" ' .
                    'AND s.service_register = "1" ' .
                    'AND CONCAT(h.host_name, " - ", s.service_description) LIKE ? ';
                $queryValues[] = (string)'%' . $q . '%';
                $queryService .= $enableQuery . $aclServices . 'ORDER BY fullname ';
                if (isset($range)) {
                    $queryRange = 'LIMIT ?,?';
                    $queryValues[] = (int)$range[0];
                    $queryValues[] = (int)$range[1];
                } else {
                    $queryRange = '';
                }
                $queryService .= $queryRange;
                $stmt = $this->pearDB->prepare($queryService);
                $dbResult = $this->pearDB->execute($stmt, $queryValues);
                break;
            case 'm':
                $queryService = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT CONCAT("Meta - ", ms.display_name) ' .
                    'as fullname, ms.service_id, mh.host_id ' .
                    'FROM host mh, service ms ' .
                    'WHERE mh.host_name = "_Module_Meta" ' .
                    'AND mh.host_register = "2" ' .
                    'AND ms.service_register = "2" ' .
                    'AND CONCAT("Meta - ", ms.display_name) LIKE ? ';
                $queryValues[] = (string)'%' . $q . '%';
                $queryService .= $enableQueryMeta . $aclMetaServices . 'ORDER BY fullname ';
                if (isset($range)) {
                    $queryRange = 'LIMIT ?,?';
                    $queryValues[] = (int)$range[0];
                    $queryValues[] = (int)$range[1];
                } else {
                    $queryRange = '';
                }
                $queryService .= $queryRange;
                $stmt = $this->pearDB->prepare($queryService);
                $dbResult = $this->pearDB->execute($stmt, $queryValues);

                break;
        }

        $total = $this->pearDB->numberRows();
        $serviceList = array();
        while ($data = $dbResult->fetchRow()) {
            if ($hasGraph) {
                if (service_has_graph($data['host_id'], $data['service_id'], $this->pearDBMonitoring)) {
                    $serviceCompleteName = $data['fullname'];
                    $serviceCompleteId = $data['host_id'] . '-' . $data['service_id'];
                    $serviceList[] = array('id' => htmlentities($serviceCompleteId), 'text' => $serviceCompleteName);
                }
            } else {
                $serviceCompleteName = $data['fullname'];
                $serviceCompleteId = $data['host_id'] . '-' . $data['service_id'];
                $serviceList[] = array('id' => htmlentities($serviceCompleteId), 'text' => $serviceCompleteName);
            }
        }

        return array(
            'items' => $serviceList,
            'total' => $total
        );
    }

    /**
     * @param $q
     * @param $aclServices
     * @param array $range
     * @return array
     */
    private function getServicesByHostgroup($q, $aclServices, $range = array())
    {
        $queryValues = array();
        $queryService = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT CONCAT(hg.hg_name, " - ", s.service_description) ' .
            'as fullname, s.service_id, hg.hg_id ' .
            'FROM hostgroup hg, service s, host_service_relation hsr ' .
            'WHERE hsr.hostgroup_hg_id = hg.hg_id ' .
            'AND hsr.service_service_id = s.service_id ' .
            'AND s.service_register = "1" ' .
            'AND CONCAT(hg.hg_name, " - ", s.service_description) LIKE ? ';
        $queryValues[] = (string)'%' . $q . '%';
        $queryService .= $aclServices . 'ORDER BY fullname ';

        if (isset($range)) {
            $queryRange = 'LIMIT ?,?';
            $queryValues[] = (int)$range[0];
            $queryValues[] = (int)$range[1];
        } else {
            $queryRange = '';
        }
        $queryService .= $queryRange;

        $stmt = $this->pearDB->prepare($queryService);
        $dbResult = $this->pearDB->execute($stmt, $queryValues);
        $total = $this->pearDB->numberRows();
        $serviceList = array();
        while ($data = $dbResult->fetchRow()) {
            $serviceCompleteName = $data['fullname'];
            $serviceCompleteId = $data['hg_id'] . '-' . $data['service_id'];
            $serviceList[] = array('id' => htmlentities($serviceCompleteId), 'text' => $serviceCompleteName);
        }

        return array(
            'items' => $serviceList,
            'total' => $total
        );
    }

    /**
     * @return array
     * @throws RestBadRequestException
     */
    public function getDefaultEscalationValues()
    {
        $defaultValues = array();
        // Get Object targeted
        if (isset($this->arguments['id']) && !empty($this->arguments['id'])) {
            $id = $this->arguments['id'];
        } else {
            throw new RestBadRequestException("Bad parameters id");
        }

        $queryService = 'SELECT distinct host_host_id, host_name, service_service_id, service_description ' .
            'FROM service s, escalation_service_relation esr, host h ' .
            'WHERE s.service_id = esr.service_service_id ' .
            'AND esr.host_host_id = h.host_id ' .
            'AND h.host_register = "1" ' .
            'AND esr.escalation_esc_id = ?';

        $stmt = $this->db->prepare($queryService);
        $dbResult = $this->db->execute($stmt, array((int)$id));

        while ($data = $dbResult->fetchRow()) {
            $serviceCompleteName = $data['host_name'] . ' - ' . $data['service_description'];
            $serviceCompleteId = $data['host_host_id'] . '-' . $data['service_service_id'];

            $defaultValues[] = array('id' => htmlentities($serviceCompleteId), 'text' => $serviceCompleteName);
        }
        return $defaultValues;
    }
}
