<?php

/*
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
require_once __DIR__ . "/centreon_configuration_service.class.php";

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
     * @return array
     * @throws RestBadRequestException
     */
    public function getList()
    {
        $range = array();
        // Check for select2 'q' argument
        if (isset($this->arguments['q'])) {
            $q = (string)$this->arguments['q'];
        } else {
            $q = '';
        }

        if (isset($this->arguments['l'])) {
            $templateType = array('0', '1');
            if (in_array($this->arguments['l'], $templateType)) {
                $l = $this->arguments['l'];
            } else {
                throw new \RestBadRequestException('Error, bad list parameter');
            }
        } else {
            $l = '0';
        }

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            if (
                !is_numeric($this->arguments['page'])
                || !is_numeric($this->arguments['page_limit'])
                || $this->arguments['page_limit'] < 1
            ) {
                throw new \RestBadRequestException('Error, limit must be an integer greater than zero');
            }
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
     * @param $q
     * @param array $range
     * @return array
     */
    private function listClassic($q, $range = array())
    {
        $serviceList = array();
        $queryValues = array();

        $queryContact = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT service_id, service_description ' .
            'FROM service ' .
            'WHERE service_description LIKE :description ' .
            'AND service_register = "0" ' .
            'ORDER BY service_description ';
        if (isset($range) && !empty($range)) {
            $queryContact .= 'LIMIT :offset, :limit';
            $queryValues['offset'] = (int)$range[0];
            $queryValues['limit'] = (int)$range[1];
        }
        $queryValues['description'] = '%' . (string)$q . '%';
        $stmt = $this->pearDB->prepare($queryContact);
        $stmt->bindParam(':description', $queryValues['description'], PDO::PARAM_STR);
        if (isset($queryValues['offset'])) {
            $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        }
        $stmt->execute();
        while ($data = $stmt->fetch()) {
            $serviceList[] = array('id' => $data['service_id'], 'text' => $data['service_description']);
        }
        return array(
            'items' => $serviceList,
            'total' => (int) $this->pearDB->numberRows()
        );
    }

    /**
     * @param string $q
     * @param array $range
     * @return array
     */
    private function listWithHostTemplate($q = '', $range = array())
    {
        $queryValues = array();
        $queryValues['description'] = '%' . (string)$q . '%';
        $queryService = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT s.service_description, s.service_id, ' .
            'h.host_name, h.host_id ' .
            'FROM host h, service s, host_service_relation hsr ' .
            'WHERE hsr.host_host_id = h.host_id ' .
            'AND hsr.service_service_id = s.service_id ' .
            'AND h.host_register = "0" ' .
            'AND s.service_register = "0" ' .
            'AND CONCAT(h.host_name, " - ", s.service_description) LIKE :description ' .
            'ORDER BY h.host_name ';
        if (isset($range) && !empty($range)) {
            $queryService .= 'LIMIT :offset, :limit';
            $queryValues['offset'] = (int)$range[0];
            $queryValues['limit'] = (int)$range[1];
        }
        $stmt = $this->pearDB->prepare($queryService);
        $stmt->bindParam(':description', $queryValues['description'], PDO::PARAM_STR);
        if (isset($queryValues['offset'])) {
            $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        }
        $stmt->execute();
        $serviceList = array();
        while ($data = $stmt->fetch()) {
            $serviceCompleteName = $data['host_name'] . ' - ' . $data['service_description'];
            $serviceCompleteId = $data['host_id'] . '-' . $data['service_id'];

            $serviceList[] = array(
                'id' => htmlentities($serviceCompleteId),
                'text' => $serviceCompleteName
            );
        }
        return array(
            'items' => $serviceList,
            'total' => (int) $this->pearDB->numberRows()
        );
    }
}
