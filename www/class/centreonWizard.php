<?php
/*
 * Copyright 2005-2012 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

class Centreon_Wizard
{
    private $_uuid = null;
    private $_name = null;
    private $_values = array();
    private $_lastUpdate = 0;

    /**
     * Constructor
     *
     * @param string $name The wizard name
     * @param string $uuid The wizard unique id
     */
    public function __construct($name, $uuid)
    {
        $this->_uuid = $uuid;
	    $this->_name = $name;
	    $this->_lastUpdate = time();
    }

    /**
     * Get values for a step
     *
     * @param int $step The step position
     * @return array
     */
    public function getValues($step)
    {
        if (false === isset($this->_values[$step])) {
	        return array();
	    }
	    return $this->_values[$step];
    }

    /**
     * Get a value
     *
     * @param int $step The step position
     * @param string $name The variable name
     * @param string $default The default value
     * @return string
     */
    public function getValue($step, $name, $default = '')
    {
        if (false === isset($this->_values[$step]) || false === isset($this->_values[$step][$name])) {
	        return $default;
	    }
	    return $this->_values[$step][$name];
    }

    /**
     * Add values for a step
     *
     * @param int $step The step position
     * @param array $post The post with values
     */
    public function addValues($step, $post)
    {
    	/* Reinit */
    	$this->_values[$step] = array();
    	foreach ($post as $key => $value) {
                if (strncmp($key, 'step' . $step . '_', 6) === 0) {
    	        $this->_values[$step][str_replace('step' . $step . '_', '', $key)] = $value;
    	    }
    	}
        $this->_lastUpdate = time();
    }

    /**
     * Test if the uuid of wizard
     *
     * @param string $uuid The unique id
     * @return bool
     */
    public function testUuid($uuid)
    {
       if ($uuid == $this->_uuid) {
           return true;
       }
       return false;
    }

    /**
     * Magic method __sleep
     */
    public function __sleep()
    {
        $this->_lastUpdate = time();
        return array('_uuid', '_lastUpdate', '_name');
    }

    /**
     * Magic method __wakeup
     */
    public function __wakeup()
    {
        $this->_lastUpdate = time();
    }
}
