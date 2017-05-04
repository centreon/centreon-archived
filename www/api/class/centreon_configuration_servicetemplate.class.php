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
require_once dirname(__FILE__) . "/centreon_configuration_service.class.php";

class CentreonConfigurationServicetemplate extends CentreonConfigurationService
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
        $range = array();
        // Check for select2 'q' argument
        if (false === isset($this->arguments['q'])) {
            $q = '';
        } else {
            $q = $this->arguments['q'];
        }

        if (false === isset($this->arguments['l'])) {
            $l = '0';
        } else {
            $l = $this->arguments['l'];
        }

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $range[] = (int)$offset;
            $range[] = (int)$this->arguments['page_limit'];
        }

        if ($l == '1') {
            $serviceTemplateList = $this->listWithHostTemplate($q, $range);
        } else {
            $serviceTemplateList = $this->listClassic($q, $range);
        }
        return $serviceTemplateList;
    }

    /**
     *
     * @param string $q
     * @return array
     */
    private function listClassic($q, $range = array())
    {
        $serviceList = array();
        $queryValues = array();
        $queryRange = '';
        $queryContact = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT service_id, service_description ' .
            'FROM service ' .
            'WHERE service_description LIKE ? ' .
            'AND service_register = "0" ' .
            'ORDER BY service_description ';
        $queryValues[] = (string)'%' . $q . '%';

        if (isset($range)) {
            $queryRange = 'LIMIT ?, ?';
            $queryValues[] = (int)$range[0];
            $queryValues[] = (int)$range[1];
        }
        $queryContact .= $queryRange;
        $stmt = $this->pearDB->prepare($queryContact);
        $dbResult = $this->pearDB->execute($stmt, $queryValues);
        $total = $this->pearDB->numberRows();

        while ($data = $dbResult->fetchRow()) {
            $serviceList[] = array('id' => $data['service_id'], 'text' => $data['service_description']);
        }
        return array(
            'items' => $serviceList,
            'total' => $total
        );
    }

    /**
     *
     * @param string $q
     * @return array
     */
    private function listWithHostTemplate($q = '', $range = array())
    {
        $queryValues = array();
        $queryValues[] = (string)'%' . $q . '%';
        $queryService = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT s.service_description, s.service_id, ' .
            'h.host_name, h.host_id ' .
            'FROM host h, service s, host_service_relation hsr ' .
            'WHERE hsr.host_host_id = h.host_id ' .
            'AND hsr.service_service_id = s.service_id ' .
            'AND h.host_register = "0" ' .
            'AND s.service_register = "0" ' .
            'AND s.service_description LIKE ? ' .
            'ORDER BY h.host_name ';
        if (isset($range)) {
            $queryService .= 'LIMIT ?, ?';
            $queryValues[] = (int)$range[0];
            $queryValues[] = (int)$range[1];
        }
        $stmt = $this->pearDB->prepare($queryService);
        $dbResult = $this->pearDB->execute($stmt, $queryValues);
        $total = $this->pearDB->numberRows();
        $serviceList = array();
        while ($data = $dbResult->fetchRow()) {
            $serviceCompleteName = $data['host_name'] . ' - ' . $data['service_description'];
            $serviceCompleteId = $data['host_id'] . '-' . $data['service_id'];

            $serviceList[] = array(
                'id' => htmlentities($serviceCompleteId),
                'text' => $serviceCompleteName
            );
        }

        return array(
            'items' => $serviceList,
            'total' => $total
        );
    }
}
