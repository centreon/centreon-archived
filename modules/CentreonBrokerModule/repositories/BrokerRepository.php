<?php
/*
 * Copyright 2005-2014 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonBroker\Repository;

use Centreon\Internal\Di;
use CentreonAdministration\Repository\OptionRepository;
use CentreonConfiguration\Internal\Poller\Template\Manager as PollerTemplateManager;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package CentreonEngine
 * @subpackage Repository
 */
class BrokerRepository
{
    /**
     * Save broker parameters of a node
     *
     * @param int $pollerId
     * @param array $params
     */
    public static function save($pollerId, $params)
    {
        $db = Di::getDefault()->get('db_centreon');

        $arr = array();
        foreach ($params as $k => $v) {
            $arr[$k] = $v;
        }

        /* Save paths */
        /* Test if exists in db */
        $query = "SELECT COUNT(poller_id) as poller
            FROM cfg_centreonbroker_paths
            WHERE poller_id = :poller_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':poller_id', $pollerId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if ($row['poller'] > 0) {
            /* Update */
            $query = "UPDATE cfg_centreonbroker_paths SET 
                directory_config = :broker_etc_directory,
                directory_modules = :broker_module_directory,
                directory_logs = :broker_logs_directory,
                directory_data = :broker_data_directory,
                directory_cbmod = :broker_cbmod_directory,
                init_script = :init_script
                WHERE poller_id = :poller_id";
        } else {
            /* Insert */
            $query = "INSERT INTO cfg_centreonbroker_paths
                (poller_id, directory_config, directory_modules, directory_logs, 
                directory_data, init_script, directory_cbmod) VALUES
                (:poller_id, :broker_etc_directory, :broker_module_directory, 
                :broker_logs_directory, :broker_data_directory, :init_script, :broker_cbmod_directory)";
        }
        $stmt = $db->prepare($query);
        $stmt->bindParam(':poller_id', $pollerId);
        $stmt->bindParam(':broker_etc_directory', $arr['broker_etc_directory'], \PDO::PARAM_STR);
        $stmt->bindParam(':broker_module_directory', $arr['broker_module_directory'], \PDO::PARAM_STR);
        $stmt->bindParam(':broker_logs_directory', $arr['broker_logs_directory'], \PDO::PARAM_STR);
        $stmt->bindParam(':broker_data_directory', $arr['broker_data_directory'], \PDO::PARAM_STR);
        $stmt->bindParam(':init_script', $arr['broker_init_script'], \PDO::PARAM_STR);
        $stmt->bindParam(':broker_cbmod_directory', $arr['broker_cbmod_directory'], \PDO::PARAM_STR);
        $stmt->execute();
        
        /* Save extract params */
        $listTpl = PollerTemplateManager::buildTemplatesList();
        $tmpl = $params['poller_tmpl'];
        if (!isset($listTpl[$tmpl])) {
            return;
        }
        
        $fileTplList = $listTpl[$tmpl]->getBrokerPath();
        $information = array();
        foreach ($fileTplList as $fileTpl) {
            $information = static::mergeBrokerConf($information, $fileTpl);
        }
        
        $listType = array('output', 'input', 'logger');
        /* setup */
        foreach ($information['content']['broker']['setup'] as $setup) {
            /* mode */
            foreach ($setup['params']['mode'] as $mode) {
                /* type */
                foreach ($mode as $type => $config) {
                    /* @todo one peer retention */
                    if ($type == 'normal') {
                        /* module */
                        foreach ($config as $module) {
                            $configId =  static::insertConfig($pollerId, $module['general']['name'], $arr);
                            foreach ($listType as $type) {
                                if (isset($module[$type])) {
                                    $groupNb = 1;
                                    foreach ($module[$type] as $typeInfo) {
                                        /* Key */
                                        foreach ($typeInfo as $key => $value) {
                                            if (is_string($value) && preg_match("/%([\w_]+)%/", $value, $matches)) {
                                                if (isset($params[$matches[1]]) && trim($params[$matches[1]]) !== "") {
                                                    static::insertPollerInfo($pollerId, $matches[1], $params[$matches[1]]);
                                                }
                                            } else {
                                                $finalKey = $module['general']['name']
                                                    . '-'
                                                    . $type
                                                    . '-'
                                                    . $typeInfo['type']
                                                    . '-'
                                                    . $typeInfo['name']
                                                    . '-'
                                                    . $key;
                                                if (is_string($value)) {
                                                    if (in_array($finalKey, array_keys($arr))) {
                                                        static::insertUserInfo($configId, $type, $groupNb, $finalKey, $arr[$finalKey]);
                                                    } else {
                                                        static::insertUserInfo($configId, $type, $groupNb, $finalKey, $value);
                                                    }
                                                }
                                            }
                                        }
                                        $groupNb++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * @param type $finalFileTpl
     * @param type $fileTpl
     * @return $finalFileTpl
     */
    public static function mergeBrokerConf($finalFileTpl, $fileTpl)
    {
        $content = json_decode(file_get_contents($fileTpl), true);
        if (count($finalFileTpl) > 0) {
            //foreach ($finalFileTpl as &$finalConfBlock) {
                //if ($finalFileTpl['name'] === $content['name']) {
                    foreach ($content['content']['broker']['setup'] as $setup) {
                        $finalFileTpl['content']['broker']['setup'] = static::mergeBrokerConfSetup($finalFileTpl['content']['broker']['setup'], $setup);
                    }
                    //$finalConfBlock['content']['broker']['setup'] = static::concatBrokerConfSetup($finalConfBlock['content']['broker']['setup'], $content['content']['broker']['setup']);
                /*} else {
                    $count = count($finalFileTpl);
                    $finalConfBlock[$count]['name'] = $confBlock['name'];
                    $finalConfBlock[$count]['content'] = $confBlock['content'];
                }*/
            //}
        } else {
            $finalFileTpl = $content;
        }
        return $finalFileTpl;
    }

    /**
     *
     * @param type $finalSetup
     * @param type $tmpSetup
     * @return $finalSetup
     */
    public static function mergeBrokerConfSetup($finalSetup, $tmpSetup)
    {
        //var_dump($tmpSetup);
        //var_dump($finalSetup);
        foreach ($finalSetup as &$setup) {
            if ($setup['name'] === $tmpSetup['name']) {
                foreach ($tmpSetup['params']['mode'] as $mode) {
                    $setup['params']['mode'] = static::mergeBrokerConfMode($setup['params']['mode'], $mode);
                }
            } else {
                $count = count($setup);
                $setup[$count]['name'] = $tmpSetup['name'];
                $setup[$count]['params'] = $tmpSetup['params'];
            }
        }
        return $finalSetup;
    }

    /**
     *
     * @param type $finalMode
     * @param type $tmpMode
     * @return $finalMode
     */
    public static function mergeBrokerConfMode($finalMode, $tmpMode)
    {
        $tmpModeName = current(array_keys($tmpMode));
        $tmpModeValue = $tmpMode[$tmpModeName];
        $merged = false;
        foreach ($finalMode as &$mode) {
            $modeName = current(array_keys($mode));
            if ($modeName === $tmpModeName) {
                $merged = true;
                foreach ($tmpMode[$tmpModeName] as $property) {
                    $mode[$modeName] = static::mergeBrokerConfProperty($mode[$modeName], $property);
                }
            }
        }
        return $finalMode;
    }

    /**
     *
     * @param type $finalProperty
     * @param type $tmpProperty
     * @return $finalProperty
     */
    public static function mergeBrokerConfProperty($finalProperty, $tmpProperty)
    {
        $merged = false;
        foreach ($finalProperty as &$property) {
            if ($property['general']['name'] === $tmpProperty['general']['name']) {
                $merged = true;
                if (isset ($tmpProperty['input'])) {
                    $property['input'] = isset($property['input']) ? $property['input'] : array();
                    $property['input'] = array_merge($property['input'], $tmpProperty['input']);
                }
                if (isset ($tmpProperty['output'])) {
                    $property['output'] = isset($property['output']) ? $property['output'] : array();
                    $property['output'] = array_merge($property['output'], $tmpProperty['output']);
                }
                if (isset ($tmpProperty['logger'])) {
                    $property['logger'] = isset($property['logger']) ? $property['logger'] : array();
                    $property['logger'] = array_merge($property['logger'], $tmpProperty['logger']);
                }
                if (isset ($tmpProperty['correlation'])) {
                    $property['correlation'] = isset($property['correlation']) ? $property['correlation'] : array();
                    $property['correlation'] = array_merge($property['correlation'], $tmpProperty['correlation']);
                }
                if (isset ($tmpProperty['statistics'])) {
                    $property['statistics'] = isset($property['statistics']) ? $property['statistics'] : array();
                    $property['statistics'] = array_merge($property['statistics'], $tmpProperty['statistics']);
                }
            }
        }
        if (!$merged) {
            $finalProperty[] = $tmpProperty;
        }
        return $finalProperty;
    }

    /**
     * 
     * @param type $pollerId
     * @param type $configName
     * @return type
     */
    public static function getConfig($pollerId, $configName = "", $withName = false)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        /* Test if the configuration is in database */
        $query = "SELECT config_id";
        
        if ($withName) {
            $query .= ", config_name";
        }
        
        $query .= " FROM cfg_centreonbroker WHERE poller_id = :poller_id";
        
        if (!empty($configName)) {
            $query .= " AND config_name = :config_name";
        }
        
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':poller_id', $pollerId, \PDO::PARAM_INT);
        
        if (!empty($configName)) {
            $stmt->bindParam(':config_name', $configName, \PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $row = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        $return = false;
        
        if (count($row) > 1) {
            $return = $row;
        } elseif (count($row) == 1) {
            $return = $row[0]['config_id'];
        }
        return $return;
    }
    
    /**
     * Add a configuration for a module
     * 
     * @param int $pollerId The poller id
     * @param string $configName The configuration module name
     * @param array $params
     * @return mixed
     */
    public static function insertConfig($pollerId, $configName, $params) {
        $dbconn = Di::getDefault()->get('db_centreon');
        /* Test if the configuration is in database */
        $configId = static::getConfig($pollerId, $configName);
        if (false !== $configId) {
            $queryInsert = "UPDATE cfg_centreonbroker
                SET event_queue_max_size = :event_queue_max_size,
                write_thread_id = :write_thread_id,
                write_timestamp = :write_timestamp,
                flush_logs = :flush_logs
                WHERE config_name = :config_name
                AND poller_id = :poller_id";
            $stmt = $dbconn->prepare($queryInsert);
            $stmt->bindParam(':config_name', $configName, \PDO::PARAM_STR);
            $stmt->bindParam(':event_queue_max_size', $params['event_queue_max_size'], \PDO::PARAM_STR);
            $stmt->bindParam(':write_thread_id', $params['write_thread_id'], \PDO::PARAM_STR);
            $stmt->bindParam(':write_timestamp', $params['write_timestamp'], \PDO::PARAM_STR);
            $stmt->bindParam(':flush_logs', $params['flush_logs'], \PDO::PARAM_STR);
            $stmt->bindParam(':poller_id', $pollerId, \PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $queryInsert = "INSERT INTO cfg_centreonbroker
                (poller_id, config_name) VALUES
                (:poller_id, :config_name)";
            $stmt = $dbconn->prepare($queryInsert);
            $stmt->bindParam(':poller_id', $pollerId, \PDO::PARAM_INT);
            $stmt->bindParam(':config_name', $configName, \PDO::PARAM_STR);
            $stmt->execute();

            $configId = static::getConfig($pollerId, $configName);
        }
        
        return $configId;
    }
    
    /**
     * 
     * @param type $pollerId
     * @return type
     */
    public static function getUserInfo($pollerId)
    {
        $fullInfo = array();
        
        $configs = static::getConfig($pollerId, "", true);
        
        foreach ($configs as $config) {
            $configInfo = static::loadUserInfoForConfig($config['config_id']);
            $fullInfo[$config['config_name']] = $configInfo;
        }
        
        return $fullInfo;
    }
    
    /**
     * 
     * @param type $configId
     * @return type
     */
    public static function loadUserInfoForConfig($configId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        /* Test if the information is already in database */
        
        $query = "SELECT *
            FROM cfg_centreonbroker_info
            WHERE config_id = :config_id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':config_id', $configId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $row;
    }

    /**
     * Add or update a custom information for Centreon Broker set by a user
     *
     * @param string $group The group name
     * @param int $groupId The group id
     * @param string $key The configuration name
     * @param string $value The configuration value
     */
    public static function insertUserInfo($configId, $group, $groupId, $key, $value)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        /* Test if the information is already in database */
        $query = "SELECT COUNT(*) as nb
            FROM cfg_centreonbroker_info
            WHERE config_id = :config_id
                AND config_key = :config_key
                AND config_group = :config_group
                AND config_group_id = :config_group_id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':config_id', $configId, \PDO::PARAM_INT);
        $stmt->bindParam(':config_key', $key, \PDO::PARAM_STR);
        $stmt->bindParam(':config_group', $group, \PDO::PARAM_STR);
        $stmt->bindParam(':config_group_id', $groupId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row['nb'] > 0) {
            $query = "UPDATE cfg_centreonbroker_info SET
                config_value = :config_value
                WHERE config_id = :config_id
                    AND config_key = :config_key
                    AND config_group = :config_group
                    AND config_group_id = :config_group_id";
        } else {
            $query = "INSERT INTO cfg_centreonbroker_info
                (config_id, config_key, config_value, config_group, config_group_id) VALUES
                (:config_id, :config_key, :config_value, :config_group, :config_group_id)";
        }
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':config_id', $configId, \PDO::PARAM_INT);
        $stmt->bindParam(':config_key', $key, \PDO::PARAM_STR);
        $stmt->bindParam(':config_value', $value, \PDO::PARAM_STR);
        $stmt->bindParam(':config_group', $group, \PDO::PARAM_STR);
        $stmt->bindParam(':config_group_id', $groupId, \PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Insert a custom information for a poller
     *
     * @param int $pollerId The poller id
     * @param string $key The name of configuration
     * @param string $value The value of configuration
     */
    public static function insertPollerInfo($pollerId, $key, $value)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        /* Test if the information is in database */
        $query = "SELECT COUNT(*) as nb
            FROM cfg_centreonbroker_pollervalues
            WHERE poller_id = :poller_id
                AND name = :name";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':poller_id', $pollerId, \PDO::PARAM_INT);
        $stmt->bindParam(':name', $key, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row['nb'] > 0) {
            $query = "UPDATE cfg_centreonbroker_pollervalues SET
                value = :value
                WHERE poller_id = :poller_id
                    AND name = :name";
        } else {
            $query = "INSERT INTO cfg_centreonbroker_pollervalues
                (poller_id, name, value) VALUES
                (:poller_id, :name, :value)";
        }
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':poller_id', $pollerId, \PDO::PARAM_INT);
        $stmt->bindParam(':name', $key, \PDO::PARAM_INT);
        $stmt->bindParam(':value', $value, \PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Get the paths for a Centreon Broker poller
     * 
     * @param int $pollerId
     * @return array
     */
    public static function getPathsFromPollerId($pollerId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = "SELECT directory_modules, directory_config, directory_logs, 
            directory_data, directory_cbmod, init_script
            FROM cfg_centreonbroker_paths
            WHERE poller_id = :poller_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
            ':poller_id' => $pollerId
        ));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row;
    }
    
    /**
     * 
     * @param integer $pollerId
     * @return array
     */
    public static function getGeneralValues($pollerId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = "SELECT write_thread_id, event_queue_max_size, write_timestamp, flush_logs
            FROM cfg_centreonbroker
            WHERE poller_id = :poller_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
            ':poller_id' => $pollerId
        ));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row;
    }

    /**
     * Load custom configuration values for Centreon Broker
     *
     * @param int $pollerId The poller id
     * @return array
     */
    public static function loadValues($pollerId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $query = "SELECT name, value
            FROM cfg_centreonbroker_pollervalues
            WHERE poller_id = :poller_id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':poller_id', $pollerId, \PDO::PARAM_INT);
        $stmt->execute();
        $values = array();
        while ($row = $stmt->fetch()) {
            $values[$row['name']] = $row['value'];
        }
        return $values;
    }
    
    /**
     * 
     * @return type
     */
    public static function getGlobalValues()
    {
        $globalOptions = array();
        
        $defaultOptionskeys = array(
            'rrd_metric_path',
            'rrd_status_path',
            'rrd_path',
            'rrd_port',
            'storage_interval',
            'broker_modules_directory',
            'broker_data_directory',
        );
        $defaultOptionsValues = OptionRepository::get('default', $defaultOptionskeys);
        
        $defaultOptionsValuesKeys = array_keys($defaultOptionsValues);
        foreach ($defaultOptionsValuesKeys as &$optValue) {
            switch($optValue) {
                case 'rrd_metric_path':
                    $optValue = 'rrd_metrics';
                    break;
                case 'rrd_status_path':
                    $optValue = 'rrd_status';
                    break;
                case 'storage_interval':
                    $optValue = 'interval';
                    break;
                default:
                    break;
            }
            $optValue = 'global_' . $optValue;
        }
        if (count($defaultOptionsValues)) {
            $globalOptions = array_combine($defaultOptionsValuesKeys, array_values($defaultOptionsValues));
        }
        
        return $globalOptions;
    }
}
