<?php
/**
 * Copyright 2005-2017 Centreon
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
require_once dirname(__FILE__) . "/centreon_realtime_base.class.php";

/**
 * Class Centreon Realtime Host
 *
 */
class CentreonRealtimeHosts extends CentreonRealtimeBase
{
    /**
     * @var CentreonDB
     */
    protected $aclObj;
    protected $admin;

    /* parameters */
    protected $limit;
    protected $number;
    protected $status;
    protected $hostgroup;
    protected $search;
    protected $searchHost;
    protected $viewType;
    protected $sortType;
    protected $order;
    protected $instance;
    protected $criticality;

    protected $fieldList;

    /**
     * CentreonConfigurationService constructor.
     */
    public function __construct()
    {
        global $centreon;

        parent::__construct();

        // Init ACL
        if (!$centreon->user->admin) {
            $this->admin = 0;
            $this->aclObj = new CentreonACL($centreon->user->user_id, $centreon->user->admin);
        } else {
            $this->admin = 1;
        }
    }

    /**
     * Set a list of filters send by the request
     * @throws RestBadRequestException
     */
    protected function setHostFilters()
    {
        /* Pagination Elements */
        if (isset($this->arguments['limit'])) {
            $this->limit = $this->arguments['limit'];
        } else {
            $this->limit = 30;
        }
        if (isset($this->arguments['number'])) {
            $this->number = $this->arguments['number'];
        } else {
            $this->number = 0;
        }
        if (!is_numeric($this->number) || !is_numeric($this->limit)) {
            throw new \RestBadRequestException('Error, limit must be numerical');
        }

        /* Filters */
        if (isset($this->arguments['status'])) {
            $statusList = array('up', 'down', 'unreachable', 'pending', 'all');
            if (in_array(strtolower($this->arguments['status']), $statusList)) {
                $this->status = $this->arguments['status'];
            } else {
                throw new \RestBadRequestException('Bad status parameter');
            }
        } else {
            $this->status = null;
        }
        if (isset($this->arguments['hostgroup'])) {
            $this->hostgroup = $this->arguments['hostgroup'];
        } else {
            $this->hostgroup = null;
        }
        if (isset($this->arguments['search'])) {
            $this->search = $this->arguments['search'];
        } else {
            $this->search = null;
        }
        if (isset($this->arguments['instance'])) {
            $this->instance = $this->arguments['instance'];
        } else {
            $this->instance = null;
        }
        if (isset($this->arguments['criticality'])) {
            $this->criticality = $this->arguments['criticality'];
        } else {
            $this->criticality = null;
        }

        /* view properties */
        if (isset($this->arguments['viewType'])) {
            $this->viewType = $this->arguments['viewType'];
        } else {
            $this->viewType = null;
        }
        if (isset($this->arguments['order'])) {
            if (strtolower($this->arguments['order']) === 'asc' ||
                strtolower($this->arguments['order']) === 'desc') {
                $this->order = $this->arguments['order'];
            } else {
                throw new \RestBadRequestException('Bad order parameter');
            }
        } else {
            $this->order = null;
        }
        if (isset($this->arguments['sortType'])) {
            $this->sortType = $this->arguments['sortType'];
        } else {
            $this->sortType = null;
        }
    }

    /**
     * @return array
     */
    public function getList()
    {
        $this->setHostFilters();
        $this->setHostFieldList();
        return $this->getHostState();
    }

    /**
     * Get selected fields by the request
     *
     * @return array
     */
    protected function getFieldContent()
    {
        $tab = explode(',', $this->arguments['fields']);

        $fieldList = array();
        foreach ($tab as $key) {
            $fieldList[trim($key)] = 1;
        }
        return ($fieldList);
    }

    /**
     * Set Filters
     *
     */
    protected function setHostFieldList()
    {
        $fields = array();
        if (!isset($this->arguments['fields'])) {
            $fields["h.host_id as id"] = 'host_id';
            $fields["h.name"] = 'name';
            $fields["h.alias"] = 'alias';
            $fields["h.address"] = 'address';
            $fields["h.state"] = 'state';
            $fields["h.state_type"] = 'state_type';
            $fields["h.output"] = 'output';
            $fields["h.max_check_attempts"] = 'max_check_attempts';
            $fields["h.check_attempt"] = 'check_attempt';
            $fields["h.last_check"] = 'last_check';
            $fields["h.last_state_change"] = 'last_state_change';
            $fields["h.last_hard_state_change"] = 'last_hard_state_change';
            $fields["h.acknowledged"] = 'acknowledged';
            $fields["i.name as instance_name"] = 'instance';
            $fields["cv.value as criticality"] = 'criticality';
        } else {
            $fieldList = $this->getFieldContent();

            if (isset($fieldList['id'])) {
                $fields["h.host_id as id"] = 'host_id';
            }
            if (isset($fieldList['name'])) {
                $fields["h.name"] = 'name';
            }
            if (isset($fieldList['alias'])) {
                $fields["h.alias"] = 'alias';
            }
            if (isset($fieldList['address'])) {
                $fields["h.address"] = 'address';
            }
            if (isset($fieldList['state'])) {
                $fields["h.state"] = 'state';
            }
            if (isset($fieldList['state_type'])) {
                $fields["h.state_type"] = 'state_type';
            }
            if (isset($fieldList['output'])) {
                $fields["h.output"] = 'output';
            }
            if (isset($fieldList['max_check_attempts'])) {
                $fields["h.max_check_attempts"] = 'max_check_attempts';
            }
            if (isset($fieldList['check_attempt'])) {
                $fields["h.check_attempt"] = 'check_attempt';
            }
            if (isset($fieldList['last_check'])) {
                $fields["h.last_check"] = 'last_check';
            }
            if (isset($fieldList['next_check'])) {
                $fields["h.next_check"] = 'next_check';
            }
            if (isset($fieldList['last_state_change'])) {
                $fields["h.last_state_change"] = 'last_state_change';
            }
            if (isset($fieldList['last_hard_state_change'])) {
                $fields["h.last_hard_state_change"] = 'last_hard_state_change';
            }
            if (isset($fieldList['acknowledged'])) {
                $fields["h.acknowledged"] = 'acknowledged';
            }
            if (isset($fieldList['instance'])) {
                $fields["i.name as instance_name"] = 'instance';
            }
            if (isset($fieldList['instance_id'])) {
                $fields["i.instance_id as instance_id"] = 'instance_id';
            }
            if (isset($fieldList['criticality'])) {
                $fields["cv.value as criticality"] = 'criticality';
            }
            if (isset($fieldList['passive_checks'])) {
                $fields["h.passive_checks"] = 'passive_checks';
            }
            if (isset($fieldList['active_checks'])) {
                $fields["h.active_checks"] = 'active_checks';
            }
            if (isset($fieldList['notify'])) {
                $fields["h.notify"] = 'notify';
            }
            if (isset($fieldList['action_url'])) {
                $fields["h.action_url"] = 'action_url';
            }
            if (isset($fieldList['notes_url'])) {
                $fields["h.notes_url"] = 'notes_url';
            }
            if (isset($fieldList['notes'])) {
                $fields["h.notes"] = 'notes';
            }
            if (isset($fieldList['icon_image'])) {
                $fields["h.icon_image"] = 'icon_image';
            }
            if (isset($fieldList['icon_image_alt'])) {
                $fields["h.icon_image_alt"] = 'icon_image_alt';
            }
            if (isset($fieldList['scheduled_downtime_depth'])) {
                $fields["h.scheduled_downtime_depth"] = 'scheduled_downtime_depth';
            }
            if (isset($fieldList['flapping'])) {
                $fields["h.flapping"] = 'flapping';
            }
        }

        /* Build Field List */
        $this->fieldList = "";
        foreach ($fields as $key => $value) {
            if ($this->fieldList != '') {
                $this->fieldList .= ', ';
            }
            $this->fieldList .= $key;
        }
    }


    /**
     * @return array
     * @throws RestBadRequestException
     */
    public function getHostState()
    {
        $queryValues = array();

        /*
         * Get Host status
         */
        $query = " SELECT SQL_CALC_FOUND_ROWS DISTINCT " . $this->fieldList . " ";
        $query .= " FROM instances i, ";
        if (!$this->admin) {
            $query .= " centreon_acl, ";
        }
        if ($this->hostgroup) {
            $query .= " hosts_hostgroups hhg, hostgroups hg, ";
        }
        if ($this->criticality) {
            $query .= "customvariables cvs, ";
        }
        $query .= " `hosts` h ";
        $query .= " LEFT JOIN hosts_hosts_parents hph ";
        $query .= " ON hph.parent_id = h.host_id ";

        $query .= " LEFT JOIN `customvariables` cv ";
        $query .= " ON (cv.host_id = h.host_id ";
        $query .= " AND cv.service_id IS NULL ";
        $query .= " AND cv.name = 'CRITICALITY_LEVEL') ";

        $query .= " WHERE h.name NOT LIKE '_Module_%'";
        $query .= " AND h.instance_id = i.instance_id ";

        if ($this->criticality) {
            $query .= " AND h.host_id = cvs.host_id ";
            $query .= " AND cvs.name = 'CRITICALITY_LEVEL' ";
            $query .= " AND cvs.service_id IS NULL ";
            $query .= " AND cvs.value = :criticality ";
            $queryValues['criticality'] = (string)$this->criticality;
        }

        if (!$this->admin) {
            $query .= " AND h.host_id = centreon_acl.host_id ";
            $query .= $this->aclObj->queryBuilder(
                "AND",
                "centreon_acl.group_id",
                $this->aclObj->getAccessGroupsString()
            );
        }

        $query .= " AND (h.name LIKE :searchName ";
        $queryValues['searchName'] = '%' . (string)$this->search . '%';
        $query .= " OR h.alias LIKE :searchAlias ";
        $queryValues['searchAlias'] = '%' . (string)$this->search . '%';
        $query .= " OR h.address LIKE :searchAddress ) ";
        $queryValues['searchAddress'] = '%' . (string)$this->search . '%';

        if ($this->viewType == "unhandled") {
            $query .= " AND h.state = 1 ";
            $query .= " AND h.state_type = '1'";
            $query .= " AND h.acknowledged = 0";
            $query .= " AND h.scheduled_downtime_depth = 0";
        } elseif ($this->viewType == "problems") {
            $query .= " AND (h.state <> 0 AND h.state <> 4) ";
        }

        if ($this->status == "up") {
            $query .= " AND h.state = 0 ";
        } elseif ($this->status == "down") {
            $query .= " AND h.state = 1 ";
        } elseif ($this->status == "unreachable") {
            $query .= " AND h.state = 2 ";
        } elseif ($this->status == "pending") {
            $query .= " AND h.state = 4 ";
        }

        if ($this->hostgroup) {
            $explodedValues = '';
            foreach (explode(',', $this->hostgroup) as $hgId => $hgValue) {
                if (!is_numeric($hgValue)) {
                    throw new \RestBadRequestException('Error, host group id must be numerical');
                }
                $explodedValues .= ':hostgroup' . $hgId . ',';
                $queryValues['hostgroup'][$hgId] = (int)$hgValue;
            }
            $explodedValues = rtrim($explodedValues, ',');
            $query .= " AND h.host_id = hhg.host_id ";
            $query .= " AND hg.hostgroup_id IN ($explodedValues) ";
            $query .= " AND hhg.hostgroup_id = hg.hostgroup_id";
        }

        if ($this->instance != -1 && !empty($this->instance)) {
            if (!is_numeric($this->instance)) {
                throw new \RestBadRequestException('Error, instance id must be numerical');
            }
            $query .= " AND h.instance_id = :instanceId ";
            $queryValues['instanceId'] = (int)$this->instance;
        }
        $query .= " AND h.enabled = 1 ";

        if (!isset($this->arguments['fields']) ||
            is_null($this->arguments['fields']) ||
            in_array($this->sortType, explode(',', $this->arguments['fields']))
        ) {
            $q = 'ASC';
            if (isset($this->order) && strtoupper($this->order) === 'DESC') {
                $q = 'DESC';
            }

            switch ($this->sortType) {
                case 'id':
                    $query .= "ORDER BY h.host_id $q, h.name";
                    break;
                case 'alias':
                    $query .= "ORDER BY h.alias $q, h.name";
                    break;
                case 'address':
                    $query .= " ORDER BY IFNULL(inet_aton(h.address), h.address) $q, h.name ";
                    break;
                case 'state':
                    $query .= " ORDER BY h.state $q, h.name ";
                    break;
                case 'last_state_change':
                    $query .= " ORDER BY h.last_state_change $q, h.name ";
                    break;
                case 'last_hard_state_change':
                    $query .= " ORDER BY h.last_hard_state_change $q, h.name ";
                    break;
                case 'acknowledged':
                    $query .= "ORDER BY h.acknowledged $q, h.name";
                    break;
                case 'last_check':
                    $query .= " ORDER BY h.last_check $q, h.name ";
                    break;
                case 'check_attempt':
                    $query .= " ORDER BY h.check_attempt $q, h.name ";
                    break;
                case 'max_check_attempts':
                    $query .= "ORDER BY h.max_check_attempts $q, h.name";
                    break;
                case 'instance_name':
                    $query .= "ORDER BY i.name $q, h.name";
                    break;
                case 'output':
                    $query .= " ORDER BY h.output $q, h.name ";
                    break;
                case 'criticality':
                    $query .= " ORDER BY criticality $q, h.name ";
                    break;
                case 'name':
                default:
                    $query .= " ORDER BY h.name $q";
                    break;
            }
        }

        $query .= " LIMIT :offset,:limit";
        $queryValues['offset'] = (int)($this->number * $this->limit);
        $queryValues['limit'] = (int)$this->limit;
        $stmt = $this->realTimeDb->prepare($query);

        if ($this->criticality) {
            $stmt->bindParam(':criticality', $queryValues["criticality"], PDO::PARAM_STR);
        }
        $stmt->bindParam(':searchName', $queryValues["searchName"], PDO::PARAM_STR);
        $stmt->bindParam(':searchAlias', $queryValues["searchAlias"], PDO::PARAM_STR);
        $stmt->bindParam(':searchAddress', $queryValues["searchAddress"], PDO::PARAM_STR);
        if (isset($queryValues['hostgroup'])) {
            foreach ($queryValues['hostgroup'] as $hgId => $hgValue) {
                $stmt->bindValue(':hostgroup' . $hgId, $hgValue, PDO::PARAM_INT);
            }
        }
        if (isset($queryValues['instanceId'])) {
            $stmt->bindParam(':instanceId', $queryValues['instanceId'], PDO::PARAM_INT);
        }

        $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
        $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        $stmt->execute();

        $dataList = array();
        while ($data = $stmt->fetch()) {
            $dataList[] = $data;
        }
        return $dataList;
    }
}
