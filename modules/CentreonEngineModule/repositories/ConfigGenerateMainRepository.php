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

namespace CentreonEngine\Repository;

use Centreon\Internal\Di;
use Centreon\Internal\Exception;
use CentreonConfiguration\Models\Poller;
use CentreonConfiguration\Events\BrokerModule as BrokerModuleEvent;

/**
 * Factory for ConfigGenerate Engine For centengine.cfg
 *
 * @author Sylvestre Ho <sho@merethis.com>
 * @version 3.0.0
 */

class ConfigGenerateMainRepository
{
    /**
     * @var string
     */
    protected static $path;

    /**
     * Final etc path
     *
     * @var string
     */
    protected static $finalPath;

    /**
     * Method for generating Main configuration file
     * 
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     * @param int $testing
     */
    public static function generate(& $filesList, $poller_id, $path, $filename, $testing = 0)
    {
        static::$path = rtrim($path, '/');

        /* Get Content */
        $content = static::getContent($poller_id, $filesList, $testing);

        /* Write Check-Command configuration file */
        WriteConfigFileRepository::writeParamsFile($content, $path.$poller_id."/".$filename, $filesList, $user = "API");
        unset($content);
    }

    /**
     * 
     * @param array $filesList
     * @param array $content
     * @param int $testing
     * @param int $pollerId
     * @return array
     */
    private static function getFilesList($filesList, $content, $testing, $pollerId)
    {
        $di = Di::getDefault();

        $tmpPath = static::$path;
        $engineEtcPath = static::$finalPath;
        
        foreach ($filesList as $category => $data) {
            if ($category != 'main_file') {
                foreach ($data as $path) {
                    if (!isset($content[$category])) {
                        $content[$category] = array();
                    }
                    if (!$testing) {
                        $path = str_replace("{$tmpPath}/{$pollerId}/", "{$engineEtcPath}/", $path);
                    }
                    $content[$category][] = $path;
                }
            }
        }
        return $content;
    }

    /**
     * 
     * @param int $poller_id
     * @param array $filesList
     * @param int $testing
     * @return array
     */
    private static function getContent($poller_id, & $filesList, $testing)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Init Content Array */
        $content = array();

        /* Default values that can be overwritten by template and user */
        $defaultValues = static::getDefaultValues();

        /* Template values that can be overwritten by user */
        $templateValues = static::getTemplateValues($poller_id);

        /* For command name resolution */
        $commandIdFields = static::getCommandIdField();
        
        /* Get configuration files */
        $content = static::getFilesList($filesList, $content, $testing, $poller_id);

        /* Get values from the table, those are saved by user */
        $query = "SELECT * FROM cfg_engine WHERE poller_id = ?";
        $stmt = $dbconn->prepare($query);
        $stmt->execute(array($poller_id));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!count($row)) {
            throw new Exception(sprintf('Could not find parameters for poller %s.', $poller_id));
        }
        $userValues = array();
        foreach ($row as $key => $val) {
            if (!is_null($val) && $val != "") {
                $userValues[$key] = $val;
            }
        }

        /* Overwrite parameter */
        $tmpConf = array_merge($defaultValues, $templateValues);
        $finalConf = array_merge($tmpConf, $userValues);

        /* Set real etc path of the poller */
        static::$finalPath = $finalConf['conf_dir'];

        /* Replace path macros */
        foreach ($finalConf as $k => $v) {
            if (preg_match('/%([a-z_]+)%/', $v, $matches)) {
                $macro = $matches[1];
                if (isset($finalConf[$macro])) {
                    $finalConf[$macro] = rtrim($finalConf[$macro], '/');
                    $finalConf[$k] = str_replace("%{$macro}%", $finalConf[$macro], $v);
                }
                if ($macro == 'conf_dir' && $testing) {
                    $finalConf[$k] = str_replace('%conf_dir%', static::$path . "/" . $poller_id, $v);
                }
            }
        }

        /* Replace commands */
        foreach ($commandIdFields as $fieldName) {
            if (isset($finalConf[$fieldName]) && $finalConf[$fieldName]) {
                $finalConf[$fieldName] = CommandRepository::getCommandName($finalConf[$fieldName]);
            }
        }

        /* Write broker modules */
        $finalConf['broker_module'] = static::getBrokerConf($poller_id, $finalConf['module_dir']);

        /* Exclude parameters */
        static::unsetParameters($finalConf);

        return $finalConf;
    }

    /**
     * Unset unwanted parameters for generation
     *
     * @param array $finalConf
     */
    private static function unsetParameters(& $finalConf)
    {
        unset($finalConf['engine_id']);
        unset($finalConf['poller_id']);
        unset($finalConf['conf_dir']);
        unset($finalConf['log_dir']);
        unset($finalConf['var_lib_dir']);
        unset($finalConf['module_dir']);
        unset($finalConf['init_script']);
    }

    /**
     * 
     * @param int $poller_id
     * @return array
     */
    private static function getConfigFiles($poller_id)
    {
        $pathList = array();
        $resList = array();
        $dirList = array();
        
        $path = static::$path;
        
        /* Check that that basic path exists */
        if (!file_exists($path)) {
            if (!is_dir($path)) {
                mkdir($path);
            }
        }

        /* Check that poller directory exists */
        if (!file_exists($path)) {
            if (!is_dir($path)) {
                mkdir($path);
            }
        }

        /* Check that Object directory exists */
        if (!file_exists($path."/objects/")) {
            if (!is_dir($path."/objects/")) {
                mkdir($path."/objects/");
            }
        }

        /* Check that Ressources directory exists */
        if (!file_exists($path."/resources/")) {
            if (!is_dir($path."/resources/")) {
                mkdir($path."/resources/");
            }
        }

        /* Add fixed path files */
        $resList[] = $path."/resources.cfg";
        $pathList[] = $path."/misc-command.cfg";
        $pathList[] = $path."/check-command.cfg";
        $pathList[] = $path."/timeperiods.cfg";
        $pathList[] = $path."/connectors.cfg";
        
        $dirList[] = $path."/objects/";
        $dirList[] = $path."/resources/";

        return array("cfg_file" => $pathList, "resource_file" => $resList, "cfg_dir" => $dirList);
    }

    /**
     * Returns an array of broker module directives 
     *
     * @param int $pollerId
     * @param string $moduleDir
     * @return array
     */
    private static function getBrokerConf($pollerId, $moduleDir)
    {
        /* Retrieve broker modules */
        $events = Di::getDefault()->get('events');
        $moduleEvent = new BrokerModuleEvent($pollerId);
        $events->emit('centreon-configuration.broker.module', array($moduleEvent));
        $brokerModules = $moduleEvent->getModules();
        
        /* External command module */
        $brokerModules[] = rtrim($moduleDir, '/') . '/externalcmd.so';

        return $brokerModules;
    }

    /**
     * Returns the default configuration values of value
     * Those values are stored in the default.json file
     * 
     * @return array
     * @throws \Centreon\Internal\Exception
     */
    private static function getDefaultValues()
    {
        $config = Di::getDefault()->get('config');

        $centreonPath = rtrim($config->get('global', 'centreon_path'), '/');
        $jsonFile = "{$centreonPath}/modules/CentreonEngineModule/data/default.json";
        if (!file_exists($jsonFile)) {
            throw new Exception('Default engine configuration JSON file not found');
        }
        $defaultValue = json_decode(file_get_contents($jsonFile), true);

        return $defaultValue;
    }

    /**
     * Returns the template configuration values
     * 
     * @param int $pollerId
     * @return array
     * @throws \Centreon\Internal\Exception
     */
    private static function getTemplateValues($pollerId)
    {
        $templateValues = array(); 

        /* Retrieve template name  */
        $pollerParam = Poller::get($pollerId, 'tmpl_name');
        if (!isset($pollerParam['tmpl_name']) || is_null($pollerParam['tmpl_name'])) {
            return $templateValues;
        }

        /* Look for template file */
        $config = Di::getDefault()->get('config');
        $centreonPath = rtrim($config->get('global', 'centreon_path'), '/');
        $pollerParam['tmpl_name'] = strtolower($pollerParam['tmpl_name']);
        $jsonFile = "{$centreonPath}/modules/CentreonEngineModule/pollers/{$pollerParam['tmpl_name']}.json";
        if (!file_exists($jsonFile)) {
            throw new Exception('Engine template file not found: ' . $pollerParam['tmpl_name'] . '.json');
        }

        /* Checks whether or not template file has all the sections */
        $arr = json_decode(file_get_contents($jsonFile), true);
        if (!isset($arr['content']) || !isset($arr['content']['engine']) || 
            !isset($arr['content']['engine']['setup'])) {
                return $templateValues;
        }

        /* Retrieve parameter values */
        foreach ($arr['content']['engine']['setup'] as $setup) {
            if (isset($setup['params'])) {
                foreach ($setup['params'] as $k => $v) {
                    $templateValues[$k] = $v;
                }
            }
        }
        return $templateValues;
    }

    /**
     * 
     * @return int
     */
    private static function getCommandIdField()
    {
        $commands = array(
            'global_host_event_handler',
            'global_service_event_handler',
            'ocsp_command',
            'ochp_command'
        );
        return $commands;
    }
}
