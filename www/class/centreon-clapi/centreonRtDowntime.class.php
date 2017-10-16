<?php
/*
 * Copyright 2005-2017 CENTREON
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
require_once "Centreon/Object/Downtime/RtDowntime.php";
require_once "Centreon/Object/Host/Host.php";
require_once "Centreon/Object/Host/Group.php";
require_once "Centreon/Object/Service/Group.php";
require_once "Centreon/Object/Relation/Downtime/Host.php";
require_once "Centreon/Object/Relation/Downtime/Hostgroup.php";
require_once "Centreon/Object/Relation/Downtime/Servicegroup.php";
require_once realpath(dirname(__FILE__) . '/../centreonExternalCommand.class.php');
require_once realpath(dirname(__FILE__) . '/../centreonDB.class.php');
require_once realpath(dirname(__FILE__) . '/../centreonUser.class.php');
require_once realpath(dirname(__FILE__) . '/../centreonGMT.class.php');
require_once realpath(dirname(__FILE__) . '/../centreonHostgroups.class.php');
require_once realpath(dirname(__FILE__) . '/../centreonServicegroups.class.php');
require_once realpath(dirname(__FILE__) . '/../centreonInstance.class.php');

class CentreonRtDowntime extends CentreonObject
{
    /**
     * @var array
     */
    protected $downtimeType = array(
        'HOST',
        'SVC',
        'HG',
        'SG',
        'INSTANCE',
    );

    /**
     * @var
     */
    protected $dHosts;

    /**
     * @var
     */
    protected $dServices;

    /**
     * CentreonRtDowntime constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->object = new \Centreon_Object_RtDowntime();
        $this->db = new \CentreonDB('centreon');
        $this->hgObject = new \CentreonHostgroups($this->db);
        $this->hostObject = new CentreonHost($this->db);
        $this->serviceObject = new CentreonService($this->db);
        $this->sgObject = new \CentreonServiceGroups($this->db);
        $this->instanceObject = new \CentreonInstance($this->db);
        $this->GMTObject = new \CentreonGMT($this->db);
        $this->externalCmdObj = new \CentreonExternalCommand();
        $this->action = "RTDOWNTIME";
        $this->externalCmdObj->setUserAlias(CentreonUtils::getUserName());
        $this->externalCmdObj->setUserId(CentreonUtils::getUserId());
    }

    /**
     * @param $parameters
     * @return array
     * @throws CentreonClapiException
     */
    private function parseParameters($parameters)
    {
        // Make safe the inputs
        list($type, $resource, $start, $end, $fixed, $duration, $withServices, $comment) = explode(';', $parameters);

        // Check if object type is supported
        if (!in_array(strtoupper($type), $this->downtimeType)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        // Check date format
        $checkStart = \DateTime::createFromFormat('Y/m/d H:i', $start);
        $checkEnd = \DateTime::createFromFormat('Y/m/d H:i', $end);
        if (!$checkStart || !$checkEnd) {
            throw new CentreonClapiException('Wrong date format, expected : YYYY/MM/DD HH:mm');
        }

        // Check if fixed is 0 or 1
        if (!preg_match('/^(0|1)$/', $fixed)) {
            throw new CentreonClapiException('Bad fixed parameter (0 or 1)');
        }

        // Check duration parameters
        if (($fixed == 0 && (!preg_match('/^\d+$/', $duration) || $duration <= 0)) ||
            $fixed == 1 && !preg_match('/(^$)||(^\d+$)/', $duration)
        ) {
            throw new CentreonClapiException('Bad duration parameter');
        }

        // Check if host with services
        if (strtoupper($type) === 'HOST') {
            if (!preg_match('/^(0|1)$/', $withServices)) {
                throw new CentreonClapiException('Bad "apply to services" parameter (0 or 1)');
            }
        }

        $withServices = ($withServices == 1) ? true : false;

        // Make safe the comment
        $comment = escapeshellarg($comment);

        return array(
            'type' => $type,
            'resource' => $resource,
            'start' => $start,
            'end' => $end,
            'fixed' => $fixed,
            'duration' => $duration,
            'withServices' => $withServices,
            'comment' => $comment,
        );
    }

    /**
     * @param $parameters
     * @return array
     */
    private function parseShowParameters($parameters)
    {
        list($type, $resource) = explode(';', $parameters);

        return array(
            'type' => $type,
            'resource' => $resource,
        );
    }

    /**
     * @param null $parameters
     */
    public function show($parameters = null)
    {
        if ($parameters !== '') {
            $parsedParameters = $this->parseShowparameters($parameters);
            if (strtoupper($parsedParameters['type']) !== 'HOST' && strtoupper($parsedParameters['type']) !== 'SVC') {
                throw new CentreonClapiException(self::UNKNOWNPARAMETER);
            }
            $method = 'show' . ucfirst($parsedParameters['type']);
            $this->$method($parsedParameters['resource']);
        } else {
            $this->dHosts = $this->object->getHostDowntimes();
            $this->dServices = $this->object->getSvcDowntimes();

            $list = '';
            //all host
            if (count($this->dHosts) !== 0) {
                foreach ($this->dHosts as $host) {
                    $list .= $host['name'] . '|';
                }
                $list = rtrim($list, '|');
            }
            $list .= ';';

            //all service
            if (count($this->dServices) !== 0) {
                foreach ($this->dServices as $service) {
                    $list .= $service['name'] . ',' . $service['description'] . '|';
                }
                $list = rtrim($list, '|');
            }
            $list .= ';';

            echo "hosts;services\n";
            echo $list;
        }

    }

    /**
     * @param $hostList
     * @throws CentreonClapiException
     */
    public function showHost($hostList)
    {
        $fields = array(
            'host_name',
            'author',
            'actual_start_time',
            'actual_end_time',
            'start_time',
            'end_time',
            'comment_data',
            'duration',
            'fixed',
            'url',
        );

        echo implode($this->delim, $fields) . "\n";

        $hostList = array_filter(explode('|', $hostList));
        $db = $this->db;
        $hostList = array_map(
            function ($element) use ($db) {
                return $db->escape($element);
            },
            $hostList
        );

        // Result of the research in the base
        $hostDowntimesList = $this->object->getHostDowntimes($hostList);

        // Init user timezone
        $this->GMTObject->getMyGTMFromUser(CentreonUtils::getuserId());

        $existingHost = array();
        //Separates hosts
        foreach ($hostDowntimesList as $hostDowntime) {
            $existingHost[] = $hostDowntime['name'];
            $url = '';
            if (isset($_SERVER['HTTP_HOST'])) {
                $url = $this->getBaseUrl() . '/' . 'main.php?p=210&search_host=' . $hostDowntime['name'];
            }

            $hostDowntime['actual_start_time'] = $this->GMTObject->getDate(
                'Y/m/d H:i',
                $hostDowntime['actual_start_time'],
                $this->GMTObject->getMyGMT()
            );

            $hostDowntime['actual_end_time'] = $this->GMTObject->getDate(
                'Y/m/d H:i',
                $hostDowntime['actual_end_time'],
                $this->GMTObject->getMyGMT()
            );

            $hostDowntime['start_time'] = $this->GMTObject->getDate(
                'Y/m/d H:i',
                $hostDowntime['start_time'],
                $this->GMTObject->getMyGMT()
            );

            $hostDowntime['end_time'] = $this->GMTObject->getDate(
                'Y/m/d H:i',
                $hostDowntime['end_time'],
                $this->GMTObject->getMyGMT()
            );

            echo implode($this->delim, array_values($hostDowntime)) . ';' . $url . "\n";
        }

        $diff = array_diff($hostList, $existingHost);
        if (count($diff) !== 0) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ' : Host : ' . implode('|', $diff) . "\n");
        }
    }

    /**
     * @param $svcList
     */
    public function showSvc($svcList)
    {
        $fields = array(
            'host_name',
            'service_name',
            'author',
            'actual_start_time',
            'actual_end_time',
            'start_time',
            'end_time',
            'comment_data',
            'duration',
            'fixed',
            'url',
        );

        echo implode($this->delim, $fields) . "\n";

        $svcList = array_filter(explode('|', $svcList));
        $db = $this->db;
        $svcList = array_map(
            function ($arrayElem) use ($db) {
                return $db->escape($arrayElem);
            },
            $svcList
        );

        // Result of the research in the base
        $serviceDowntimesList = $this->object->getSvcDowntimes($svcList);

        // Init user timezone
        $this->GMTObject->getMyGTMFromUser(CentreonUtils::getuserId());

        //Separates hosts and services
        foreach ($serviceDowntimesList as $serviceDowntime) {
            $url = '';
            if (isset($_SERVER['HTTP_HOST'])) {
                $url = $this->getBaseUrl() .
                    '/' . 'main.php?p=210&search_host=' . $serviceDowntime['name'] .
                    '&search_service=' . $serviceDowntime['description'];
            }

            $serviceDowntime['actual_start_time'] = $this->GMTObject->getDate(
                'Y/m/d H:i',
                $serviceDowntime['actual_start_time'],
                $this->GMTObject->getMyGMT()
            );

            $serviceDowntime['actual_end_time'] = $this->GMTObject->getDate(
                'Y/m/d H:i',
                $serviceDowntime['actual_end_time'],
                $this->GMTObject->getMyGMT()
            );

            $serviceDowntime['start_time'] = $this->GMTObject->getDate(
                'Y/m/d H:i',
                $serviceDowntime['start_time'],
                $this->GMTObject->getMyGMT()
            );

            $serviceDowntime['end_time'] = $this->GMTObject->getDate(
                'Y/m/d H:i',
                $serviceDowntime['end_time'],
                $this->GMTObject->getMyGMT()
            );

            echo implode($this->delim, array_values($serviceDowntime)) . ';' . $url . "\n";
        }
    }

    /**
     * @param null $parameters
     */
    public function add($parameters = null)
    {
        $parsedParameters = $this->parseParameters($parameters);

        // to choose the best add (addHostDowntime, addSvcDowntime etc.)
        $method = 'add' . ucfirst($parsedParameters['type']) . 'Downtime';
        $this->$method(
            $parsedParameters['resource'],
            $parsedParameters['start'],
            $parsedParameters['end'],
            $parsedParameters['fixed'],
            $parsedParameters['duration'],
            $parsedParameters['withServices'],
            $parsedParameters['comment']
        );
    }

    /**
     * @param $resource
     * @param $start
     * @param $end
     * @param $fixed
     * @param $duration
     * @param $comment
     * @param $withServices
     */
    private function addHostDowntime(
        $resource,
        $start,
        $end,
        $fixed,
        $duration,
        $withServices,
        $comment
    ) {

        if ($this->hostObject->getHostID($resource) == 0) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND);
        }

        $this->externalCmdObj->addHostDowntime(
            $resource,
            $comment,
            $start,
            $end,
            $fixed,
            $duration,
            $withServices
        );
    }

    /**
     * @param $resource
     * @param $start
     * @param $end
     * @param $fixed
     * @param $duration
     * @param $comment
     * @param $withServices
     * @throws CentreonClapiException
     */
    private function addSvcDowntime(
        $resource,
        $start,
        $end,
        $fixed,
        $duration,
        $withServices,
        $comment
    ) {
        $withServices = 0;
        // Check if a pipe is present
        if (preg_match('/^(.+)\|(.+)$/', $resource, $matches)) {
            $this->externalCmdObj->addSvcDowntime(
                $matches[1],
                $matches[2],
                $comment,
                $start,
                $end,
                $fixed,
                $duration,
                $withServices
            );
        } else {
            throw new CentreonClapiException('Bad resource parameter');
        }
    }

    /**
     * @param $resource
     * @param $start
     * @param $end
     * @param $fixed
     * @param $duration
     * @param $comment
     * @param $withServices
     */
    private function addHgDowntime(
        $resource,
        $start,
        $end,
        $fixed,
        $duration,
        $withServices,
        $comment
    ) {
        $hostList = $this->hgObject->getHostsByHostgroupName($resource);

        //check add services with host
        if (count($hostList) == 0) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND);
        }
        if ($withServices === true) {
            foreach ($hostList as $host) {
                $this->externalCmdObj->addHostDowntime(
                    $host['host'],
                    $comment,
                    $start,
                    $end,
                    $fixed,
                    $duration,
                    true
                );
            }
        } else {
            foreach ($hostList as $host) {
                $this->externalCmdObj->addHostDowntime(
                    $host['host'],
                    $comment,
                    $start,
                    $end,
                    $fixed,
                    $duration,
                    $withServices
                );
            }
        }
    }

    /**
     * @param $resource
     * @param $start
     * @param $end
     * @param $fixed
     * @param $duration
     * @param $comment
     * @param $withServices
     */
    private function addSgDowntime(
        $resource,
        $start,
        $end,
        $fixed,
        $duration,
        $withServices,
        $comment
    ) {
        $withServices = 0;
        $serviceList = $this->sgObject->getServicesByServicegroupName($resource);

        if (count($serviceList) == 0) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND);
        }

        foreach ($serviceList as $service) {
            $this->externalCmdObj->addSvcDowntime(
                $service['host'],
                $service['service'],
                $comment,
                $start,
                $end,
                $fixed,
                $duration,
                $withServices
            );
        }
    }

    /**
     * @param $resource
     * @param $start
     * @param $end
     * @param $fixed
     * @param $duration
     * @param $comment
     * @param $withServices
     */
    private function addInstanceDowntime(
        $resource,
        $start,
        $end,
        $fixed,
        $duration,
        $withServices,
        $comment
    ) {
        $hostList = $this->instanceObject->getHostsByInstance($resource);

        //check add services with host with true in last param
        foreach ($hostList as $host) {
            $this->externalCmdObj->addHostDowntime(
                $host['host'],
                $comment,
                $start,
                $end,
                $fixed,
                $duration,
                true
            );
        }
    }
}
