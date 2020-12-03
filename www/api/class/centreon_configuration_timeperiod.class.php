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


require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once __DIR__ . "/centreon_configuration_objects.class.php";

class CentreonConfigurationTimeperiod extends CentreonConfigurationObjects
{

    /**
     * CentreonConfigurationTimeperiod constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get a list of time periods as a source of data for the select2 widget
     *
     * @throws RestBadRequestException If some parameter is missing will throw this exception
     * @return array
     */
    public function getList()
    {
        $queryWhere = $queryValues = [];

        // Check for select2 'q' argument
        if (isset($this->arguments['q'])) {
            $queryWhere[] = 'tp_name LIKE :name';
            $queryValues['name'] = [
                PDO::PARAM_STR => "%{$this->arguments['q']}%",
            ];
        }

        // exclude some values from the result
        if (isset($this->arguments['exclude'])) {
            $queryWhere[] = 'tp_id <> :exclude';
            $queryValues['exclude'] = [
                PDO::PARAM_INT => (int) $this->arguments['exclude'],
            ];
        }

        $queryTimePeriod = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT tp_id, tp_name FROM timeperiod ' .
            ($queryWhere ? 'WHERE ' . join(' AND ', $queryWhere) : '') .
            ' ORDER BY tp_name ';

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            if (
                !is_numeric($this->arguments['page'])
                || !is_numeric($this->arguments['page_limit'])
                || $this->arguments['page_limit'] < 1
            ) {
                throw new \RestBadRequestException('Error, limit must be an integer greater than zero');
            }

            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];

            $queryTimePeriod .= 'LIMIT :offset, :limit';

            $queryValues['offset'] = [
                PDO::PARAM_INT => (int) $offset,
            ];
            $queryValues['limit'] = [
                PDO::PARAM_INT => (int) $this->arguments['page_limit'],
            ];
        }

        $stmt = $this->pearDB->prepare($queryTimePeriod);

        foreach ($queryValues as $bindId => $bindData) {
            foreach ($bindData as $bindType => $bindValue) {
                $stmt->bindValue($bindId, $bindValue, $bindType);
                break;
            }
        }

        $stmt->execute();
        $timePeriodList = [];

        while ($data = $stmt->fetch()) {
            $timePeriodList[] = [
                'id' => $data['tp_id'],
                'text' => html_entity_decode($data['tp_name']),
            ];
        }

        return [
            'items' => $timePeriodList,
            'total' => (int) $this->pearDB->numberRows()
        ];
    }
}
