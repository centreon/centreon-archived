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
 *
 */

namespace CentreonPerformance\Repository;

use Centreon\Repository\FormRepository,
    Centreon\Internal\Di;

/**
 * Repository for template graph
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @version 3.0.0
 * @package Centreon
 */
class GraphTemplate extends FormRepository
{
    /**
     * Save the curves for graph template
     *
     * @param int $id The graph template id
     * @param string $action The action
     * @param array $params The parameters to save
     */
    protected static function postSave($id, $action = 'add', $params = array())
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        if ($action == 'update') {
            $query = "DELETE FROM cfg_curve_config WHERE graph_template_id = :tmpl_id";
            $stmt = $dbconn->prepare($query);
            $stmt->bindValue(':tmpl_id', $id, \PDO::PARAM_INT);
            $stmt->execute();
        }
        $query = "INSERT INTO cfg_curve_config (graph_template_id, metric_name, color, is_negative)
            VALUES (:tmpl_id, :name, :color, :neg)";
        $stmt = $dbconn->prepare($query);
        /* Insert metrics */
        for ($i = 1; $i < count($params['metric_id']); $i++) {
            $negative = 0;
            if (isset($params['negative'][$i])) {
                $negative = $params['negative'][$i];
            }
            $stmt->bindValue(':tmpl_id', $id, \PDO::PARAM_INT);
            $stmt->bindValue(':name', $params['metric_id'][$i], \PDO::PARAM_STR);
            $stmt->bindValue(':color', $params['color'][$i], \PDO::PARAM_STR);
            $stmt->bindValue(':neg', $negative, \PDO::PARAM_INT);
            $stmt->execute();
        }
    } 

    /**
     * Get the list of metrics of a graph template
     *
     * @param int $id The graph template id
     * @return array
     */
    public static function getMetrics($id)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $query = "SELECT metric_name, color, is_negative
            FROM cfg_curve_config
            WHERE graph_template_id = :id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $metrics = array();
        while ($row = $stmt->fetch()) {
            $metrics[] = $row;
        }
        return $metrics;
    }
}
