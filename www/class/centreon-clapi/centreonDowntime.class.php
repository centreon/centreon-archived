<?php
/*
 * Copyright 2005-2014 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonClapi;

require_once "centreonObject.class.php";
require_once "centreonHost.class.php";
require_once "centreonService.class.php";
require_once "Centreon/Object/Downtime/Downtime.php";
require_once "Centreon/Object/Host/Host.php";
require_once "Centreon/Object/Host/Group.php";
require_once "Centreon/Object/Service/Group.php";
require_once "Centreon/Object/Relation/Downtime/Host.php";
require_once "Centreon/Object/Relation/Downtime/Hostgroup.php";
require_once "Centreon/Object/Relation/Downtime/Servicegroup.php";

/**
 * Class for managing recurring downtime objects
 *
 * @author sylvestre
 */
class CentreonDowntime extends CentreonObject
{
    const ORDER_UNIQUENAME = 0;
    const ORDER_ALIAS = 1;
    
    /**
     *
     * @var array
     */
    protected $weekDays;
    
    /**
     *
     * @var type
     */
    protected $serviceObj;
    
    /**
     *
     * @var array
     */
    protected $availableCycles;

    public static $aDepends = array(
        'SERVICE',
        'HOST'
    );

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->serviceObj = new CentreonService();
        $this->object = new \Centreon_Object_Downtime();
        $this->action = "DOWNTIME";
        $this->insertParams = array('dt_name', 'dt_description');
        $this->exportExcludedParams = array_merge(
            $this->insertParams,
            array($this->object->getPrimaryKey())
        );
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->activateField = 'dt_activate';
        $this->weekDays = array(
            'monday'    => 1,
            'tuesday'   => 2,
            'wednesday' => 3,
            'thursday'  => 4,
            'friday'    => 5,
            'saturday'  => 6,
            'sunday'    => 7
        );
        
        $this->availableCycles = array(
            'first',
            'second',
            'third',
            'fourth',
            'last'
        );
    }

    /**
     * Display all Host Groups
     *
     * @param  string $parameters
     * @return void
     */
    public function show($parameters = null)
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%".$parameters."%");
        }
        $params = array(
            'dt_id',
            'dt_name',
            'dt_description',
            'dt_activate',
        );
        $paramString = str_replace("dt_", "", implode($this->delim, $params));
        echo $paramString . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            echo implode($this->delim, $tab) . "\n";
        }
    }

    /**
     * Add action
     *
     * @param  string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function add($parameters = null)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $params[self::ORDER_UNIQUENAME];
        $addParams['dt_description'] = $params[self::ORDER_ALIAS];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
        parent::add();
    }

    /**
     * Set params
     *
     * @param  string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function setparam($parameters = null)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            if (!preg_match("/^dt_/", $params[1])) {
                $params[1] = "dt_".$params[1];
            }
            $updateParams = array($params[1] => $params[2]);
            parent::setparam($objectId, $updateParams);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * List periods
     *
     * @param  string $parameters | downtime name
     * @throws CentreonClapiException
     */
    public function listperiods($parameters)
    {
        $dtId = $this->getObjectId($parameters);
        $rows = $this->getPeriods($dtId);

        echo implode(
            $this->delim,
            array(
                'position',
                'start time', 'end time', 'fixed', 'duration',
                'day of week', 'day of month', 'month cycle'
            )
        ) . "\n";
        $pos = 1;
        foreach ($rows as $row) {
            unset($row['dt_id']);
            echo $pos . $this->delim;
            echo implode($this->delim, $row) . "\n";
            $pos++;
        }
    }

    /**
     * Add weekly period
     *
     * @param  string $parameters | downtime_name;start;end;fixed;duration;monday...sunday
     * @throws CentreonClapiException
     */
    public function addweeklyperiod($parameters)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) != 6) {
            throw new CentreonClapiException('Incorrect number of parameters');
        }
        $p = array();
        $p[':dt_id'] = $this->getObjectId($tmp[0]);
        $p[':start_time'] = $tmp[1];
        $p[':end_time'] = $tmp[2];
        $p[':fixed'] = $tmp[3];
        $p[':duration'] = $tmp[4];
        $daysOfWeek = explode(',', strtolower($tmp[5]));
        $days = array();
        foreach ($daysOfWeek as $dayOfWeek) {
            if (!isset($this->weekDays[$dayOfWeek]) && !in_array($dayOfWeek, $this->weekDays)) {
                throw new CentreonClapiException(sprintf('Invalid period format %s', $dayOfWeek));
            }
            if (is_numeric($dayOfWeek)) { // value doesn't need conversion
                $days[] = $dayOfWeek;
            } else {
                $days[] = $this->weekDays[$dayOfWeek];
            }
        }
        $p[':day_of_week'] = implode(',', $days);
        $p[':day_of_month'] = null;
        $p[':month_cycle'] = 'all';
        $this->insertPeriod($p);
    }

    /**
     * Add monthly period
     *
     * @param string $parameters | downtime_name;start;end;fixed;duration;1...31
     */
    public function addmonthlyperiod($parameters)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) != 6) {
            throw new CentreonClapiException('Incorrect number of parameters');
        }
        $p = array();
        $p[':dt_id'] = $this->getObjectId($tmp[0]);
        $p[':start_time'] = $tmp[1];
        $p[':end_time'] = $tmp[2];
        $p[':fixed'] = $tmp[3];
        $p[':duration'] = $tmp[4];
        $p[':day_of_month'] = $tmp[5];
        $p[':day_of_week'] = null;
        $p[':month_cycle'] = 'none';
        $this->insertPeriod($p);
    }

    /**
     * Add specific period
     *
     * @param  string $parameters | downtime_name;start;end;fixed;duration;monday...sunday;first,last
     * @throws CentreonClapiException
     */
    public function addspecificperiod($parameters)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) != 7) {
            throw new CentreonClapiException('Incorrect number of parameters');
        }
        $p = array();
        $p[':dt_id'] = $this->getObjectId($tmp[0]);
        $p[':start_time'] = $tmp[1];
        $p[':end_time'] = $tmp[2];
        $p[':fixed'] = $tmp[3];
        $p[':duration'] = $tmp[4];
        $dayOfWeek = strtolower($tmp[5]);
        if (!isset($this->weekDays[$dayOfWeek]) && !in_array($dayOfWeek, $this->weekDays)) {
            throw new CentreonClapiException(sprintf('Invalid period format %s', $dayOfWeek));
        }
        if (is_numeric($dayOfWeek)) {
            $p[':day_of_week'] = $dayOfWeek;
        } else {
            $p[':day_of_week'] = $this->weekDays[$dayOfWeek];
        }
        $p[':day_of_month'] = null;

        $cycle = strtolower($tmp[6]);
        
        if (!in_array($cycle, $this->availableCycles)) {
            throw new CentreonClapiException(
                sprintf('Invalid cycle format %s. Must be "first", "second, "third", "fourth" or "last"', $cycle)
            );
        }
        
        $p[':month_cycle'] = $cycle;
        $this->insertPeriod($p);
    }


    /**
     * Delete period from downtime
     *
     * @param  string $parameters | downtime_name;position to delete
     * @throws CentreonClapiException
     */
    public function delperiod($parameters)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) != 2) {
            throw new CentreonClapiException('Incorrect number of parameters');
        }
        $sql = "DELETE FROM downtime_period
            WHERE dt_id = ?
            AND dtp_start_time = ?
            AND dtp_end_time = ?
            AND dtp_fixed = ?
            AND dtp_duration = ?
            AND dtp_day_of_week = ?
            AND dtp_day_of_month = ?
            AND dtp_month_cycle = ?";

        $period = $this->getPeriods($this->getObjectId($tmp[0]), $tmp[1]);
        $periodParams = array();
        foreach ($period as $k => $v) {
            if ($v == "") {
                $sql =  str_replace("{$k} = ?", "{$k} IS NULL", $sql);
            } else {
                $periodParams[] = $v;
            }
        }
        $this->db->query($sql, $periodParams);
    }

    /**
     * List resources
     *
     * @param string $parameters | downtime name
     */
    public function listresources($parameters)
    {
        $downtimeId = $this->getObjectId($parameters);

        // hosts
        $sql = "SELECT host_name
            FROM downtime_host_relation dhr, host h
            WHERE h.host_id = dhr.host_host_id
            AND dhr.dt_id = ?";
        $stmt = $this->db->query($sql, array($downtimeId));
        $rows = $stmt->fetchAll();
        $hosts = array();
        foreach ($rows as $row) {
            $hosts[] = $row['host_name'];
        }

        // host groups
        $sql = "SELECT hg_name
            FROM downtime_hostgroup_relation dhr, hostgroup hg
            WHERE hg.hg_id = dhr.hg_hg_id
            AND dhr.dt_id = ?";
        $stmt = $this->db->query($sql, array($downtimeId));
        $rows = $stmt->fetchAll();
        $hostgroups = array();
        foreach ($rows as $row) {
            $hostgroups[] = $row['hg_name'];
        }

        // services
        $sql = "SELECT host_name, service_description
            FROM downtime_service_relation dsr, host h, service s
            WHERE h.host_id = dsr.host_host_id
            AND dsr.service_service_id = s.service_id
            AND dsr.dt_id = ?";
        $stmt = $this->db->query($sql, array($downtimeId));
        $rows = $stmt->fetchAll();
        $services = array();
        foreach ($rows as $row) {
            $services[] = $row['host_name'].','.$row['service_description'];
        }

        // service groups
        $sql = "SELECT sg_name
            FROM downtime_servicegroup_relation dsr, servicegroup sg
            WHERE sg.sg_id = dsr.sg_sg_id
            AND dsr.dt_id = ?";
        $stmt = $this->db->query($sql, array($downtimeId));
        $rows = $stmt->fetchAll();
        $servicegroups = array();
        foreach ($rows as $row) {
            $servicegroups[] = $row['sg_name'];
        }

        // print header
        echo "hosts;host groups; services; service groups\n";
        echo implode("|", $hosts) . $this->delim;
        echo implode("|", $hostgroups) . $this->delim;
        echo implode("|", $services) . $this->delim;
        echo implode("|", $servicegroups) . "\n";
    }

    /**
     * Add host to downtime
     *
     * @param string $parameters | downtime name; host names separated by "|" character
     */
    public function addhost($parameters)
    {
        $object = new \Centreon_Object_Host();
        $this->addGenericRelation($parameters, $object, 'downtime_host_relation', 'host_host_id');
    }

    /**
     * Set host
     *
     * @param string $parameters | downtime name; host names separated by "|" character
     */
    public function sethost($parameters)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) != 2) {
            throw new CentreonClapiException("Missing parameters");
        }

        /* delete all host relationships */
        $downtimeId = $this->getObjectId($tmp[0]);
        $this->db->query("DELETE FROM downtime_host_relation WHERE dt_id = ?", array($downtimeId));

        $this->addhost($parameters);
    }

    /**
     * Delete host from downtime
     *
     * @param string $parameters | downtime name; host names separated by "|" character
     */
    public function delhost($parameters)
    {
        $object = new \Centreon_Object_Host();
        $this->delGenericRelation($parameters, $object, 'downtime_host_relation', 'host_host_id');
    }

    /**
     * Add host group to downtime
     *
     * @param string $parameters | downtime name; host group names separated by "|" character
     */
    public function addhostgroup($parameters)
    {
        $object = new \Centreon_Object_Host_Group();
        $this->addGenericRelation($parameters, $object, 'downtime_hostgroup_relation', 'hg_hg_id');
    }

    /**
     * Set host groups
     *
     * @param string $parameters | downtime name; host group names separated by "|" character
     */
    public function sethostgroup($parameters)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) != 2) {
            throw new CentreonClapiException("Missing parameters");
        }

        /* delete all host group relationships */
        $downtimeId = $this->getObjectId($tmp[0]);
        $this->db->query("DELETE FROM downtime_hostgroup_relation WHERE dt_id = ?", array($downtimeId));

        $this->addhostgroup($parameters);
    }

    /**
     * Delete host groups
     *
     * @param string $parameters | downtime name; host group names separated by "|" character
     */
    public function delhostgroup($parameters)
    {
        $object = new \Centreon_Object_Host_Group();
        $this->delGenericRelation($parameters, $object, 'downtime_hostgroup_relation', 'hg_hg_id');
    }

    /**
     * Add service to downtime
     *
     * @param string $parameters | downtime name; host_name,service_description separated by "|" character
     */
    public function addservice($parameters)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) != 2) {
            throw new CentreonClapiException('Missing parameters');
        }

        /* init var */
        $downtimeId = $this->getObjectId($tmp[0]);
        if ($downtimeId == 0) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $tmp[0]);
        }

        $resources = explode('|', $tmp[1]);

        /* retrieve object ids */
        $objectIds = array();
        foreach ($resources as $resource) {
            $tmp = explode(',', $resource);
            if (count($tmp) != 2) {
                throw new CentreonClapiException(sprintf('Wrong format for service %s', $resource));
            }
            $host = $tmp[0];
            $service = $tmp[1];

            $ids = $this->serviceObj->getHostAndServiceId($host, $service);

            /* object does not exist */
            if (!count($ids)) {
                throw new CentreonClapiException(sprintf('Could not find service %s on host %s', $service, $host));
            }


            /* checks whether or not relationship already exists */
            $sql = "SELECT *
                FROM downtime_service_relation
                WHERE dt_id = ?
                AND host_host_id = ?
                AND service_service_id = ?";
            $stmt = $this->db->query($sql, array($downtimeId, $ids[0], $ids[1]));
            if ($stmt->rowCount()) {
                throw new CentreonClapiException(sprintf('Relationship with %s / %s already exists', $host, $service));
            }

            $objectIds[] = $ids;
        }

        /* insert relationship */
        $sql = "INSERT INTO downtime_service_relation (dt_id, host_host_id, service_service_id) VALUES (?, ?, ?)";

        foreach ($objectIds as $id) {
            $this->db->query($sql, array($downtimeId, $id[0], $id[1]));
        }
    }

    /**
     * Set service
     *
     * @param string $parameters | downtime name; host_name,service_description separated by "|" character
     */
    public function setservice($parameters)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) != 2) {
            throw new CentreonClapiException("Missing parameters");
        }

        /* delete all service relationships */
        $downtimeId = $this->getObjectId($tmp[0]);
        $this->db->query("DELETE FROM downtime_service_relation WHERE dt_id = ?", array($downtimeId));

        $this->addservice($parameters);
    }

    /**
     * Delete service from downtime
     *
     * @param string $parameters | downtime name; host_name,service_description separated by "|" character
     */
    public function delservice($parameters)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) != 2) {
            throw new CentreonClapiException('Missing parameters');
        }

        /* init var */
        $downtimeId = $this->getObjectId($tmp[0]);
        $resources = explode('|', $tmp[1]);

        /* retrieve object ids */
        $objectIds = array();
        foreach ($resources as $resource) {
            $tmp = explode(',', $resource);
            if (count($tmp) != 2) {
                throw new CentreonClapiException(sprintf('Wrong format for service %s', $resource));
            }
            $host = $tmp[0];
            $service = $tmp[1];

            $ids = $this->serviceObj->getHostAndServiceId($host, $service);

            /* object does not exist */
            if (!count($ids)) {
                throw new CentreonClapiException(sprintf('Could not find service %s on host %s', $service, $host));
            }

            /* checks whether or not relationship already exists */
            $sql = "SELECT *
                FROM downtime_service_relation
                WHERE dt_id = ?
                AND host_host_id = ?
                AND service_service_id = ?";
            $stmt = $this->db->query($sql, array($downtimeId, $ids[0], $ids[1]));
            if (!$stmt->rowCount()) {
                throw new CentreonClapiException(sprintf('Relationship with %s / %s does not exist', $host, $service));
            }

            $objectIds[] = $ids;
        }

        /* delete relationship */
        $sql = "DELETE FROM downtime_service_relation
            WHERE dt_id = ?
            AND host_host_id = ?
            AND service_service_id = ?";
        foreach ($objectIds as $id) {
            $this->db->query($sql, array($downtimeId, $id[0], $id[1]));
        }
    }

    /**
     * Add service group to downtime
     *
     * @param string $parameters | downtime name; service group names separated by "|" character
     */
    public function addservicegroup($parameters)
    {
        $object = new \Centreon_Object_Service_Group();
        $this->addGenericRelation($parameters, $object, 'downtime_servicegroup_relation', 'sg_sg_id');
    }

    /**
     * Set service groups
     *
     * @param string $parameters | downtime name; service group names separated by "|" character
     */
    public function setservicegroup($parameters)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) != 2) {
            throw new CentreonClapiException("Missing parameters");
        }

        /* delete all service group relationships */
        $downtimeId = $this->getObjectId($tmp[0]);
        $this->db->query("DELETE FROM downtime_servicegroup_relation WHERE dt_id = ?", array($downtimeId));

        $this->addservicegroup($parameters);
    }

    /**
     * Delete service group from downtime
     *
     * @param string $parameters | downtime name; service group names separated by "|" character
     */
    public function delservicegroup($parameters)
    {
        $object = new \Centreon_Object_Service_Group();
        $this->delGenericRelation($parameters, $object, 'downtime_servicegroup_relation', 'sg_sg_id');
    }

    /**
     * Export
     */
    public function export()
    {
        // generic add & setparam
        parent::export();

        // handle host relationships
        $this->exportHostRel();

        // handle hostgroup relationships
        $this->exportHostgroupRel();

        // handle service relationships
        $this->exportServiceRel();

        // handle servicegroup relationships
        $this->exportServicegroupRel();

        // handle periods
        $this->exportPeriods();
    }

    /**
     *
     */
    protected function exportPeriods()
    {
        $sql = "SELECT dt_name, dtp_start_time, dtp_end_time, dtp_fixed, dtp_duration,
            dtp_day_of_week, dtp_day_of_month, dtp_month_cycle
            FROM downtime d, downtime_period p
            WHERE d.dt_id = p.dt_id";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $periodType = null;
            $extraData = array();
            $row['dtp_start_time'] = preg_replace('/:00$/', '', $row['dtp_start_time']);
            $row['dtp_end_time'] = preg_replace('/:00$/', '', $row['dtp_end_time']);
            if (!$row['dtp_day_of_month'] && $row['dtp_month_cycle'] == 'all') { // weekly
                $periodType = 'ADDWEEKLYPERIOD';
                $extraData[] = $row['dtp_day_of_week'];
            } elseif (!$row['dtp_day_of_week'] && $row['dtp_month_cycle'] == 'none') { // monthly
                $periodType = 'ADDMONTHLYPERIOD';
                $extraData[] = $row['dtp_day_of_month'];
            } elseif ($row['dtp_month_cycle'] == 'last' || $row['dtp_month_cycle'] == 'first') { // specific
                $periodType = 'ADDSPECIFICPERIOD';
                $extraData[] = $row['dtp_day_of_week'];
                $extraData[] = $row['dtp_month_cycle'];
            }
            if (!is_null($periodType)) {
                echo implode(
                    $this->delim,
                    array_merge(
                        array(
                            $this->action,
                            $periodType,
                            $row['dt_name'],
                            $row['dtp_start_time'],
                            $row['dtp_end_time'],
                            $row['dtp_fixed'],
                            $row['dtp_duration']

                        ),
                        $extraData
                    )
                ) . "\n";
            }
        }
    }

    /**
     *
     */
    protected function exportHostRel()
    {
        $sql = "SELECT dt_name, host_name as object_name
            FROM downtime d, host o, downtime_host_relation rel
            WHERE d.dt_id = rel.dt_id
            AND rel.host_host_id = o.host_id";
        $this->exportGenericRel('ADDHOST', $sql);
    }

    /**
     *
     */
    protected function exportHostgroupRel()
    {
        $sql = "SELECT dt_name, hg_name as object_name
            FROM downtime d, hostgroup o, downtime_hostgroup_relation rel
            WHERE d.dt_id = rel.dt_id
            AND rel.hg_hg_id = o.hg_id";
        $this->exportGenericRel('ADDHOSTGROUP', $sql);
    }

    /**
     *
     */
    protected function exportServiceRel()
    {
        $sql = "SELECT dt_name, CONCAT_WS(',', host_name, service_description) as object_name
            FROM downtime d, host h, service s, downtime_service_relation rel
            WHERE d.dt_id = rel.dt_id
            AND rel.host_host_id = h.host_id
            AND rel.service_service_id = s.service_id";
        $this->exportGenericRel('ADDSERVICE', $sql);
    }

    /**
     *
     */
    protected function exportServicegroupRel()
    {
        $sql = "SELECT dt_name, sg_name as object_name
            FROM downtime d, servicegroup o, downtime_servicegroup_relation rel
            WHERE d.dt_id = rel.dt_id
            AND rel.sg_sg_id = o.sg_id";
        $this->exportGenericRel('ADDSERVICEGROUP', $sql);
    }

    /**
     *
     * @param string $actionType | addhost, addhostgroup, addservice or addservicegroup
     * @param string $sql        | query
     */
    protected function exportGenericRel($actionType, $sql)
    {
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            echo implode(
                $this->delim,
                array(
                    $this->action,
                    $actionType,
                    $row['dt_name'],
                    $row['object_name']
                )
            ) . "\n";
        }
    }

    /**
     * Add period
     *
     * @param array $p
     */
    protected function insertPeriod($p)
    {
        /* check time periods */
        $p[':start_time'] .= ':00';
        $p[':end_time'] .= ':00';
        if (!preg_match('/^(\d+):(\d+):00$/', $p[':start_time'])) {
            throw new CentreonClapiException(sprintf('Invalid start time %s', $p[':start_time']));
        }
        if (!preg_match('/^(\d+):(\d+):00$/', $p[':end_time'])) {
            throw new CentreonClapiException(sprintf('Invalid end time %s', $p[':end_time']));
        }

        /* handle fixed / duration */
        if ($p[':fixed']) {
            $p[':duration'] = null;
        }

        /* delete duplicate period */
        $sql = "DELETE FROM downtime_period
            WHERE dt_id = :dt_id
            AND dtp_start_time = :start_time
            AND dtp_end_time = :end_time
            AND dtp_fixed = :fixed
            AND dtp_duration = :duration
            AND dtp_day_of_week = :day_of_week
            AND dtp_day_of_month = :day_of_month
            AND dtp_month_cycle = :month_cycle";
        $delParams = array();
        foreach ($p as $k => $v) {
            if ($v == "") {
                $sql = str_replace("= {$k}", 'IS NULL', $sql);
            } else {
                $delParams[$k] = $v;
            }
        }
        $this->db->query($sql, $delParams);

        $sql = "INSERT INTO downtime_period
            (dt_id, dtp_start_time, dtp_end_time, dtp_fixed, dtp_duration,
            dtp_day_of_week, dtp_day_of_month, dtp_month_cycle)
            VALUES (:dt_id, :start_time, :end_time, :fixed, :duration,
                :day_of_week, :day_of_month, :month_cycle)";
        $this->db->query($sql, $p);
    }

    /**
     * Get preiods from downtime id
     *
     * @param  int $downtimeId
     * @param  int $position
     * @return array
     */
    protected function getPeriods($downtimeId, $position = null)
    {
        $sql = "SELECT dt_id, dtp_start_time, dtp_end_time, dtp_fixed, dtp_duration,
            dtp_day_of_week, dtp_day_of_month, dtp_month_cycle
            FROM downtime_period
            WHERE dt_id = ?";
        $stmt = $this->db->query($sql, array($downtimeId));
        $rows = $stmt->fetchAll();

        if (!is_null($position)) {
            $cur = 1;
            foreach ($rows as $row) {
                if ($cur == $position) {
                    return $row;
                }
                $cur++;
            }
            throw new CentreonClapiException(sprintf('Could not find position %s', $position));
        }

        return $rows;
    }

    /**
     * Add resource to downtime
     *
     * @param string          $parameters | downtime name; resource names separated by "|" character
     * @param Centreon_Object $object
     * @param string          $relTable
     * @param string          $relField
     */
    protected function addGenericRelation($parameters, $object, $relTable, $relField)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) != 2) {
            throw new CentreonClapiException('Missing parameters');
        }

        /* init var */
        $downtimeId = $this->getObjectId($tmp[0]);
        $resources = explode('|', $tmp[1]);

        /* retrieve object ids */
        $objectIds = array();
        foreach ($resources as $resource) {
            $ids = $object->getIdByParameter($object->getUniqueLabelField(), array($resource));

            /* object does not exist */
            if (!count($ids)) {
                throw new CentreonClapiException(sprintf('Unknown object named %s', $resource));
            }

            /* checks whether or not relationship already exists */
            $sql = "SELECT * FROM {$relTable} WHERE dt_id = ? AND {$relField} = ?";
            $stmt = $this->db->query($sql, array($downtimeId, $ids[0]));
            if ($stmt->rowCount()) {
                throw new CentreonClapiException(sprintf('Relationship with %s already exists', $resource));
            }

            $objectIds[] = $ids[0];
        }

        /* insert relationship */
        $sql = "INSERT INTO {$relTable} (dt_id, {$relField}) VALUES (?, ?)";
        foreach ($objectIds as $id) {
            $this->db->query($sql, array($downtimeId, $id));
        }
    }

    /**
     * Delete resource from downtime
     *
     * @param string          $parameters | downtime name; resource name separated by "|" character
     * @param Centreon_Object $object
     * @param string          $relTable
     * @param string          $relField
     */
    protected function delGenericRelation($parameters, $object, $relTable, $relField)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) != 2) {
            throw new CentreonClapiException('Missing parameters');
        }

        /* init var */
        $downtimeId = $this->getObjectId($tmp[0]);
        $resources = explode('|', $tmp[1]);

        /* retrieve object ids */
        $objectIds = array();
        foreach ($resources as $resource) {
            $ids = $object->getIdByParameter($object->getUniqueLabelField(), array($resource));

            /* object does not exist */
            if (!count($ids)) {
                throw new CentreonClapiException(sprintf('Unknown object named %s', $resource));
            }

            /* checks whether or not relationship already exists */
            $sql = "SELECT * FROM {$relTable} WHERE dt_id = ? AND {$relField} = ?";
            $stmt = $this->db->query($sql, array($downtimeId, $ids[0]));
            if (!$stmt->rowCount()) {
                throw new CentreonClapiException(
                    sprintf(
                        'Cannot remove relationship with %s as the relationship does not exist',
                        $resource
                    )
                );
            }

            $objectIds[] = $ids[0];
        }

        /* delete relationship */
        $sql = "DELETE FROM {$relTable} WHERE dt_id = ? AND {$relField} = ?";
        foreach ($objectIds as $id) {
            $this->db->query($sql, array($downtimeId, $id));
        }
    }
}
