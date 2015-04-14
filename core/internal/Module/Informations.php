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
 *
 */

namespace Centreon\Internal\Module;

use Centreon\Internal\Di;
use Centreon\Internal\Utils\String\CamelCaseTransformation;
use Centreon\Models\Module;

/**
 * Gives informations about modules
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class Informations
{
    /**
     * 
     * @param array $module
     * @return boolean
     */
    public static function checkDependency($module)
    {
        $dependencySatisfied = false;
        $db = Di::getDefault()->get('db_centreon');
        $sql = "SELECT name, version FROM cfg_modules WHERE name = '$module[name]'";
        $res = $db->query($sql);
        $dependency = $res->fetchAll(\PDO::FETCH_ASSOC);
        
        if (is_array($dependency) && count($dependency) > 0) {
            if (version_compare($dependency[0]['version'], $module['version'], '>=')) {
                $dependencySatisfied = true;
            }
        }
        
        return $dependencySatisfied;
    }
    
    /**
     * 
     * @param string $moduleName
     * @return boolean
     */
    public static function isModuleActivated($moduleName)
    {
        $moduleId = self::getModuleIdByName($moduleName);
        $result = Module::getParameters($moduleId, 'isactivated');
        return (boolean)$result['isactivated'];
    }
    
    /**
     * 
     * @param string $moduleName
     * @return boolean
     */
    public static function isModuleInstalled($moduleName)
    {
        $isinstalled = false;
        $moduleId = self::getModuleIdByName($moduleName);
        if ($moduleId != false) {
            $result = Module::getParameters($moduleId, 'isinstalled');
            $isinstalled = (boolean)$result['isinstalled'];
        }
        return $isinstalled;
    }
    
    /**
     * Chzeck to see if the module routes can be reached
     * @return boolean
        */
    public static function isModuleReachable($moduleName)
    {
        $isReachable = false;
        if (self::isModuleInstalled($moduleName)) {
            if (self::isModuleActivated($moduleName)) {
                $isReachable = true;
            }
        }
        
        return $isReachable;
    }
    
    /**
     * 
     * @param type $moduleName
     * @return type
     */
    public static function getModuleIdByName($moduleName)
    {
        $returnValue = false;
        $resultModule = Module::getIdByParameter('name', $moduleName);
        
        if (count($resultModule) > 0) {
            $returnValue = $resultModule[0];
        }
        
        return $returnValue;
    }
    
    /**
     *
     * @param bool $onlyActivated If list only module activated
     * @return array Array of module names (string)
     */
    public static function getModuleList($onlyActivated = 1)
    {
        $moduleList = array();
        $activated = array('0', '1', '2');
        if ($onlyActivated == 1) {
            $activated = array('1', '2');
        }
        
        try {
            $rawModuleList = Module::getList(
                'name',
                -1,
                0,
                null,
                "ASC",
                array('isactivated' => $activated)
                );

            foreach ($rawModuleList as $module) {
                $moduleList[] = $module['name'];
            }
        } catch (\PDOException $e) {
            
        }
        
        return $moduleList;
    }

    /**
     *
     * @param bool $onlyActivated If list only module activated
     * @return array Array of arrays describing modules
     */
    public static function getModuleExtendedList($onlyActivated = 1)
    {
        $activated = array('0', '1', '2');
        if ($onlyActivated == 1) {
            $activated = array('1', '2');
        }

        try {
            $rawModuleList = Module::getList(
                '*',
                -1,
                0,
                null,
                "ASC",
                array('isactivated' => $activated)
            );

        } catch (\PDOException $e) {

        }

        return $rawModuleList;
    }

    /**
     * 
     * @return array
     */
    public static function getCoreModuleList()
    {
        $coreModuleList = array(
            'centreon-main',
            'centreon-security',
            'centreon-administration',
            'centreon-configuration',
            'centreon-realtime',
            'centreon-customview',
            'centreon-bam',
        );
        
        return $coreModuleList;
    }
    
    /**
     * 
     * @param type $moduleName
     * @return type
     */
    public static function getModulePath($moduleName)
    {
        $path = rtrim(Di::getDefault()->get('config')->get('global', 'centreon_path'), '/');
        $realPath = $path . '/modules/' . self::getModuleCommonName($moduleName) . 'Module/';
        return realpath($realPath);
    }

    /**
     * Returns the name of the module from any given path
     *
     * @param string $path
     * @return string
     */
    public static function getModuleFromPath($path)
    {
        $centreonPath = Di::getDefault()->get('config')->get('global', 'centreon_path');
        $path = str_replace($centreonPath, '', $path);
        $module = "";
        if (preg_match('/modules\/([A-Za-z]+)Module\//', $path, $matches)) {
            $module = $matches[1];
        }
        
        return $module;
    }

    /**
     * Returns the slug name from camelcased module name
     *
     * @param string $moduleName
     * @return string
     */
    public static function getModuleSlugName($moduleName)
    {
        $slugName = ltrim(strtolower(preg_replace('/[A-Z]/', '-$0', $moduleName)), '-');

        // remove the suffix
        return str_replace('-module', '', $slugName);
    }

    /**
     * 
     * @param string $moduleName
     * @return string
     */
    public static function getModuleCommonName($moduleName)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $moduleName)));
    }
    
    /**
     * 
     * @param string $canonicalName
     * @param array $result
     */
    public static function isCanonicalNameValid($canonicalName, &$result)
    {
        $canonicalNameNotOk = true;
        $error = "";
        if (!CamelCaseTransformation::isCamelCase($canonicalName)) {
            $canonicalNameNotOk = false;
            $error = "The given canonical name is not in CamelCase";
        } elseif (ucwords(substr($canonicalName, -6)) === "Module") {
            $canonicalNameNotOk = false;
            $error = "The given canonical name contains 'Module' at the end";
        } elseif (self::isModuleCanonicalExists($canonicalName)) {
            $canonicalNameNotOk = false;
            $error = "A module with the same canonical name already exist in your centreon";
        }
        
        $result['success'] = $canonicalNameNotOk;
        $result['message'] = $error;
    }
    
    /**
     * 
     * @param string $canonicalName
     * @return boolean
     */
    public static function isModuleCanonicalExists($canonicalName)
    {
        $moduleExists = false;
        $config =  Di::getDefault()->get('config');
        $centreonPath = rtrim($config->get('global', 'centreon_path'), '/');
        if (file_exists($centreonPath . '/modules/' . $canonicalName .'Module')) {
            $moduleExists = true;
        }
        return $moduleExists;
    }

    /**
     * Set menu entry
     * Inserts into database if short_name does not exist, otherwise it just updates entry
     *
     * @param array $data (name, short_name, parent, route, icon_class, icon, bgcolor, order, module)
     */
    public static function setMenu($data)
    {
        $menus = null;

        $db = Di::getDefault()->get('db_centreon');
        if (is_null($menus)) {
            $sql = "SELECT menu_id, short_name FROM cfg_menus";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $menus[$row['short_name']] = $row['menu_id'];
            }
        }
        $mandatoryKeys = array('name', 'short_name');
        foreach ($mandatoryKeys as $k) {
            if (!isset($data[$k])) {
                throw new Exception(sprintf('Missing mandatory key %s', $k));
            }
        }
        
        if (isset($data['parent']) && !isset($menus[$data['parent']])) {
            throw new Exception(sprintf('Parent %s does not exist', $data['parent']));
        }
        
        if (!isset($menus[$data['short_name']])) {
            $sql = "INSERT INTO cfg_menus 
                (name, short_name, parent_id, url, icon_class, icon, bgcolor, menu_order, module_id, menu_block) VALUES
                (:name, :short_name, :parent, :route, :icon_class, :icon, :bgcolor, :order, :module, :menu_block)";
        }
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':short_name', $data['short_name']);
        $parent = isset($data['parent']) && isset($menus[$data['parent']])? $menus[$data['parent']] : null;
        $stmt->bindParam(':parent', $parent);
        $stmt->bindParam(':route', $data['route']);
        $icon_class = isset($data['icon_class']) ? $data['icon_class'] : null;
        $stmt->bindParam(':icon_class', $icon_class);
        $icon = isset($data['icon']) ? $data['icon'] : null;
        $stmt->bindParam(':icon', $icon);
        $bgcolor = isset($data['bgcolor']) ? $data['bgcolor'] : null;
        $stmt->bindParam(':bgcolor', $bgcolor);
        $order = isset($data['order']) ? $data['order'] : null;
        $stmt->bindParam(':order', $order);
        $module = isset($data['module']) ? $data['module'] : 0;
        $stmt->bindParam(':module', $module);
        $menuBlock = 'root';
        if (isset($data['block'])) {
            $menuBlock = $data['block'];
        } elseif (isset($data['parent'])) {
            $menuBlock = 'submenu';
        }
        $stmt->bindParam(':menu_block', $menuBlock, \PDO::PARAM_STR);
        $stmt->execute();
    }
    
    /**
     * 
     * @param type $moduleName
     * @return \Centreon\Commands\classCall
     * @throws \Exception
     */
    public static function getModuleInstaller($moduleName, $moduleId = null)
    {
        $config =  Di::getDefault()->get('config');
        $centreonPath = rtrim($config->get('global', 'centreon_path'), '/');
        
        $commonName = str_replace(' ', '', ucwords(str_replace('-', ' ', $moduleName)));
        
        $moduleDirectory = $centreonPath
            . '/modules/'
            . $commonName
            . 'Module/';
        
        if (!file_exists(realpath($moduleDirectory . 'install/config.json'))) {
            throw new \Exception("The module is not valid because of a missing configuration file");
        }
        $moduleInfo = json_decode(file_get_contents($moduleDirectory . 'install/config.json'), true);
        
        // Launched Install
        $classCall = '\\'.$commonName.'\\Install\\Installer';
        
        if (isset($moduleId)) {
            $moduleInfo['id'] = $moduleId;
        } else {
            $retrievedId = self::getModuleIdByName($moduleName);
            if ($retrievedId !== false) {
                $moduleInfo['id'] = $retrievedId;
            }
        }
        
        $moduleInstaller = new $classCall($moduleDirectory, $moduleInfo);
        
        return $moduleInstaller;
    }
}
