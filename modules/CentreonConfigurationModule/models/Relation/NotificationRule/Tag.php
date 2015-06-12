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
 *
 */

namespace CentreonConfiguration\Models\Relation\NotificationRule;

use Centreon\Internal\Di;
use Centreon\Models\CentreonRelationModel;

class Tag extends CentreonRelationModel
{
    protected static $resourceType = null;
    protected static $relationTable = "cfg_notification_rules_tags_relation";
    protected static $firstKey = "rule_id";
    protected static $secondKey = "tag_id";
    public static $firstObject = '\CentreonConfiguration\Models\NotificationRule';
    public static $secondObject = '\CentreonAdministration\Models\Tag';

    public static function insert($fkey, $skey, $extra = array())
    {
        if (is_null(static::$resourceType)) {
            throw new \Exception("Bad resource type.");
        }
        parent::insert($fkey, $skey, array('resource_type' => static::$resourceType));
    }

    public static function delete($fkey, $skey = null)
    {
        if (!is_null($fkey) && !is_null($skey)) {
            $sql = "DELETE FROM " . static::$relationTable .
                " WHERE " . static::$firstKey . " = ? AND " . static::$secondKey . " = ? AND resource_type = ?";
            $args = array($fkey, $skey, static::$resourceType);
        } elseif (!is_null($skey)) {
            $sql = "DELETE FROM " . static::$relationTable . " WHERE ". static::$secondKey . " = ? AND resource_type = ?";
            $args = array($skey, static::$resourceType);
        } else {
            $sql = "DELETE FROM " . static::$relationTable . " WHERE " . static::$firstKey . " = ? AND resource_type = ?";
            $args = array($fkey, static::$resourceType);
        }
        $db = Di::getDefault()->get(static::$databaseName);
        $stmt = $db->prepare($sql);
        $stmt->execute($args);
    }

    public static function getMergedParameters(
        $firstTableParams = array(),
        $secondTableParams = array(),
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR",
        $relationTableParams = array()
    ) {
        if (is_null(static::$resourceType)) {
            throw new \Exception("Bad resource type.");
        }
        $filters = array_merge($filters, array('resource_type' => static::$resourceType));
        return parent::getMergedParameters($firstTableParams, $secondTableParams, $count, $offset, $order, $sort, $filters, 'AND', $relationTableParams);
    }

    public static function getMergedParametersBySearch(
        $firstTableParams = array(),
        $secondTableParams = array(),
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR",
        $relationTableParams = array(),
        $aAddFilters = array(),
        $aGroup = array()
    ) {
        if (is_null(static::$resourceType)) {
            throw new \Exception("Bad resource type.");
        }
        $filters = array_merge($filters, array('resource_type' => static::$resourceType));
        return parent::getMergedParametersBySearch($firstTableParams, $secondTableParams, $count, $offset, $order, $sort, $filters, 'AND', $relationTableParams, $aAddFilters, $aGroup);
    }

    public static function getTargetIdFromSourceId($targetKey, $sourceKey, $sourceId)
    {
        if (!is_array($sourceId)) {
            $sourceId = array($sourceId);
        }
        $sql = "SELECT $targetKey FROM " . static::$relationTable . " WHERE $sourceKey = ? AND resource_type = ?";
        $sourceId += static::$resourceType;
        $result = static::getResult($sql, $sourceId);
        $tab = array();
        foreach ($result as $rez) {
            $tab[] = $rez[$targetKey];
        }
        return $tab;
    }
}
