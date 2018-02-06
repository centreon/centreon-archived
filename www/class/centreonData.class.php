<?php
/*
 * Copyright 2005-2013 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the
 * GNU General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an
 * executable, regardless of the license terms of these independent modules,
 * and to copy and distribute the resulting executable under terms of Centreon
 * choice, provided that Centreon also meet, for each linked independent module,
 * the terms  and conditions of the license of that module. An independent
 * module is a module which is not derived from this program. If you modify this
 * program, you may extend this exception to your version of the program, but
 * you are not obliged to do so.
 * If you do not wish to do so, delete this exception statement from your
 * version.
 * 
 * For more information : contact@centreon.com
 */

/**
 * Object used for storing and accessing specific data
 *
 * @package Centreon
 * @version 2.5.0
 * @since 2.5.0
 * @author Sylvestre Ho <sho@centreon.com>
 */
class CentreonData
{
    /**
     * Instance of Centreon Template
     *
     * @var Centreon_Template
     */
    static private $instance = null;

    /**
     * List of javascript data
     *
     * @var array
     */
    private $jsData = array();
    
    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * Pass data to javascript
     *
     * @param string $key
     * @param string $value
     * @return void
     * @throws Exception
     */
    public function addJsData($key, $value)
    {
        if (isset($this->jsData[$key])) {
            throw new Exception(
                sprintf('Key %s in Javascript Data already used', $key)
            );
        }
        $this->jsData[$key] = $value;
    }

    /**
     * Get javascript data
     *
     * @return array
     */
    public function getJsData()
    {
        return $this->jsData;
    }
    
    /**
     * Get a instance of Centreon_Template
     *
     * @return Centreon_Template
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new CentreonData();
        }
        return self::$instance;
    }
}

/* vim: set ai softtabstop=4 shiftwidth=4 tabstop=4 expandtab: */
