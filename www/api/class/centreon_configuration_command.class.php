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
 * this program; if not, see <htcommand://www.gnu.org/licenses>.
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
 * For more information : command@centreon.com
 *
 */


require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once __DIR__ . "/centreon_configuration_objects.class.php";

class CentreonConfigurationCommand extends CentreonConfigurationObjects
{
    /**
     * CentreonConfigurationCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     * @throws Exception
     * @throws RestBadRequestException
     */
    public function getList()
    {
        $queryValues = array();
        // Check for select2 'q' argument
        if (false === isset($this->arguments['q'])) {
            $queryValues['commandName'] = '%%';
        } else {
            $queryValues['commandName'] = '%' . (string)$this->arguments['q'] . '%';
        }

        if (isset($this->arguments['t'])) {
            if (!is_numeric($this->arguments['t'])) {
                throw new \RestBadRequestException('Error, command type must be numerical');
            }
            $queryCommandType = 'AND command_type = :commandType ';
            $queryValues['commandType'] = (int)$this->arguments['t'];
        } else {
            $queryCommandType = '';
        }

        $queryCommand = 'SELECT SQL_CALC_FOUND_ROWS command_id, command_name ' .
            'FROM command ' .
            'WHERE command_name LIKE :commandName AND command_activate = "1" ' .
            $queryCommandType .
            'ORDER BY command_name ';

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            if (
                !is_numeric($this->arguments['page'])
                || !is_numeric($this->arguments['page_limit'])
                || $this->arguments['page_limit'] < 1
            ) {
                throw new \RestBadRequestException('Error, limit must be an integer greater than zero');
            }
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $queryCommand .= 'LIMIT :offset, :limit';
            $queryValues['offset'] = (int)$offset;
            $queryValues['limit'] = (int)$this->arguments['page_limit'];
        }

        $stmt = $this->pearDB->prepare($queryCommand);
        $stmt->bindParam(':commandName', $queryValues["commandName"], PDO::PARAM_STR);
        if (isset($queryValues['commandType'])) {
            $stmt->bindParam(':commandType', $queryValues["commandType"], PDO::PARAM_INT);
        }
        if (isset($queryValues["offset"])) {
            $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        }
        $stmt->execute();

        $commandList = array();
        while ($data = $stmt->fetch()) {
            $commandList[] = array('id' => $data['command_id'], 'text' => $data['command_name']);
        }

        return array(
            'items' => $commandList,
            'total' => (int)$this->pearDB->numberRows()
        );
    }
}
