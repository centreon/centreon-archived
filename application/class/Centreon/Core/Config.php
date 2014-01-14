<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 */

namespace Centreon\Core;

/**
 * Class for Centreon Configuration
 *
 * @see http://www.php.net/manual/en/class.pdo.php
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Core
 */
class Config
{
    private $file_groups = array('db_centreon', 'db_storage');
    private $config = null;

    /**
     * Constructor
     *
     * @param $filename string The configuration filename, this file is in ini format
     * @throws \Centreon\Core\Exception The configuration file is not readable
     */
    public function __construct($filename)
    {
        if (false === is_readable($filename)) {
            throw new Exception("The configuration file is not readable.");
        }
        $this->config = parse_ini_file($filename, true);
        if (false === $this->config) {
            throw new Exception("Error when parsing configuration file.");
        }
    }

    /**
     * Load configuration from Centreon database
     */
    public function loadFromDb()
    {
        $di = Di::getDefault();
        /* @Todo test if in cache and load from cache */
        $dbconn = $di->get('db_centreon');
        $stmt = $dbconn->query("SELECT `group`, `key`, `value`
            FROM `options`
            ORDER BY `group`, `key`");
        while ($row = $stmt->fetch()) {
            if (false === in_array($row['group'], $this->file_groups)) {
                if (false === isset($this->config[$row['group']])) {
                    $this->config[$row['group']] = array();
                }
                $this->config[$row['group']][$row['key']] = $row['value'];
            }
        }
        $stmt->closeCursor();
    }

    /**
     * Get a configuration value
     *
     * @param $group string The group of configuration
     * @param $var string The variable name
     * @param $default mixed The default value if the variable doesn't exists
     * @return mixed
     */
    public function get($group, $var, $default=null)
    {
        if (isset($this->config[$group]) && isset($this->config[$group][$var])) {
            return $this->config[$group][$var];
        }
        return $default;
    }

    /**
     * Set a configuration variable
     *
     * @param $group string The group of configuration
     * @param $var string The variable name
     * @param $value mixed The value to store
     * @throws The group is not permit for store in database
     */
    public function set($group, $var, $value)
    {
        if (in_array($group, $this->file_groups)) {
            throw new Exception("This configuration group is not permit.");
        }
        $di = Di::getDefault();
        /* Save information in database */
        $dbconn = $di->get('db_centreon');
        $stmt = $dbconn->prepare("UPDATE `options`
            SET `value` = :value
            WHERE `group` = :group
                AND `key` = :key");
        $stmt->bindParam(':group', $group, \PDO::PARAM_STR);
        $stmt->bindParam(':key', $key, \PDO::PARAM_STR);
        $stmt->execute();
        /* @Todo update cache */
        if (false === isset($this->config[$group])) {
            $this->config[$group] = array();
        }
        $this->config[$group][$var] = $value;
    }
}
