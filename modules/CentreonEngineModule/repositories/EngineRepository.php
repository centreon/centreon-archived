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

namespace CentreonEngine\Repository;

use CentreonMain\Repository\FormRepository;

use Centreon\Internal\Di;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package CentreonEngine
 * @subpackage Repository
 */
class EngineRepository extends FormRepository
{
    /**
     * @var string
     */
    private static $engineModel = '\CentreonEngine\Models\Engine';

    /**
     * Save engine parameters of a poller
     *
     * @param int $pollerId
     * @param array $params
     */
    public static function save($pollerId, $params, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
         if ($validate) {
            self::validateForm($params, $origin, $route, $validateMandatory);
        }
        
        $db = Di::getDefault()->get('db_centreon');

        /* Get engine id, if it does not exist, insert it */
        $stmt = $db->prepare("SELECT poller_id FROM cfg_engine WHERE poller_id = ?");
        $stmt->execute(array($pollerId));
        if (!$stmt->rowCount()) {
            $stmt = $db->prepare("INSERT INTO cfg_engine (poller_id) VALUES (?)");
            $stmt->execute(array($pollerId));
        }

        /* Update parameters of engine only if the parameter name matches the column name */
        $sqlParams = array(':poller_id' => $pollerId);
        $updateSql = "";
        $model = static::$engineModel;
        $columns = $model::getColumns();
        foreach ($params as $k => $v) {
            if (!in_array($k, $columns)) {
                continue;
            }
            if (is_string($v)) {
                $v = trim($v);
            }
            if (empty($v)) {
                $v = null;
            }
            
            $newkey = ':' . $k;
            $sqlParams[$newkey] = $v;
            if ($updateSql != "") {
                $updateSql .= ', ';
            }
            $updateSql .= "{$k} = :{$k}";
        }
        if ($updateSql) {
            $sql = "UPDATE cfg_engine SET {$updateSql} WHERE poller_id = :poller_id";
            $stmt = $db->prepare($sql);
            $stmt->execute($sqlParams);
        }
    }

    /**
     * Get directories from poller id
     *
     * @param int $pollerId
     * @return array
     */
    public static function getDirectories($pollerId)
    {
        $sql = "SELECT conf_dir, log_dir, var_lib_dir, module_dir 
            FROM cfg_engine
            WHERE poller_id = :poller_id";
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute(array(':poller_id' => $pollerId));
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data;
    }
}
