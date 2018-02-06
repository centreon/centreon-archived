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

require_once "class/centreonWidget/Params.class.php";

class CentreonWidgetParamsRange extends CentreonWidgetParams
{
    public function __construct($db, $quickform, $userId)
    {
        parent::__construct($db, $quickform, $userId);
    }

    public function init($params)
    {
        parent::init($params);
        if (isset($this->quickform)) {
            $query = "SELECT min_range, max_range, step
            		  FROM widget_parameters_range
            		  WHERE parameter_id = " . $this->db->escape($params['parameter_id']);
            $res = $this->db->query($query);
            $row = $res->fetchRow();
            $min = $row['min_range'];
            $max = $row['max_range'];
            $step = $row['step'];
            $tab = array();
            for ($i = $min; $i <= $max; $i += $step) {
                $tab[$i] = $i;
            }
            $this->element = $this->quickform->addElement('select',
            											  'param_'.$params['parameter_id'],
                                                          $params['parameter_name'],
                                                          $tab);
        }
    }
}
