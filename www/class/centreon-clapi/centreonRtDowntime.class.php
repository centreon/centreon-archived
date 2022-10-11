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
require_once dirname(__FILE__, 2) . '/centreonExternalCommand.class.php';
require_once dirname(__FILE__, 2) . '/centreonDB.class.php';
require_once dirname(__FILE__, 2) . '/centreonUser.class.php';
require_once dirname(__FILE__, 2) . '/centreonGMT.class.php';
require_once dirname(__FILE__, 2) . '/centreonHostgroups.class.php';
require_once dirname(__FILE__, 2) . '/centreonServicegroups.class.php';
require_once dirname(__FILE__, 2) . '/centreonInstance.class.php';
require_once __DIR__ . "/Validator/RtValidator.php";

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
     * @var \CentreonClapi\CentreonHost
     */
    protected $hostObject;

    /**
     * @var \CentreonClapi\CentreonService
     */
    protected $serviceObject;

    /**
     * @var \CentreonClapi\Validator\RtValidator
     */
    protected $rtValidator;

    /**
     * CentreonRtDowntime constructor.
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->object = new \Centreon_Object_RtDowntime($dependencyInjector);
        $this->hgObject = new \CentreonHostgroups($this->db);
        $this->hostObject = new \CentreonClapi\CentreonHost($dependencyInjector);
        $this->serviceObject = new \CentreonClapi\CentreonService($dependencyInjector);
        $this->sgObject = new \CentreonServiceGroups($this->db);
        $this->instanceObject = new \CentreonInstance($this->db);
        $this->GMTObject = new \CentreonGMT();
        $this->externalCmdObj = new \CentreonExternalCommand();
        $this->action = "RTDOWNTIME";
        $this->externalCmdObj->setUserAlias(CentreonUtils::getUserName());
        $this->externalCmdObj->setUserId(CentreonUtils::getUserId());
        $this->rtValidator = new \CentreonClapi\Validator\RtValidator($this->hostObject, $this->serviceObject);
    }

    /**
     * @param $date
     * @param string $format
     * @return bool
     */
    private function validateDate($date, $format = 'Y/m/d H:i')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * @param $parameters
     * @return array
     * @throws CentreonClapiException
     */
    private function parseParameters($parameters)
    {
        // Make safe the inputs
        list($type, $resource, $start, $end, $fixed, $duration, $comment, $withServices) = explode(';', $parameters);

        // Check if object type is supported
        if (!in_array(strtoupper($type), $this->downtimeType)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        // Check date format
        $checkStart = $this->validateDate($start);
        $checkEnd = $this->validateDate($end);

        if (!$checkStart || !$checkEnd) {
            throw new CentreonClapiException('Wrong date format, expected : YYYY/MM/DD HH:mm');
        }

        // Check if fixed is 0 or 1
        if (!preg_match('/^(0|1)$/', $fixed)) {
            throw new CentreonClapiException('Bad fixed parameter (0 or 1)');
        }

        // Check duration parameters
        if (
            ($fixed == 0 && (!preg_match('/^\d+$/', $duration) || $duration <= 0))
            || ($fixed == 1 && !preg_match('/(^$)||(^\d+$)/', $duration))
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
     * @param array $filter
     * @throws CentreonClapiException
     */
    public function show($parameters = null, $filter = array())
    {
        if ($parameters !== '') {
            $parsedParameters = $this->parseShowparameters($parameters);
            if (strtoupper($parsedParameters['type']) !== 'HOST' && strtoupper($parsedParameters['type']) !== 'SVC') {
                throw new CentreonClapiException(self::UNKNOWNPARAMETER . ' : ' . $parsedParameters['type']);
            }
            $method = 'show' . ucfirst(strtolower($parsedParameters['type']));
            $this->$method($parsedParameters['resource']);
        } else {
            $this->dHosts = $this->object->getHostDowntimes();
            $this->dServices = $this->object->getSvcDowntimes();

            //all host
            $hostsToReturn = [];
            foreach ($this->dHosts as $host) {
                $hostsToReturn[] = $host['name'];
            }

            //all service
            $servicesToReturn = [];
            foreach ($this->dServices as $service) {
                $servicesToReturn[] = $service['name'] . ',' . $service['description'];
            }

            echo "hosts;services\n";
            if ([] !== $hostsToReturn || [] !== $servicesToReturn) {
                echo implode('|', $hostsToReturn) . ';' . implode('|', $servicesToReturn) . "\n";
            }
        }
    }

    /**
     * @param $hostList
     * @throws CentreonClapiException
     */
    public function showHost($hostList)
    {
        $unknownHost = [];

        $fields = array(
            'id',
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

        if (!empty($hostList)) {
            $hostList = array_filter(explode('|', $hostList));
            $db = $this->db;
            $hostList = array_map(
                function ($element) use ($db) {
                    return $db->escape($element);
                },
                $hostList
            );

            // check if host exist
            $existingHost = array();
            foreach ($hostList as $host) {
                if ($this->hostObject->getHostID($host) == 0) {
                    $unknownHost[] = $host;
                } else {
                    $existingHost[] = $host;
                }
            }
            // Result of the research in the base
            $hostDowntimesList = $this->object->getHostDowntimes($existingHost);
        } else {
            $hostDowntimesList = $this->object->getHostDowntimes();
        }

        // Init user timezone
        $this->GMTObject->getMyGTMFromUser(CentreonUtils::getuserId());

        echo implode($this->delim, $fields) . "\n";
        //Separates hosts
        if (count($hostDowntimesList)) {
            foreach ($hostDowntimesList as $hostDowntime) {
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
        }

        if (count($unknownHost) !== 0) {
            echo "\n";
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ' : Host : ' . implode('|', $unknownHost) . "\n");
        }
    }

    /**
     * @param $svcList
     * @throws CentreonClapiException
     */
    public function showSvc($svcList)
    {
        $serviceDowntimesList = array();
        $unknownService = array();
        $existingService = array();

        $fields = array(
            'id',
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

        if (!empty($svcList)) {
            $svcList = array_filter(explode('|', $svcList));
            $db = $this->db;
            $svcList = array_map(
                function ($arrayElem) use ($db) {
                    return $db->escape($arrayElem);
                },
                $svcList
            );

            // check if service exist
            foreach ($svcList as $service) {
                $serviceData = explode(',', $service);
                if ($this->serviceObject->serviceExists($serviceData[0], $serviceData[1])) {
                    $existingService[] = $serviceData;
                } else {
                    $unknownService[] = $service;
                }
            }

            // Result of the research in the base
            if (count($existingService)) {
                foreach ($existingService as $svc) {
                    $tmpDowntime = $this->object->getSvcDowntimes($svc);
                    if (!empty($tmpDowntime)) {
                        $serviceDowntimesList = $tmpDowntime;
                    }
                }
            }
        } else {
            $serviceDowntimesList = $this->object->getSvcDowntimes();
        }

        // Init user timezone
        $this->GMTObject->getMyGTMFromUser(CentreonUtils::getuserId());

        //Separates hosts and services
        echo implode($this->delim, $fields) . "\n";

        if (count($serviceDowntimesList)) {
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

        if (count($unknownService) !== 0) {
            echo "\n";
            throw new CentreonClapiException(
                self::OBJECT_NOT_FOUND . ' : Service : ' . implode('|', $unknownService) . "\n"
            );
        }
    }

    /**
     * @param null $parameters
     */
    public function add($parameters = null)
    {
        $parsedParameters = $this->parseParameters($parameters);

        // to choose the best add (addHostDowntime, addSvcDowntime etc.)
        $method = 'add' . ucfirst(strtolower($parsedParameters['type'])) . 'Downtime';
        if ((strtolower($parsedParameters['type']) === 'host') || (strtolower($parsedParameters['type']) === 'hg')) {
            $this->$method(
                $parsedParameters['resource'],
                $parsedParameters['start'],
                $parsedParameters['end'],
                $parsedParameters['fixed'],
                $parsedParameters['duration'],
                $parsedParameters['comment'],
                $parsedParameters['withServices']
            );
        } else {
            $this->$method(
                $parsedParameters['resource'],
                $parsedParameters['start'],
                $parsedParameters['end'],
                $parsedParameters['fixed'],
                $parsedParameters['duration'],
                $parsedParameters['comment']
            );
        }
    }

    /**
     * @param $resource
     * @param $start
     * @param $end
     * @param $fixed
     * @param $duration
     * @param $withServices
     * @param $comment
     * @throws CentreonClapiException
     */
    private function addHostDowntime(
        $resource,
        $start,
        $end,
        $fixed,
        $duration,
        $comment,
        $withServices = true
    ) {
        if ($resource === "") {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $unknownHost = array();
        $listHost = explode('|', $resource);

        foreach ($listHost as $host) {
            if ($this->rtValidator->isHostNameValid($host)) {
                $this->externalCmdObj->addHostDowntime(
                    $host,
                    $comment,
                    $start,
                    $end,
                    $fixed,
                    $duration,
                    $withServices
                );
            } else {
                $unknownHost[] = $host;
            }
        }

        if (count($unknownHost)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ' HOST : ' . implode('|', $unknownHost));
        }
    }

    /**
     * @param $resource
     * @param $start
     * @param $end
     * @param $fixed
     * @param $duration
     * @param $comment
     * @throws CentreonClapiException
     */
    private function addSvcDowntime(
        $resource,
        $start,
        $end,
        $fixed,
        $duration,
        $comment
    ) {
        if ($resource === "") {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $unknownService = array();
        $listService = explode('|', $resource);
        $existingService = [];

        // check if service exist
        foreach ($listService as $service) {
            $serviceData = explode(',', $service);
            if ($this->rtValidator->isServiceNameValid($serviceData[0], $serviceData[1])) {
                $existingService[] = $serviceData;
            } else {
                $unknownService[] = $service;
            }
        }

        // Result of the research in the base
        if (count($existingService)) {
            foreach ($existingService as $service) {
                $this->externalCmdObj->addSvcDowntime(
                    $service[0],
                    $service[1],
                    $comment,
                    $start,
                    $end,
                    $fixed,
                    $duration
                );
            }
        }

        if (count($unknownService)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ' SERVICE : ' . implode('|', $unknownService));
        }
    }

    /**
     * @param $resource
     * @param $start
     * @param $end
     * @param $fixed
     * @param $duration
     * @param $comment
     * @param int $withServices
     * @throws CentreonClapiException
     */
    private function addHgDowntime(
        $resource,
        $start,
        $end,
        $fixed,
        $duration,
        $comment,
        $withServices = true
    ) {
        if ($resource === "") {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $existingHg = array();
        $unknownHg = array();
        $listHg = explode('|', $resource);

        // check if service exist
        foreach ($listHg as $hg) {
            if ($this->hgObject->getHostgroupId($hg)) {
                $existingHg[] = $hg;
            } else {
                $unknownHg[] = $hg;
            }
        }
        if (count($existingHg)) {
            foreach ($existingHg as $hg) {
                $hostList = $this->hgObject->getHostsByHostgroupName($hg);
                //check add services with host
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
        if (count($unknownHg)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ' HG : ' . implode('|', $unknownHg));
        }
    }

    /**
     * @param $resource
     * @param $start
     * @param $end
     * @param $fixed
     * @param $duration
     * @param $comment
     * @throws CentreonClapiException
     */
    private function addSgDowntime(
        $resource,
        $start,
        $end,
        $fixed,
        $duration,
        $comment
    ) {
        if ($resource === "") {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        $existingSg = array();
        $unknownSg = array();
        $listSg = explode('|', $resource);

        // check if service exist
        foreach ($listSg as $sg) {
            if ($this->sgObject->getServicesGroupId($sg)) {
                $existingSg[] = $sg;
            } else {
                $unknownSg[] = $sg;
            }
        }

        if (count($existingSg)) {
            foreach ($existingSg as $sg) {
                $serviceList = $this->sgObject->getServicesByServicegroupName($sg);
                foreach ($serviceList as $service) {
                    $this->externalCmdObj->addSvcDowntime(
                        $service['host'],
                        $service['service'],
                        $comment,
                        $start,
                        $end,
                        $fixed,
                        $duration
                    );
                }
            }
        }

        if (count($unknownSg)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ' SG : ' . implode('|', $unknownSg));
        }
    }

    /**
     * @param $resource
     * @param $start
     * @param $end
     * @param $fixed
     * @param $duration
     * @param $comment
     * @throws CentreonClapiException
     */
    private function addInstanceDowntime(
        $resource,
        $start,
        $end,
        $fixed,
        $duration,
        $comment
    ) {
        if ($resource === "") {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        $existingPoller = array();
        $unknownPoller = array();
        $listPoller = explode('|', $resource);

        foreach ($listPoller as $poller) {
            if ($this->instanceObject->getInstanceId($poller)) {
                $existingPoller[] = $poller;
            } else {
                $unknownPoller[] = $poller;
            }
        }

        if (count($existingPoller)) {
            foreach ($existingPoller as $poller) {
                $hostList = $this->instanceObject->getHostsByInstance($poller);
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

        if (count($unknownPoller)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ' INSTANCE : ' . implode('|', $unknownPoller));
        }
    }

    /**
     * @param null $parameters
     * @throws CentreonClapiException
     */
    public function cancel($parameters = null)
    {
        if (empty($parameters) || is_null($parameters)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $listDowntime = explode('|', $parameters);
        $unknownDowntime = array();

        foreach ($listDowntime as $downtime) {
            if (!is_numeric($downtime)) {
                $unknownDowntime[] = $downtime;
            } else {
                $infoDowntime = $this->object->getCurrentDowntime($downtime);
                if ($infoDowntime) {
                    $hostName = $this->hostObject->getHostName($infoDowntime['host_id']);
                    if ($infoDowntime['type'] == 2) {
                        $this->externalCmdObj->deleteDowntime(
                            'HOST',
                            array($hostName . ';' . $infoDowntime['internal_id'] => 'on')
                        );
                    } else {
                        $this->externalCmdObj->deleteDowntime(
                            'SVC',
                            array($hostName . ';' . $infoDowntime['internal_id'] => 'on')
                        );
                    }
                } else {
                    $unknownDowntime[] = $downtime;
                }
            }
        }

        if (count($unknownDowntime)) {
            throw new CentreonClapiException(
                self::OBJECT_NOT_FOUND . ' DOWNTIME ID : ' . implode('|', $unknownDowntime)
            );
        }
    }
}
