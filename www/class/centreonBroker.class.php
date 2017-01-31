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

class CentreonBroker
{
    private $name;
    private $db;

    /*
	 * Constructor class
	 *
	 * @access public
	 * @return 	object	object session
	 */
    public function __construct($db)
    {
        $this->db = $db;
        $this->setBrokerName();
    }

    /**
     * Get Broker engine name
     */
    private function setBrokerName()
    {
            $this->name = 'broker';
    }

    /*
	 * return broker engine
	 */
    public function getBroker()
    {
        return "broker";
    }
        
    /**
     * Execute script
     *
     * @param string $script
     * @param string $action
     * @return void
     */
    protected function execLocalScript($script, $action)
    {
      shell_exec("sudo service $script $action");
    }
        
    /**
     * Get Init script
     *
     * @param string $sql;
     * @return string
     */
    protected function getInitScript($sql)
    {
        $res = $this->db->query($sql);
        $row = $res->fetchRow();
        $scriptName = "";
        if (isset($row['value']) && trim($row['value']) != '') {
            $scriptName = trim($row['value']);
        }
        return $scriptName;
    }
        
    /**
     * Do action
     *
     * @param string $action
     * @return void
     */
    protected function doAction($action)
    {
        if ($this->name == 'broker') {
            $initScript = $this->getInitScript("SELECT `value` FROM options WHERE `key` = 'broker_correlator_script'");
            if ($initScript) {
                $this->execLocalScript($initScript, $action);
            }
        }
    }
        
    /**
     * Magic method
     *
     * @param string $name
     * @param array $params
     * @throws Exception
     */
    public function __call($name, $params)
    {
        if (!preg_match('/reload|restart|stop|start/', $name)) {
            throw new Exception('Unknown method: '.$name);
        }
        $this->doAction($name);
    }
}
