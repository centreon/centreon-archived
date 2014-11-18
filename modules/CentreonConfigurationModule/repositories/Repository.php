<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonConfiguration\Repository;

use Centreon\Internal\Di;
use Centreon\Internal\Exception;
use Centreon\Repository\FormRepository;
use CentreonConfiguration\Repository\AuditlogRepository;

/**
 * Abstact class for configuration repository
 *
 * @version 3.0.0
 * @author Sylvestre Ho <sho@merethis.com>
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
        if (isset($_SESSION['user'])) {
            AuditlogRepository::addLog(
                $actionList[$action],
                static::$objectName,
                $id,
                $name,
                array()
            );
        }
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
        if (isset($_SESSION['user'])) {
            AuditlogRepository::addLog(
                $actionList[$action],
                static::$objectName,
                $id,
                $name,
                $params
            );
        }
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
