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

namespace CentreonConfiguration\Models;

use Centreon\Models\CentreonBaseModel;

/**
 * Used for interacting with pollers
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @package Centreon
 * @subpackage Configuration
 * @version 3.0.0
 */
class Poller extends CentreonBaseModel
{
    protected static $table = "cfg_pollers p";
    protected static $primaryKey = "poller_id";
    protected static $uniqueLabelField = "name";

    /**
     *
     * @param type $parameterNames
     * @param type $count
     * @param type $offset
     * @param type $order
     * @param type $sort
     * @param array $filters
     * @param type $filterType
     * @return type
     */
    public static function getList(
        $parameterNames = "*",
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR"
    ) {
        return parent::getList($parameterNames, $count, $offset, $order, $sort, $filters, $filterType);
    }

    /**
     *
     * @param type $parameterNames
     * @param type $count
     * @param type $offset
     * @param type $order
     * @param type $sort
     * @param array $filters
     * @param type $filterType
     * @return type
     */
    public static function getListBySearch(
        $parameterNames = "*",
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR"
    ) {
        $aAddFilters = array();
        $tablesString =  null;
        $aGroup = array();

        // Add join on node table
        if (isset($filters['ip_address']) && !empty($filters['ip_address'])) {
            $aAddFilters['tables'][] = 'cfg_nodes n';
            $aAddFilters['join'][] = 'p.node_id = n.node_id';
        }

        // Add join on instance table
        if ((isset($filters['running']) && !empty($filters['running']))
            || (isset($filters['version']) && !empty($filters['version']))
        ) {
            $aAddFilters['tables'][] = 'rt_instances i';
            $aAddFilters['join'][] = 'p.name = i.name';
        }

        // Avoid error on ambiguous column
        if (isset($filters['name'])) {
            $filters['p.name'] = $filters['name'];
            unset($filters['name']);
        }

        // Avoid error on ambiguous column
        if (isset($filters['enable'])) {
            $filters['p.enable'] = $filters['enable'];
            unset($filters['enable']);
        }

        return parent::getList($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, $tablesString, null, $aAddFilters, $aGroup);
    }
}
