<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace Centreon\Internal;

/**
 * Class for Centreon Configuration
 *
 * @see http://www.php.net/manual/en/class.pdo.php PHP PDO
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Core
 */
class Config
{
    /**
     * @var string The list of group from config file, this group is readonly
     */
    private $fileGroups = array(
        'db_centreon',
        'db_storage',
        'loggers',
        'cache',
        'template',
        'static_file',
        'global'
    );
    /**
     * @var array The application configuration
     */
    private $config = null;

    /**
     * Constructor
     *
     * @param $filename string The configuration filename, this file is in ini format
     * @throws \Centreon\Exception The configuration file is not readable
     */
    public function __construct($filename)
    {
        if (false === is_readable($filename)) {
            throw new Exception("The configuration file is not readable.");
        }
        try {
            $this->config = parse_ini_file($filename, true);
        } catch (\Exception $e) {
            throw new Exception("Error when parsing configuration file.", 0, $e);
        }
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
        /* Load from cache if exists */
        if ($di->get('cache')->has('app:config')) {
            $configTmp = $di->get('cache')->get('app:config');
            foreach ($configTmp as $group => $configGroup) {
                if (false === in_array($group, $this->fileGroups)) {
                    $this->config[$group] = $configGroup;
                }
            }
            return;
        }

        /* Load from database */
        $dbconn = $di->get('db_centreon');
        $stmt = $dbconn->query(
            "SELECT `group`, `key`, `value`
                FROM `cfg_options`
                ORDER BY `group`, `key`"
        );
        while ($row = $stmt->fetch()) {
            if (false === in_array($row['group'], $this->fileGroups)) {
                if (false === isset($this->config[$row['group']])) {
                    $this->config[$row['group']] = array();
                }
                $this->config[$row['group']][$row['key']] = $row['value'];
            }
        }
        $stmt->closeCursor();
        /* Save config into cache */
        $di->get('cache')->set('app:config', $this->config);
    }

    /**
     * Get a configuration value
     *
     * @param $group string The group of configuration
     * @param $var string The variable name
     * @param $default mixed The default value if the variable doesn't exists
     * @return mixed
     */
    public function get($group, $var, $default = null)
    {
        if (isset($this->config[$group]) && isset($this->config[$group][$var])) {
            return $this->config[$group][$var];
        }
        return $default;
    }

    /**
     * Get a full section informaiton
     *
     * @param $group string The group of configuration
     * @return array
     */
    public function getGroup($group)
    {
        if (isset($this->config[$group])) {
            return $this->config[$group];
        }
        return array();
    }

    /**
     * Set a configuration variable
     *
     * @param $group string The group of configuration
     * @param $var string The variable name
     * @param $value mixed The value to store
     * @throws The group is not permit for store in database
     * @throws If the configuration is not set in database
     */
    public function set($group, $var, $value)
    {
        if (in_array($group, $this->fileGroups)) {
            throw new Exception("This configuration group is not permit.");
        }
        if (false === isset($this->config[$group]) || false === isset($this->config[$group][$var])) {
            throw new Exception("This configuration $group - $var does not exists into database.");
        }
        $di = Di::getDefault();
        /* Save information in database */
        $dbconn = $di->get('db_centreon');
        $stmt = $dbconn->prepare(
            "UPDATE `cfg_options`
                SET `value` = :value
                WHERE `group` = :group
                    AND `key` = :key"
        );
        $stmt->bindParam(':value', $value, \PDO::PARAM_STR);
        $stmt->bindParam(':group', $group, \PDO::PARAM_STR);
        $stmt->bindParam(':key', $var, \PDO::PARAM_STR);
        $stmt->execute();
        $this->config[$group][$var] = $value;
        /* Save config into cache */
        $di->get('cache')->set('app:cache', $this->config);
    }
}
