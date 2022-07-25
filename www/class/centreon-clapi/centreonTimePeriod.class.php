<?php
/*
 * Copyright 2005-2015 CENTREON
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
require_once __DIR__ . "/../../../lib/Centreon/Object/Timeperiod/Timeperiod.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Timeperiod/Exception.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Relation/Timeperiod/Exclude.php";
require_once __DIR__ . "/../../../lib/Centreon/Object/Relation/Timeperiod/Include.php";

class CentreonTimePeriod extends CentreonObject
{
    const ORDER_UNIQUENAME = 0;
    const ORDER_ALIAS = 1;
    const TP_INCLUDE = "include";
    const TP_EXCLUDE = "exclude";
    const TP_EXCEPTION = "exception";

    /**
     * @var Centreon_Relation_Timeperiod_Exclude
     */
    protected $exclude;

    /**
     * @var Centreon_Relation_Timeperiod_Exclude
     */
    protected $dependencyInjector;

    /**
     *
     * @var Centreon_Relation_Timeperiod_Include
     */
    protected $include;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->dependencyInjector = $dependencyInjector;
        $this->object = new \Centreon_Object_Timeperiod($dependencyInjector);
        $this->params = array(
            'tp_sunday' => '',
            'tp_monday' => '',
            'tp_tuesday' => '',
            'tp_wednesday' => '',
            'tp_thursday' => '',
            'tp_friday' => '',
            'tp_saturday' => ''
        );
        $this->insertParams = array("tp_name", "tp_alias");
        $this->exportExcludedParams = array_merge($this->insertParams, array($this->object->getPrimaryKey()));
        $this->action = "TP";
        $this->nbOfCompulsoryParams = count($this->insertParams);
    }

    /**
     * @param null $parameters
     * @param array $filters
     */
    public function show($parameters = null, $filters = array())
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%" . $parameters . "%");
        }
        $params = array(
            'tp_id',
            'tp_name',
            'tp_alias',
            'tp_sunday',
            'tp_monday',
            'tp_tuesday',
            'tp_wednesday',
            'tp_thursday',
            'tp_friday',
            'tp_saturday'
        );
        $paramString = str_replace("tp_", "", implode($this->delim, $params));
        echo $paramString . "\n";
        $elements = $this->object->getList(
            $params,
            -1,
            0,
            null,
            null,
            $filters
        );
        foreach ($elements as $tab) {
            $tab = array_map('html_entity_decode', $tab);
            $tab = array_map('utf8_encode', $tab);
            echo implode($this->delim, $tab) . "\n";
        }
    }

    /**
     * @param $parameters
     * @return mixed|void
     * @throws CentreonClapiException
     */
    public function initInsertParameters($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $this->checkIllegalChar($params[self::ORDER_UNIQUENAME]);
        $addParams['tp_alias'] = $params[self::ORDER_ALIAS];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
    }

    /**
     * @param $parameters
     * @return array
     * @throws CentreonClapiException
     */
    public function initUpdateParameters($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        $objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME]);
        if ($objectId != 0) {
            if ($params[1] == self::TP_INCLUDE || $params[1] == self::TP_EXCLUDE) {
                $this->setRelations($params[1], $objectId, $params[2]);
            } elseif (!preg_match("/^tp_/", $params[1])) {
                $params[1] = "tp_" . $params[1];
            }
            if ($params[1] != self::TP_INCLUDE && $params[1] != self::TP_EXCLUDE) {
                $updateParams = array($params[1] => $params[2]);
                $updateParams['objectId'] = $objectId;
                return $updateParams;
            }
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * Set Exception
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function setexception($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (($tpId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) == 0) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $excObj = new \Centreon_Object_Timeperiod_Exception($this->dependencyInjector);
        $escList = $excObj->getList(
            $excObj->getPrimaryKey(),
            -1,
            0,
            null,
            null,
            array(
                "timeperiod_id" => $tpId,
                "days" => $params[1]
            ),
            "AND"
        );
        if (count($escList)) {
            $excObj->update($escList[0][$excObj->getPrimaryKey()], array('timerange' => $params[2]));
        } else {
            $excObj->insert(array(
                'timeperiod_id' => $tpId,
                'days' => $params[1],
                'timerange' => $params[2]
            ));
        }
    }

    /**
     * Delete exception
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function delexception($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (($tpId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) == 0) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $excObj = new \Centreon_Object_Timeperiod_Exception($this->dependencyInjector);
        $escList = $excObj->getList(
            $excObj->getPrimaryKey(),
            -1,
            0,
            null,
            null,
            array(
                "timeperiod_id" => $tpId,
                "days" => $params[1]
            ),
            "AND"
        );
        if (count($escList)) {
            $excObj->delete($escList[0][$excObj->getPrimaryKey()]);
        }
    }

    /**
     * Get exception
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function getexception($parameters)
    {
        if (($tpId = $this->getObjectId($parameters)) == 0) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $parameters);
        }
        $excObj = new \Centreon_Object_Timeperiod_Exception($this->dependencyInjector);
        $escList = $excObj->getList(array("days", "timerange"), -1, 0, null, null, array("timeperiod_id" => $tpId));
        echo "days;timerange\n";
        foreach ($escList as $exc) {
            echo $exc['days'] . $this->delim . $exc['timerange'] . "\n";
        }
    }

    /**
     * Get Timeperiod Id
     *
     * @param string $name
     * @return int
     * @throws CentreonClapiException
     */
    public function getTimeperiodId($name)
    {
        $tpIds = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($name));
        if (!count($tpIds)) {
            throw new CentreonClapiException("Unknown timeperiod: " . $name);
        }
        return $tpIds[0];
    }

    /**
     * Get timeperiod name
     *
     * @param int $timeperiodId
     * @return string
     */
    public function getTimeperiodName($timeperiodId)
    {
        $tpName = $this->object->getParameters($timeperiodId, array($this->object->getUniqueLabelField()));
        return $tpName[$this->object->getUniqueLabelField()];
    }

    /**
     * Set Include / Exclude relations
     *
     * @param int $relationType
     * @param string $sourceName
     * @param string $relationName
     * @return void
     */
    protected function setRelations($relationType, $sourceId, $relationName)
    {
        $relationIds = array();
        $relationNames = explode("|", $relationName);
        foreach ($relationNames as $name) {
            $name = trim($name);
            $relationIds[] = $this->getTimePeriodId($name);
        }
        if ($relationType == self::TP_INCLUDE) {
            $relObj = new \Centreon_Object_Relation_Timeperiod_Include($this->dependencyInjector);
        } else {
            $relObj = new \Centreon_Object_Relation_Timeperiod_Exclude($this->dependencyInjector);
        }
        $relObj->delete($sourceId);
        foreach ($relationIds as $relId) {
            $relObj->insert($sourceId, $relId);
        }
    }
}
