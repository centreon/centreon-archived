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

class CentreonConfigurationServicecategory extends CentreonConfigurationObjects
{
    /**
     * CentreonConfigurationServicecategory constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getList()
    {
        $queryValues = array();
        // Check for select2 'q' argument
        if (false === isset($this->arguments['q'])) {
            $queryValues['name'] = '%%';
        } else {
            $queryValues['name'] = '%' . (string)$this->arguments['q'] . '%';
        }

        /*
		 * Check for select2 't' argument
		 * 'a' or empty = category and severitiy
		 * 'c' = catagory only
		 * 's' = severity only
		 */
        if (false === isset($this->arguments['t'])) {
            $t = '';
        } else {
            $t = $this->arguments['t'];
        }

        $queryContact = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT sc_id, sc_name ' .
            'FROM service_categories ' .
            'WHERE sc_name LIKE :name ';
        if ($t == 'c') {
            $queryContact .= "AND level IS NULL ";
        }
        if ($t == 's') {
            $queryContact .= "AND level IS NOT NULL ";
        }
        $queryContact .= 'ORDER BY sc_name ';

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $queryContact .= 'LIMIT :offset,:limit';
            $queryValues['offset'] = (int)$offset;
            $queryValues['limit'] = (int)$this->arguments['page_limit'];
        }
        $stmt = $this->pearDB->prepare($queryContact);
        $stmt->bindParam(':name', $queryValues['name'], PDO::PARAM_STR);
        if (isset($queryValues['offset'])) {
            $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        }
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $serviceList = array();
        while ($data = $stmt->fetch()) {
            $serviceList[] = array('id' => $data['sc_id'], 'text' => $data['sc_name']);
        }
        return array(
            'items' => $serviceList,
            'total' => $stmt->rowCount()
        );
    }
}
