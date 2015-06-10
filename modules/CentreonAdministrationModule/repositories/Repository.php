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
