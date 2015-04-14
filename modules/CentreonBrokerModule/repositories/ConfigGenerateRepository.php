<?php
/*
 * Copyright 2005-2015 CENTREON
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
use Centreon\Internal\Db as CentreonDb;
use Centreon\Internal\Exception;
use CentreonConfiguration\Models\Poller;
use CentreonBroker\Repository\BrokerRepository;
use CentreonConfiguration\Events\BrokerModule as BrokerModuleEvent;
use CentreonConfiguration\Internal\Poller\Template\Manager as PollerTemplateManager;

/**
 * Factory for generate Centron Broker configuration
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package CentreonBroker
 */
class ConfigGenerateRepository
{
    private $tmpPath;
    private $pollerId;
    private $paths = array();
    private $baseConfig = array();
    private $tplInformation = array();
    private $defaults = array();
    private $parsedDefault = array();

    /**
     * Construt
     */
    public function __construct()
    {
        $di = Di::getDefault();

        $this->tmpPath = $di->get('config')->get('global', 'centreon_generate_tmp_dir');

        if (!isset($this->tmpPath)) {
            throw new Exception('Temporary path not set');
        }
        $this->tmpPath = rtrim($this->tmpPath, '/') . '/broker';

        /* Load defaults values */
        $this->defaults = json_decode(file_get_contents(dirname(__DIR__) . '/data/default.json'), true);

        /* Create directories if they don't exist */
        if (!is_dir($this->tmpPath)) {
            mkdir($this->tmpPath);
        }
    }

    /**
     * Generate configuration files for Centreon Broker
     *
     * @param int $pollerId The poller id
     */
    public function generate($pollerId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $this->pollerId = $pollerId;

        if (!is_dir($this->tmpPath . '/' . $this->pollerId)) {
            mkdir($this->tmpPath . '/' . $this->pollerId);
        }
        /* Get poller template */
        $params = Poller::get($this->pollerId, 'tmpl_name');
        if (!isset($params['tmpl_name']) || is_null($params['tmpl_name'])) {
            throw new Exception('Not template defined');
        }
        $tmplName = $params['tmpl_name'];

        /* Load template information for poller */
        $listTpl = PollerTemplateManager::buildTemplatesList();
        if (!isset($listTpl[$tmplName])) {
            throw new Exception('The template is not found on list of templates');
        }
        $fileTplList = $listTpl[$tmplName]->getBrokerPath();
        //$this->tplInformation = json_decode(file_get_contents($fileTpl), true);

        $this->tplInformation = array();
        foreach ($fileTplList as $fileTpl) {
            $this->tplInformation = BrokerRepository::mergeBrokerConf($this->tplInformation, $fileTpl);
        }

        $this->loadMacros($pollerId);

        /* Get list of configuration files */
        $query = "SELECT config_id, config_name, flush_logs, write_timestamp, name, 
            write_thread_id, event_queue_max_size
            FROM cfg_centreonbroker c, cfg_pollers p
            WHERE c.poller_id = p.poller_id
            AND p.poller_id = :poller_id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':poller_id', $pollerId, \PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $this->baseConfig['%name%'] = $row['name'];
            static::generateModule($row);
        }
    }

    /**
     * Gerenate configuration file for a module
     *
     * @param array $row The module information
     */
    private function generateModule($row)
    {
        $filename = $this->tmpPath . '/' . $this->pollerId . '/' . $row['config_name'] . '.xml';

        $moduleInformation = $this->getInformationFromTpl($row['config_name']);

        $xml = new \XMLWriter();
        if (false === $xml->openURI($filename)) {
            throw new Exception('Error when create configuration file.');
        }
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('conf');

        foreach ($moduleInformation  as $module => $information) {
            if ($module == 'general') {
                /* Merge information with default */
                $configuration = $this->moduleGeneral($information, $row);
                $configuration = array_merge($this->defaults['general'], $configuration);
                $this->addModule($xml, $module, $configuration, true);
            } else {
                $nbGroup = 1;
                foreach ($information as $group) {
                    /* Merge information with default */
                    $configuration = $this->moduleBlock($row['config_id'], $module, $nbGroup, $group);
                    $default = $this->getDefaults($module, $group);
                    $configuration = array_merge($default, $configuration);
                    $this->addModule($xml, $module, $configuration);
                    $nbGroup++;
                }
            }
        }


        $xml->endElement();
        $xml->endDocument();
    }

    /**
     * Load information form the template
     *
     * @param string $name The name of the module
     * @return array
     */
    private function getInformationFromTpl($name)
    {
        foreach ($this->tplInformation['content']['broker']['setup'] as $setup) {
            foreach ($setup['params']['mode'] as $mode) {
                foreach ($mode as $type => $config) {
                    if ($type == 'normal') {
                        foreach ($config as $module) {
                            if ($module['general']['name'] == $name) {
                                return $module;
                            }
                        }
                    }
                }
            }
        }
        return array();
    }

    /**
     * Parse the general configuration
     *
     * @param array $info The information
     * @param array $row The Centreon Broker poller configuration
     * @return array 
     */
    private function moduleGeneral($info, $row)
    {
        /* Generate general */
        $listGeneralUser = array('flush_logs', 'write_timestamp', 'write_thread_id', 'event_queue_max_size');
        $generalConf = $info;
        foreach ($listGeneralUser as $info) {
            if (false === is_null($row[$info])) {
                $generalConf[$info] = $row[$info];
            }
        }
        return $generalConf;
    }

    /**
     * Parse a module block
     *
     * @param int $configId The configuration id
     * @param string $moduleType The module type
     * @param int $nbGroup The position in configuration
     * @param array $information The template information
     * @return array
     */
    private function moduleBlock($configId, $moduleType, $nbGroup, $information)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        /* Prepare general information */
        $defaultInformation = array();
        $blockConf = $information;
        /* Get user modification */
        $query = "SELECT config_key, config_value
            FROM cfg_centreonbroker_info
            WHERE config_id = :config_id
                AND config_group = :group
                AND config_group_id = :group_id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':config_id', $configId, \PDO::PARAM_INT);
        $stmt->bindParam(':group', $moduleType, \PDO::PARAM_STR);
        $stmt->bindParam(':group_id', $nbGroup, \PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $blockConf[$row['config_key']] = $row['config_value'];
        }
        return $blockConf;
    }

    /**
     * Add a module to Centreon Broker configuration
     *
     * @param \XMLWriter $file The xml file
     * @param string $name The module type
     * @param array $configuration The configuration for the module
     * @param bool $isGeneral Is the module is the base configuration
     */
    private function addModule($file, $name, $configuration, $isGeneral = false)
    {
        if (false === $isGeneral) {
            $file->startElement($name);
        }
        foreach ($configuration as $key => $value) {
            if (is_array($value)) {
                $file->startElement($key);
                foreach ($value as $subkey => $subvalue) {
                    $subvalue = str_replace(
                        array_keys($this->baseConfig),
                        array_values($this->baseConfig),
                        $subvalue
                    );
                    if (is_string($subkey)) {
                        $file->writeElement($subkey, $subvalue);
                    }
                }
                $file->endElement();
            } else {
                $value = str_replace(
                    array_keys($this->baseConfig),
                    array_values($this->baseConfig),
                    $value
                );
                $key = str_replace(
                    array_keys($this->baseConfig),
                    array_values($this->baseConfig),
                    $key
                );
                $key = str_replace(array('/','.'),'-',$key);
                $file->writeElement($key, $value);
            }
        }
        if (false === $isGeneral) {
            $file->endElement();
        }
    }

    /**
     * Load macros for replace in default configuration
     *
     * @param int $pollerId The poller id
     */
    private function loadMacros($pollerId)
    {
        $config = Di::getDefault()->get('config');
        /* Load contant values */
        $this->baseConfig['broker_central_ip'] = getHostByName(getHostName());
        /* Load user value */
        $this->baseConfig = array_merge($this->baseConfig, BrokerRepository::loadValues($pollerId));
        /* Load paths */
        $paths = BrokerRepository::getPathsFromPollerId($pollerId);
        $pathsValue = array_values($paths);
        $pathsKeys = array_map(
            function($name) {
                switch ($name) {
                    case 'directory_modules':
                        $str = 'modules_directory';
                        break;
                    case 'directory_config':
                        $str = 'etc_directory';
                        break;
                    case 'directory_logs':
                        $str = 'logs_directory';
                        break;
                    case 'directory_data':
                        $str = 'data_directory';
                        break;
                    default:
                        $str = '';
                        break;
                }
                return 'global_broker_' . $str;
            },
            array_keys($paths)
        );
        $paths = array_combine($pathsKeys, $pathsValue);
        $this->baseConfig = array_merge($this->baseConfig, $paths);
        $this->baseConfig['poller_id'] = $this->pollerId;
        /* Information for database */
        $dbInformation = CentreonDb::parseDsn(
            $config->get('db_centreon', 'dsn'),
            $config->get('db_centreon', 'username'),
            $config->get('db_centreon', 'password')
        );
        $dbKeys = array_map(
            function($name) {
                return 'global_' . $name;
            },
            array_keys($dbInformation)
        );
        $dbInformation = array_combine($dbKeys, array_values($dbInformation));
        $this->baseConfig = array_merge($dbInformation, $this->baseConfig);
        
        /* get global value in database */
        $globalOptions = BrokerRepository::getGlobalValues();
        $this->baseConfig = array_merge($globalOptions, $this->baseConfig);
        
        /* Add % in begin and end of keys */
        $keys = array_keys($this->baseConfig);
        $values = array_values($this->baseConfig);
        $keys = array_map(
            function($key) {
                return '%' . $key . '%';
            },
            $keys
        );
        $this->baseConfig = array_combine($keys, $values);
    }

    /**
     * Prepare default values by module and group
     *
     * @param string $module The module
     * @param string $group The information of current group
     * @return array
     */
    private function getDefaults($module, $group)
    {
        if (isset($this->parsedDefault[$module])) {
            if (false === $group['type']) {
                return $this->parsedDefault[$module];
            } elseif (isset($this->parsedDefault[$module][$group['type']])) {
                return $this->parsedDefault[$module][$group['type']];
            }
        }
        if (false === isset($this->defaults[$module])) {
            return array();
        }
        $values = array();
        foreach ($this->defaults[$module] as $key => $value) {
            if ($key != 'type') {
                $values[$key] = $value;
            } else {
                if (isset($group['type']) && isset($value[$group['type']])) {
                    foreach ($value[$group['type']] as $keyType => $valueType) {
                        $values[$keyType] = $valueType;
                    }
                }
            }
        }
        if (false === isset($this->parsedDefault[$module])) {
            $this->parsedDefault[$module] = array();
        }
        if (isset($group['type'])) {
            $this->parsedDefault[$module][$group['type']] = $values;
        } else {
            $this->parsedDefault[$module] = $values;
        }
        return $values;
    }
}
