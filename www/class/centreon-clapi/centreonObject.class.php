<?php

/*
 * Copyright 2005-2021 CENTREON
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

require_once "centreonAPI.class.php";
require_once _CLAPI_LIB_ . "/Centreon/Object/Contact/Contact.php";
require_once "centreonClapiException.class.php";
require_once _CENTREON_PATH_ . "www/class/centreon-clapi/centreonExported.class.php";

abstract class CentreonObject
{
    public const MISSINGPARAMETER = "Missing parameters";
    public const MISSINGNAMEPARAMETER = "Missing name parameter";
    public const OBJECTALREADYEXISTS = "Object already exists";
    public const OBJECT_NOT_FOUND = "Object not found";
    public const UNKNOWN_METHOD = "Method not implemented into Centreon API";
    public const NAMEALREADYINUSE = "Name is already in use";
    public const NB_UPDATE_PARAMS = 3;
    public const UNKNOWNPARAMETER = "Unknown parameter";
    public const OBJECTALREADYLINKED = "Objects already linked";
    public const OBJECTNOTLINKED = "Objects are not linked";
    public const SINGLE_VALUE = 0;
    public const MULTIPLE_VALUE = 1;


    private $centreon_api = null;
    protected static $instances;

    /**
     * Db object provided by dependency injector
     *
     * @var CentreonDB
     */
    protected $db;

    /**
     * @var
     */
    protected $dependencyInjector;

    /**
     * Version of Centreon
     *
     * @var string
     */
    protected $version;
    /**
     * Centreon Configuration object type
     *
     * @var Centreon_Object
     */
    protected $object;
    /**
     * Default params
     *
     * @var array
     */
    protected $params;
    /**
     * Number of compulsory parameters when adding a new object
     *
     * @var int
     */
    protected $nbOfCompulsoryParams;
    /**
     * Delimiter
     *
     * @var string
     */
    protected $delim;
    /**
     * Table column used for activating and deactivating object
     *
     * @var string
     */
    protected $activateField;
    /**
     * Export : Table columns that are used for 'add' action
     *
     * @var array
     */
    protected $insertParams;
    /**
     * Export : Table columns which will not be exported for 'setparam' action
     *
     * @var array
     */
    protected $exportExcludedParams;
    /**
     * cache to store object ids by object names
     *
     * @var array
     */
    protected $objectIds = array();

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        $this->db = $dependencyInjector['configuration_db'];
        $this->dependencyInjector = $dependencyInjector;
        $res = $this->db->query("SELECT `value` FROM informations WHERE `key` = 'version'");
        $row = $res->fetch();
        $this->version = $row['value'];
        $this->params = array();
        $this->insertParams = array();
        $this->exportExcludedParams = array();
        $this->action = "";
        $this->delim = ";";
        $this->api = CentreonAPI::getInstance();
    }

    /**
     * @return Centreon_Object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param $dependencyInjector
     */
    public function setDependencyInjector($dependencyInjector)
    {
        $this->dependencyInjector = $dependencyInjector;
    }

    /**
     * Get Centreon Version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Checks if object exists
     *
     * @param string $name
     * @return bool
     */
    protected function objectExists($name, $updateId = null)
    {
        $ids = $this->object->getList(
            $this->object->getPrimaryKey(),
            -1,
            0,
            null,
            null,
            array(
                $this->object->getUniqueLabelField() => $name
            ),
            "AND"
        );
        if (isset($updateId) && count($ids)) {
            if ($ids[0][$this->object->getPrimaryKey()] == $updateId) {
                return false;
            } else {
                return true;
            }
        } elseif (count($ids)) {
            return true;
        }
        return false;
    }

    /**
     * Get Object Id
     *
     * @param string $name
     * @return int
     */
    public function getObjectId($name, int $type = self::SINGLE_VALUE)
    {
        if (isset($this->objectIds[$name])) {
            return $this->objectIds[$name];
        }
        $ids = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($name));
        if (count($ids)) {
            $this->objectIds[$name] = ($type === self::SINGLE_VALUE)
                ? $ids[0]
                : $ids;
            return $this->objectIds[$name];
        }
        return 0;
    }

    /**
     * Get Object Name
     *
     * @param int $id
     * @return string
     */
    public function getObjectName($id)
    {
        $tmp = $this->object->getParameters($id, array($this->object->getUniqueLabelField()));
        if (isset($tmp[$this->object->getUniqueLabelField()])) {
            return $tmp[$this->object->getUniqueLabelField()];
        }
        return "";
    }

    /**
     * Catch the beginning of the URL
     *
     * @return string
     *
     */
    public function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? "https" : "http";
        $port = '';
        if (
            ($protocol == 'http' && $_SERVER['SERVER_PORT'] != 80)
            || ($protocol == 'https' && $_SERVER['SERVER_PORT'] != 443)
        ) {
            $port = ':' . $_SERVER['SERVER_PORT'];
        }
        $uri = 'centreon';
        if (preg_match('/^(.+)\/api/', $_SERVER['REQUEST_URI'], $matches)) {
            $uri = $matches[1];
        }

        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $port . $uri;
    }

    /**
     * @throws CentreonClapiException
     */
    protected function checkParameters()
    {
        if (!isset($this->params[$this->object->getUniqueLabelField()])) {
            throw new CentreonClapiException(self::MISSINGNAMEPARAMETER);
        }
        if ($this->objectExists($this->params[$this->object->getUniqueLabelField()]) === true) {
            throw new CentreonClapiException(
                self::OBJECTALREADYEXISTS . " ("
                . $this->params[$this->object->getUniqueLabelField()] . ")"
            );
        }
    }

    /**
     * @param $parameters
     * @return mixed
     */
    public function add($parameters)
    {
        $this->initInsertParameters($parameters);

        $id = $this->object->insert($this->params);
        $this->addAuditLog(
            'a',
            $id,
            $this->params[$this->object->getUniqueLabelField()],
            $this->params
        );

        if (method_exists($this, "insertRelations")) {
            $this->insertRelations($id);
        }
    }


    /**
     * @param $parameters
     * @return mixed
     */
    public function initInsertParameters($parameters)
    {
        return $parameters;
    }

    /**
     * Del Action
     *
     * @param string $objectName
     * @return void
     * @throws CentreonClapiException
     */
    public function del($objectName)
    {
        $ids = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($objectName));
        if (count($ids)) {
            $this->object->delete($ids[0]);
            $this->addAuditLog('d', $ids[0], $objectName);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $objectName);
        }
    }

    /**
     * Get a parameter
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function getparam($parameters = null)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $p = $this->object->getParameters($params[0], $params[1]);
        print $p[$params[1]] . "\n";
    }

    /**
     * @param array $parameters
     * @throws CentreonClapiException
     */
    public function setparam($parameters = array())
    {
        if (method_exists($this, "initUpdateParameters")) {
            $params = $this->initUpdateParameters($parameters);
        } else {
            $params = $parameters;
        }

        if (!empty($params)) {
            $uniqueLabel = $this->object->getUniqueLabelField();
            $objectId = $params['objectId'];
            unset($params['objectId']);

            if (
                isset($params[$uniqueLabel])
                && $this->objectExists($params[$uniqueLabel], $objectId) == true
            ) {
                throw new CentreonClapiException(self::NAMEALREADYINUSE);
            }

            $this->object->update($objectId, $params);
            $p = $this->object->getParameters($objectId, $uniqueLabel);
            if (isset($p[$uniqueLabel])) {
                $this->addAuditLog(
                    'c',
                    $objectId,
                    $p[$uniqueLabel],
                    $params
                );
            }
        }
    }

    /**
     * Shows list
     *
     * @param array $params
     * @param array $filters
     * @return void
     */
    public function show($params = array(), $filters = array())
    {
        echo str_replace("_", " ", implode($this->delim, $params)) . "\n";
        $elements = $this->object->getList(
            $params,
            -1,
            0,
            null,
            null,
            $filters
        );
        foreach ($elements as $tab) {
            echo implode($this->delim, $tab) . "\n";
        }
    }

    /**
     * Set the activate field
     *
     * @param string $objectName
     * @param int $value
     * @throws CentreonClapiException
     */
    protected function activate($objectName, $value)
    {
        if (!isset($objectName) || !$objectName) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (isset($this->activateField)) {
            $ids = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($objectName));
            if (count($ids)) {
                $this->object->update($ids[0], array($this->activateField => $value));
            } else {
                throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $objectName);
            }
        } else {
            throw new CentreonClapiException(self::UNKNOWN_METHOD);
        }
    }

    /**
     * Enable object
     *
     * @param string $objectName
     * @return void
     */
    public function enable($objectName)
    {
        $this->activate($objectName, '1');
    }

    /**
     * Disable object
     *
     * @param string $objectName
     * @return void
     */
    public function disable($objectName)
    {
        $this->activate($objectName, '0');
    }

    protected function canBeExported($filterName = null)
    {
        $exported = CentreonExported::getInstance();

        if (is_null($this->action)) {
            return false;
        }

        if (is_null($filterName)) {
            return true;
        }

        $filterId = $this->getObjectId($filterName);
        $filterIds = is_array($filterId) ? $filterId : [$filterId];
        foreach ($filterIds as $filterId) {
            $exported->ariane_push($this->action, $filterId, $filterName);
            if ($exported->is_exported($this->action, $filterId, $filterName)) {
                $exported->ariane_pop();
                return false;
            }
        }

        return true;
    }

    /**
     * Export from a specific object
     *
     * @param $filterName
     * @return bool
     */
    public function export($filterName = null)
    {
        if (!$this->canBeExported($filterName)) {
            return false;
        }

        $filterId = $this->getObjectId($filterName);
        $labelField = $this->getObject()->getUniqueLabelField();

        $filters = array();
        if (!is_null($filterId) && $filterId !== 0) {
            $primaryKey = $this->getObject()->getPrimaryKey();
            $filters[$primaryKey] = $filterId;
        }
        if (!is_null($filterName)) {
            $filters[$labelField] = $filterName;
        }

        $elements = $this->object->getList(
            "*",
            -1,
            0,
            $labelField,
            'ASC',
            $filters,
            "AND"
        );
        foreach ($elements as $element) {
            $addStr = $this->action . $this->delim . "ADD";
            foreach ($this->insertParams as $param) {
                $element[$param] = CentreonUtils::convertLineBreak($element[$param]);
                $addStr .= $this->delim . $element[$param];
            }
            $addStr .= "\n";
            echo $addStr;
            foreach ($element as $parameter => $value) {
                if (!in_array($parameter, $this->exportExcludedParams)) {
                    if (!is_null($value) && $value != "") {
                        $value = CentreonUtils::convertLineBreak($value);
                        echo $this->action . $this->delim
                            . "setparam" . $this->delim
                            . $element[$this->object->getUniqueLabelField()] . $this->delim
                            . $parameter . $this->delim
                            . $value . "\n";
                    }
                }
            }
        }

        CentreonExported::getInstance()->ariane_pop();
        return true;
    }

    /**
     * Insert audit log
     *
     * @param $actionType
     * @param $objId
     * @param $objName
     * @param array $objValues
     * @param null $objectType
     * @return null
     * @throws CentreonClapiException
     */
    public function addAuditLog($actionType, $objId, $objName, $objValues = array(), $objectType = null)
    {
        $objType = is_null($objectType) ? strtoupper($this->action) : $objectType;
        $objectTypes = array(
            'HTPL' => 'host',
            'STPL' => 'service',
            'CONTACT' => 'contact',
            'SG' => 'servicegroup',
            'TP' => 'timeperiod',
            'SERVICE' => 'service',
            'CG' => 'contactgroup',
            'CMD' => 'command',
            'HOST' => 'host',
            'HC' => 'hostcategories',
            'HG' => 'hostgroup',
            'SC' => 'servicecategories'
        );
        if (!isset($objectTypes[$objType])) {
            return null;
        }
        $objType = $objectTypes[$objType];

        $contactObj = new \Centreon_Object_Contact($this->dependencyInjector);
        $contact = $contactObj->getIdByParameter('contact_alias', CentreonUtils::getUserName());
        $userId = $contact[0];

        $dbstorage = $this->dependencyInjector['realtime_db'];
        $query = 'INSERT INTO log_action
            (action_log_date, object_type, object_id, object_name, action_type, log_contact_id)
            VALUES (?, ?, ?, ?, ?, ?)';
        $time = time();

        $dbstorage->query($query, array(
            $time,
            $objType,
            $objId,
            $objName,
            $actionType,
            $userId
        ));

        $query = 'SELECT LAST_INSERT_ID() as action_log_id';
        $stmt = $dbstorage->query($query);
        $row = $stmt->fetch();
        if (false === $row) {
            throw new CentreonClapiException("Error while inserting log action");
        }
        $stmt->closeCursor();
        $actionId = $row['action_log_id'];

        $query = 'INSERT INTO log_action_modification
            (field_name, field_value, action_log_id)
            VALUES (?, ?, ?)';
        foreach ($objValues as $name => $value) {
            try {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                if (is_null($value)) {
                    $value = '';
                }
                $dbstorage->query(
                    $query,
                    array(
                        $name,
                        $value,
                        $actionId
                    )
                );
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }


    /**
     * Check illegal char defined into nagios.cfg file
     *
     * @param string $name The string to sanitize
     * @return string The string sanitized
     */
    public function checkIllegalChar($name)
    {
        $dbResult = $this->db->query("SELECT illegal_object_name_chars FROM cfg_nagios");
        while ($data = $dbResult->fetch()) {
            $tab = str_split(html_entity_decode($data['illegal_object_name_chars'], ENT_QUOTES, "UTF-8"));
            foreach ($tab as $char) {
                $name = str_replace($char, "", $name);
            }
        }
        $dbResult->closeCursor();
        return $name;
    }

    /**
     * @param null $dependencyInjector
     * @return mixed
     */
    public static function getInstance($dependencyInjector = null)
    {
        $class = get_called_class();

        if (is_null($dependencyInjector)) {
            $dependencyInjector = loadDependencyInjector();
        }

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class($dependencyInjector);
        }

        return self::$instances[$class];
    }
}
