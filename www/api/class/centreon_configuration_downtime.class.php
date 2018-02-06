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
 * this program; if not, see <http://www.gnu.org/licenses>.
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

require_once _CENTREON_PATH_ . "/www/class/centreonBroker.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonDowntime.class.php";
require_once dirname(__FILE__) . "/centreon_configuration_objects.class.php";

class CentreonConfigurationDowntime extends CentreonConfigurationObjects
{

    /**
     *
     * @var type
     */
    protected $pearDBMonitoring;

    /**
     *
     */
    public function __construct()
    {
        global $pearDBO;

        parent::__construct();
        $brk = new CentreonBroker($this->pearDB);
        $this->pearDBMonitoring = new CentreonDB('centstorage');
        $pearDBO = $this->pearDBMonitoring;
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

        $queryDowntime = "SELECT SQL_CALC_FOUND_ROWS DISTINCT dt.dt_name, dt.dt_id "
            . "FROM downtime dt "
            . "WHERE dt.dt_name LIKE ? "
            . "ORDER BY dt.dt_name";
        $queryValues[] = (string)'%' . $q . '%';

        $stmt = $this->pearDB->prepare($queryDowntime);
        $dbResult = $this->pearDB->execute($stmt, $queryValues);

        $total = $this->pearDB->numberRows();
        $downtimeList = array();
        while ($data = $dbResult->fetchRow()) {
            $downtimeList[] = array(
                'id' => htmlentities($data['dt_id']),
                'text' => $data['dt_name']
            );
        }

        return array(
            'items' => $downtimeList,
            'total' => $total
        );
    }
}
