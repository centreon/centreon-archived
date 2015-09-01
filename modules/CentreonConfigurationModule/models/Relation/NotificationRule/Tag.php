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

namespace CentreonConfiguration\Models\Relation\NotificationRule;

use Centreon\Internal\Di;
use Centreon\Models\CentreonRelationModel;

class Tag extends CentreonRelationModel
{
    protected static $resourceType = null;
    protected static $relationTable = "cfg_notification_rules_tags_relations";
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
