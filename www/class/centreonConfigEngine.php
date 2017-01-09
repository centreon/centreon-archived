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

/**
 * Class for Engine configuration
 *
 * @author Sylvestre Ho <sho@centreon.com>
 */
class CentreonConfigEngine
{
    protected $db;
    
    /**
     * Constructor
     *
     * @param CentreonDB $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Insert one or multiple broker directives
     *
     * @param int $serverId | id of monitoring server
     * @param array $directives | event broker directives
     * @return void
     */
    public function insertBrokerDirectives($serverId, $directives = array())
    {
        $this->db->query("DELETE FROM cfg_nagios_broker_module
                WHERE cfg_nagios_id = ".$this->db->escape($serverId));
                    
        foreach ($directives as $value) {
            if ($value != "") {
                $this->db->query("INSERT INTO cfg_nagios_broker_module (`broker_module`, `cfg_nagios_id`) 
                                VALUES ('". $this->db->escape($value) ."', ". $this->db->escape($serverId) .")");
            }
        }
    }
    
    /**
     * Used by form only
     *
     * @param int $serverId
     * @return array
     */
    public function getBrokerDirectives($serverId = null)
    {
        $arr = array();
        $i = 0;
        if (!isset($_REQUEST['in_broker']) && $serverId) {
            $res = $this->db->query("SELECT broker_module
                                FROM cfg_nagios_broker_module
                                WHERE cfg_nagios_id = " . $this->db->escape($serverId));
            while ($row = $res->fetchRow()) {
                $arr[$i]['in_broker_#index#'] = $row['broker_module'];
                $i++;
            }
        } elseif (isset($_REQUEST['in_broker'])) {
            foreach ($_REQUEST['in_broker'] as $val) {
                $arr[$i]['in_broker_#index#'] = $val;
                $i++;
            }
        }
        return $arr;
    }
}
