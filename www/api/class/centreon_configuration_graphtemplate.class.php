<?php
/*
 * Copyright 2005-2015 Centreon
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
 * this program; if not, see <htcontact://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once dirname(__FILE__) . "/centreon_configuration_objects.class.php";

class CentreonConfigurationGraphtemplate extends CentreonConfigurationObjects
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @return array
     */
    public function getList()
    {
        $queryValues = array();
        // Check for select2 'q' argument
        if (false === isset($this->arguments['q'])) {
            $q = '';
        } else {
            $q = $this->arguments['q'];
        }

        $query = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT graph_id, name ' .
            'FROM giv_graphs_template ' .
            'WHERE name LIKE ? ';
        $queryValues[] = (string)'%' . $q . '%';

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            $limit = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $range = 'LIMIT ?, ?';
            $queryValues[] = (int)$limit;
            $queryValues[] = (int)$this->arguments['page_limit'];
        } else {
            $range = '';
        }
        $query .= 'ORDER BY name ' . $range;
        $stmt = $this->pearDB->prepare($query);
        $dbResult = $this->pearDB->execute($stmt, $queryValues);
        $total = $this->pearDB->numberRows();
        $serviceList = array();
        while ($data = $dbResult->fetchRow()) {
            $serviceList[] = array(
                'id' => $data['graph_id'],
                'text' => $data['name']
            );
        }

        return array(
            'items' => $serviceList,
            'total' => $total
        );
    }
}
