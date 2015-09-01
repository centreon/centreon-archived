<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonConfiguration\Repository;

use Centreon\Internal\Di;
use Centreon\Internal\Exception;
use CentreonMain\Repository\FormRepository;
use CentreonConfiguration\Repository\AuditlogRepository;

/**
 * Abstact class for configuration repository
 *
 * @version 3.0.0
 * @author Sylvestre Ho <sho@centreon.com>
 */
abstract class Repository extends FormRepository
{
    /**
     * Action before save
     *
     * * Emit event objectName.action
     *
     * @param $id int The object id
     * @param $action string The action (add, update, delete)
     */
    protected static function preSave($id, $action = 'add')
    {
        $actionList = array(
            'delete' => 'd'
        );
        if (false === in_array($action, array_keys($actionList))) {
            return;
        }
        $objClass = static::$objectClass;
        $name = $objClass::getParameters($id, $objClass::getUniqueLabelField());
        $name = $name[$objClass::getUniqueLabelField()];
        /* Add change log */
        /*if (isset($_SESSION['user'])) {
            AuditlogRepository::addLog(
                $actionList[$action],
                static::$objectName,
                $id,
                $name,
                array()
            );
        }*/
    }

    /**
     * Action after save
     *
     * * Emit event objectName.action
     *
     * @param $id int The object id
     * @param $action string The action (add, update, delete)
     * @param array $params
     */
    protected static function postSave($id, $action = 'add', $params = array())
    {
        $actionList = array(
            'add' => 'a',
            'update' => 'c'
        );
        $di = Di::getDefault();
        $event = $di->get('events');
        $eventParams = array(
            'id' => $id,
            'params' => $params
        );
        $event->emit(static::$objectName . '.' . $action, $eventParams);
        /* Add change log */
        if (false === in_array($action, array_keys($actionList))) {
            return;
        }
        $objClass = static::$objectClass;
        $name = $objClass::getParameters($id, $objClass::getUniqueLabelField());
        $name = $name[$objClass::getUniqueLabelField()];
        /*if (isset($_SESSION['user'])) {
            AuditlogRepository::addLog(
                $actionList[$action],
                static::$objectName,
                $id,
                $name,
                $params
            );
        }*/
    }

    /**
     * Get object name
     *
     * @param string $objectType
     * @param int $objectId
     * @return string
     */
    protected static function getObjectName($objectType, $objectId)
    {
        if ($objectId) {
            $field = $objectType::getUniqueLabelField();
            $object = $objectType::getParameters($objectId, $field);
            if (isset($object[$field])) {
                return $object[$field];
            }
        }
        return "";
    }
}
