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

namespace Centreon\Internal\Module;

use Centreon\Internal\Di;
use Centreon\Internal\Utils\String\CamelCaseTransformation;
use Centreon\Models\Module;
use CentreonMain\Models\ModuleDependency;
use Centreon\Internal\Installer\Versioning;

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
        $sql = "SELECT name, version FROM cfg_modules WHERE name = :moduleName";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':moduleName', $module['name'], \PDO::PARAM_STR);
        $stmt->execute();
        $dependency = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (is_array($dependency) && count($dependency) > 0) {
            if (Versioning::compareVersion($dependency[0]['version'], $module['version'])) {
                $dependencySatisfied = true;
            }
        }
        
        return $dependencySatisfied;
    }
    
    /**
     * 
     * @param string $module
     * @param string $params
     * @return array
     */
    public static function getChildren($module, $params = 'name')
    {
        $finalChildrenList = array();
        $currentModuleId = static::getModuleIdByName($module);
        $childrenList = ModuleDependency::getList('child_id', -1, 0, null, "ASC", array('parent_id' => $currentModuleId));
        
        foreach ($childrenList as $child) {
            $childInfo = Module::get($child['child_id'], $params);
            if (count($childInfo) > 0) {
                $finalChildrenList[] = $childInfo;
            }
        }
        
        return $finalChildrenList;
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
     * Check to see if the module routes can be reached
     * 
     * @param sting $moduleName
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
     * @param string $moduleName
     * @return boolean
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
     * @param integer $onlyActivated If list only module activated
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
                'name',
                'ASC',
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
     * @param integer $onlyActivated If list only module activated
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
                'name',
                'ASC',
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
        );
        
        return $coreModuleList;
    }
    
    /**
     * 
     * @param string $moduleName
     * @return string
     */
    public static function getModulePath($moduleName)
    {
        $path = rtrim(Di::getDefault()->get('config')->get('global', 'centreon_path'), '/');
        
        $realPath = '';
        if ($moduleName == 'core') {
            $realPath .= $path;
        } else {
            $realPath .= $path . '/modules/' . self::getModuleCommonName($moduleName) . 'Module/';
        }
        
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
     * @param string $moduleName
     * @return array
     */
    public static function getModuleTables($moduleName)
    {
        $tableList = array();
        $tableFilesPath = static::getModulePath($moduleName) . '/install/db/centreon/*.xml';
        
        $tableFiles = glob($tableFilesPath);
        
        
        foreach ($tableFiles as $tableFile) {
            $tableList[] = basename($tableFile, '.xml');
        }
        
        return $tableList;
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
                throw new \Exception(sprintf('Missing mandatory key %s', $k));
            }
        }
        
        if (isset($data['parent']) && !isset($menus[$data['parent']])) {
            throw new \Exception(sprintf('Parent %s does not exist', $data['parent']));
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
     * @param integer $moduleId
     */
    public static function deleteMenus($moduleId)
    {
        $db = Di::getDefault()->get('db_centreon');
        
        $sql = "DELETE FROM cfg_menus WHERE module_id = :module_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':module_id', $moduleId, \PDO::PARAM_INT);
        $stmt->execute();
    }
    
    /**
     * 
     * @param string $launcher
     * @param string $moduleName
     * @param integer $moduleId
     * @return \Centreon\Internal\Module\classCall
     * @throws \Exception
     */
    public static function getModuleInstaller($launcher, $moduleName, $moduleId = null)
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
        
        $moduleInstaller = new $classCall($moduleDirectory, $moduleInfo, $launcher);
        
        return $moduleInstaller;
    }
}
