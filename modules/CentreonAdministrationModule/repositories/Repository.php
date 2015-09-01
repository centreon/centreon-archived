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

namespace CentreonAdministration\Repository;

use CentreonMain\Repository\FormRepository;
use Centreon\Internal\Di;
use Centreon\Internal\Exception;
use CentreonAdministration\Repository\AuditlogRepository;
use Centreon\Internal\CentreonSlugify;

/**
 * Abstact class for administration repository
 *
 * @version 3.0.0
 * @author Sylvestre Ho <sho@centreon.com>
 */
abstract class Repository extends FormRepository
{
    /**
     * Generic create action
     *
     * @param array $givenParameters
     * @return int id of created object
     */
    /*public static function create($givenParameters)
    {
        $class = static::$objectClass;
        $pk = $class::getPrimaryKey();
        $db = Di::getDefault()->get('db_centreon');
        $columns = $class::getColumns();
        $insertParams = array();
        $givenParameters[static::ORGANIZATION_FIELD] = Di::getDefault()->get('organization');
        
        $sField = $class::getUniqueLabelField();
        if (isset($givenParameters[$sField])) {
            $oSlugify = new CentreonSlugify($class, get_called_class());
            $sSlug = $oSlugify->slug($givenParameters[$sField]);
            $givenParameters[$class::getSlugField()] = $sSlug;
        }

        foreach ($givenParameters as $key => $value) {
            if (in_array($key, $columns)) {
                if (!is_array($value)) {
                    $value = trim($value);
                    if (!empty($value)) {
                        $insertParams[$key] = trim($value);
                    }
                }
            }
        }
        
        $id = $class::insert($insertParams);
        if (is_null($id)) {
            throw new Exception('Could not create object');
        }
        foreach (static::$relationMap as $k => $rel) {
            if (!isset($givenParameters[$k])) {
                continue;
            }
            $arr = explode(',', ltrim($givenParameters[$k], ','));
            $db->beginTransaction();

            foreach ($arr as $relId) {
                $relId = trim($relId);
                if (is_numeric($relId)) {
                    if ($rel::$firstObject == static::$objectClass) {
                        $rel::insert($id, $relId);
                    } else {
                        $rel::insert($relId, $id);
                    }
                } elseif (!empty($relId)) {
                    $complexeRelId = explode('_', $relId);
                    if ($rel::$firstObject == static::$objectClass) {
                        $rel::insert($id, $complexeRelId[1], $complexeRelId[0]);
                    }
                }
            }
            $db->commit();
            unset($givenParameters[$k]);
        }
        return $id;
    }*/
}
