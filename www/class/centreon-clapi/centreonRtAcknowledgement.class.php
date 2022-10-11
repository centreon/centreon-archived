<?php

/*
 * Copyright 2005-2020 CENTREON
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
require_once "Centreon/Object/Acknowledgement/RtAcknowledgement.php";
require_once "Centreon/Object/Host/Host.php";
require_once "Centreon/Object/Service/Service.php";
require_once dirname(__FILE__, 2) . '/centreonExternalCommand.class.php';
require_once dirname(__FILE__, 2) . '/centreonDB.class.php';
require_once dirname(__FILE__, 2) . '/centreonUser.class.php';
require_once dirname(__FILE__, 2) . '/centreonGMT.class.php';
require_once __DIR__ . "/Validator/RtValidator.php";

/**
 * Manage Acknowledgement with clapi
 *
 * Class CentreonRtAcknowledgement
 * @package CentreonClapi
 */
class CentreonRtAcknowledgement extends CentreonObject
{
    /**
     * @var array
     */
    protected $acknowledgementType = array(
        'HOST',
        'SVC'
    );

    /**
     * @var
     */
    protected $aHosts;

    /**
     * @var
     */
    protected $aServices;

    /**
     * @var
     */
    protected $hostObject;

    /**
     * @var
     */
    protected $serviceObject;

    /**
     * @var
     */
    protected $GMTObject;

    /**
     * @var
     */
    protected $externalCmdObj;

    /**
     * @var
     */
    protected $author;

    /**
     * @var \CentreonClapi\Validator\RtValidator
     */
    protected $rtValidator;

    /**
     * CentreonRtAcknowledgement constructor.
     * @param \Pimple\Container $dependencyInjector
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->object = new \Centreon_Object_RtAcknowledgement($dependencyInjector);
        $this->hostObject = new \CentreonClapi\CentreonHost($dependencyInjector);
        $this->serviceObject = new \CentreonClapi\CentreonService($dependencyInjector);
        $this->GMTObject = new \CentreonGMT();
        $this->externalCmdObj = new \CentreonExternalCommand();
        $this->action = "RTACKNOWLEDGEMENT";
        $this->author = CentreonUtils::getUserName();
        $this->rtValidator = new \CentreonClapi\Validator\RtValidator($this->hostObject, $this->serviceObject);

        $this->externalCmdObj->setUserAlias($this->author);
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
        list($type, $resource, $comment, $sticky, $notify, $persistent) = explode(';', $parameters);

        // Check if object type is supported
        if (!in_array(strtoupper($type), $this->acknowledgementType)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        // Check if sticky is 0 or 2
        if (!preg_match('/^(0|1|2)$/', $sticky)) {
            throw new CentreonClapiException('Bad sticky parameter (0 or 1/2)');
        }

        // Check if notify is 0 or 1
        if (!preg_match('/^(0|1)$/', $notify)) {
            throw new CentreonClapiException('Bad notify parameter (0 or 1)');
        }

        // Check if fixed is 0 or 1
        if (!preg_match('/^(0|1)$/', $persistent)) {
            throw new CentreonClapiException('Bad persistent parameter (0 or 1)');
        }

        // Make safe the comment
        $comment = escapeshellarg($comment);

        return array(
            'type' => $type,
            'resource' => $resource,
            'comment' => $comment,
            'sticky' => $sticky,
            'notify' => $notify,
            'persistent' => $persistent
        );
    }

    /**
     * @param $parameters
     * @return array
     */
    private function parseShowParameters($parameters)
    {
        $parameters = explode(';', $parameters);
        if (count($parameters) === 1) {
            $resource = '';
        } elseif (count($parameters) === 2) {
            $resource = $parameters[1];
        } else {
            throw new CentreonClapiException('Bad parameters');
        }
        $type = $parameters[0];

        return [
            'type' => $type,
            'resource' => $resource,
        ];
    }

    /**
     * show acknowledgement without option
     *
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
            $method = 'show' . ucfirst($parsedParameters['type']);
            $this->$method($parsedParameters['resource']);
        } else {
            $this->aHosts = $this->object->getLastHostAcknowledgement();
            $this->aServices = $this->object->getLastSvcAcknowledgement();
            $list = '';
            //all host
            if (count($this->aHosts) !== 0) {
                foreach ($this->aHosts as $host) {
                    $list .= $host['name'] . ";\n";
                }
            }

            //all service
            if (count($this->aServices) !== 0) {
                foreach ($this->aServices as $service) {
                    $list .= $service['name'] . ';' . $service['description'] . " \n";
                }
            }
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
            'id',
            'host_name',
            'entry_time',
            'author',
            'comment_data',
            'sticky',
            'notify_contacts',
            'persistent_comment',
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
            $unknownHost = array();
            $existingHostIds = array();
            foreach ($hostList as $host) {
                if (($hostId = $this->hostObject->getHostID($host)) == 0) {
                    $unknownHost[] = $host;
                } else {
                    $existingHostIds[] = $hostId;
                }
            }
            if (count($unknownHost) !== 0) {
                echo "\n";
                throw new CentreonClapiException(
                    self::OBJECT_NOT_FOUND . ' : Host : ' . implode('|', $unknownHost) . "\n"
                );
            }
            // Result of the research in the base
            $hostAcknowledgementList = $this->object->getLastHostAcknowledgement($existingHostIds);
        } else {
            $hostAcknowledgementList = $this->object->getLastHostAcknowledgement();
        }

        // Init user timezone
        $this->GMTObject->getMyGTMFromUser(CentreonUtils::getuserId());

        echo implode($this->delim, $fields) . "\n";
        //Separates hosts
        if (count($hostAcknowledgementList)) {
            foreach ($hostAcknowledgementList as $hostAcknowledgement) {
                $hostAcknowledgement['entry_time'] = $this->GMTObject->getDate(
                    'Y/m/d H:i',
                    $hostAcknowledgement['entry_time'],
                    $this->GMTObject->getMyGMT()
                );

                if ($hostAcknowledgement['sticky'] !== 0) {
                    $hostAcknowledgement['sticky'] = 2;
                }

                echo implode($this->delim, array_values($hostAcknowledgement)) . "\n";
            }
        }
    }

    /**
     * @param $svcList
     * @throws CentreonClapiException
     */
    public function showSvc($svcList)
    {
        $serviceAcknowledgementList = array();
        $unknownService = array();
        $existingService = array();

        $fields = array(
            'id',
            'host_name',
            'service_name',
            'entry_time',
            'author',
            'comment_data',
            'sticky',
            'notify_contacts',
            'persistent_comment',
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
                    $tmpAcknowledgement = $this->object->getLastSvcAcknowledgement($svc);
                    if (!empty($tmpAcknowledgement)) {
                        $serviceAcknowledgementList[] = array_pop($tmpAcknowledgement);
                    }
                }
            }
        } else {
            $serviceAcknowledgementList = $this->object->getLastSvcAcknowledgement();
        }

        // Init user timezone
        $this->GMTObject->getMyGTMFromUser(CentreonUtils::getuserId());

        //Separates hosts and services
        echo implode($this->delim, $fields) . "\n";

        if (count($serviceAcknowledgementList)) {
            foreach ($serviceAcknowledgementList as $serviceAcknowledgement) {
                $serviceAcknowledgement['entry_time'] = $this->GMTObject->getDate(
                    'Y/m/d H:i',
                    $serviceAcknowledgement['entry_time'],
                    $this->GMTObject->getMyGMT()
                );

                if ($serviceAcknowledgement['sticky'] !== 0) {
                    $serviceAcknowledgement['sticky'] = 2;
                }

                echo implode($this->delim, array_values($serviceAcknowledgement)) . "\n";
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
     * redirect on SVC or HOST
     *
     * @param null $parameters
     * @return mixed|void
     * @throws CentreonClapiException
     */
    public function add($parameters = null)
    {
        $parsedParameters = $this->parseParameters($parameters);

        // to choose the best add (addHostAcknowledgement, addSvcAcknowledgement.)
        $method = 'add' . ucfirst($parsedParameters['type']) . 'Acknowledgement';

        $this->$method(
            $parsedParameters['resource'],
            $parsedParameters['comment'],
            $parsedParameters['sticky'],
            $parsedParameters['notify'],
            $parsedParameters['persistent']
        );
    }

    /**
     * @param $resource
     * @param $comment
     * @param $sticky
     * @param $notify
     * @param $persistent
     * @throws CentreonClapiException
     */
    private function addHostAcknowledgement(
        $resource,
        $comment,
        $sticky,
        $notify,
        $persistent
    ) {
        if ($resource === "") {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $unknownHost = array();
        $listHost = explode('|', $resource);

        foreach ($listHost as $host) {
            if ($this->rtValidator->isHostNameValid($host)) {
                $this->externalCmdObj->acknowledgeHost(
                    $host,
                    $sticky,
                    $notify,
                    $persistent,
                    $this->author,
                    $comment
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
     * @param $comment
     * @param $sticky
     * @param $notify
     * @param $persistent
     * @throws CentreonClapiException
     */
    private function addSvcAcknowledgement(
        $resource,
        $comment,
        $sticky,
        $notify,
        $persistent
    ) {
        if ($resource === "") {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $unknownService = array();
        $existingService = array();
        $listService = explode('|', $resource);

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
                $this->externalCmdObj->acknowledgeService(
                    $service[0],
                    $service[1],
                    $sticky,
                    $notify,
                    $persistent,
                    $this->author,
                    $comment
                );
            }
        }
        if (count($unknownService)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ' SERVICE : ' . implode('|', $unknownService));
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
        $listAcknowledgement = explode('|', $parameters);
        $unknownAcknowledgement = array();

        foreach ($listAcknowledgement as $acknowledgement) {
            list($hostName, $serviceName) = explode(',', $acknowledgement);

            if ($serviceName) {
                $serviceId = $this->serviceObject->getObjectId($hostName . ";" . $serviceName);
                if (
                    $this->rtValidator->isServiceNameValid($hostName, $serviceName)
                    && $this->object->svcIsAcknowledged($serviceId)
                ) {
                    $this->externalCmdObj->deleteAcknowledgement(
                        'SVC',
                        array($hostName . ';' . $serviceName => 'on')
                    );
                } else {
                    $unknownAcknowledgement[] = $acknowledgement;
                }
            } else {
                $hostId = $this->hostObject->getHostID($hostName);
                if ($this->object->hostIsAcknowledged($hostId)) {
                    $this->externalCmdObj->deleteAcknowledgement(
                        'HOST',
                        array($hostName => 'on')
                    );
                } else {
                    $unknownAcknowledgement[] = $acknowledgement;
                }
            }
        }

        if (count($unknownAcknowledgement)) {
            throw new CentreonClapiException(
                self::OBJECT_NOT_FOUND . ' OR not acknowledged : ' . implode('|', $unknownAcknowledgement)
            );
        }
    }
}
