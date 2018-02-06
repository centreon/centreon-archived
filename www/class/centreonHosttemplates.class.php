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

require_once _CENTREON_PATH_ . 'www/class/centreonInstance.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonService.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonHost.class.php';

/*
 *  Class that contains various methods for managing hosts
 */

class CentreonHosttemplates extends CentreonHost
{
    /**
     *
     * @param array $values
     * @return array
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        return parent::getObjectForSelect2($values, $options, '0');
    }
    
    /**
     * Returns array of host linked to the template
     *
     * @return array
     */
    public function getLinkedHostsByName($hostTemplateName, $checkTemplates = true)
    {
        if ($checkTemplates) {
            $register = 0;
        } else {
            $register = 1;
        }

        $linkedHosts = array();
        $query = 'SELECT DISTINCT h.host_name '
            . 'FROM host_template_relation htr, host h, host ht '
            . 'WHERE htr.host_tpl_id = ht.host_id '
            . 'AND htr.host_host_id = h.host_id '
            . 'AND ht.host_register = "0" '
            . 'AND h.host_register = "' . $register . '" '
            . 'AND ht.host_name = "' . $this->db->escape($hostTemplateName) . '" ';
 
        $result = $this->db->query($query);

        if (PEAR::isError($result)) {
            throw new \Exception('Error while getting linked hosts of ' . $hostTemplateName);
        }

        while ($row = $result->fetchRow()) {
            $linkedHosts[] = $row['host_name'];
        }

        return $linkedHosts;
    }
}
