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

use Centreon\Internal\Utils\CommandLine\Colorize;
use Centreon\Internal\Module\Informations;
use Centreon\Internal\Di;
use Centreon\Internal\Install\Db;
use Centreon\Internal\Form\Install\Installer as FormInstaller;
use Centreon\Internal\Hook;
use Centreon\Models\Module;

/**
 * Module Abstract Install
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
abstract class Installer
{
    /**
     *
     * @var array 
     */
    protected $moduleInfo;
    
    /**
     *
     * @var string 
     */
    protected $moduleDirectory;
    
    /**
     *
     * @var int 
     */
    protected $moduleId;


    /**
     * 
     * @param string $moduleDirectory
     * @param array $moduleInfo
     */
    public function __construct($moduleDirectory, $moduleInfo)
    {
        $this->moduleInfo = $moduleInfo;
        $this->moduleDirectory = $moduleDirectory;
    }
    
    /**
     * 
     */
    abstract public function customPreInstall();
    
    /**
     * 
     */
    abstract public function customInstall();
    
    /**
     * 
     */
    abstract public function customRemove();
    
    /**
     * 
     */
    public function install($installDefault = true)
    {
        $this->preInstall();
        $this->installDb($installDefault);
        $this->customPreInstall();
        $this->installForms();
        $this->installMenu();
        $this->installHooks();
        $this->customInstall();
        $this->postInstall();
    }
    
    /**
     * 
     */
    public function installMenu()
    {
        $filejson = $this->moduleDirectory . 'install/menu.json';
        Informations::deleteMenus($this->moduleId);
        if (file_exists($filejson)) {
            $menus = json_decode(file_get_contents($filejson), true);
            self::parseMenuArray($this->moduleId, $menus);
        }
    }

    /**
     * @todo After seeing Propel
     */
    public function installDb($installDefault = true)
    {
        // Initialize configuration
        $di = Di::getDefault();
        $config = $di->get('config');
        $dbName = $config->get('db_centreon', 'dbname');
        echo "Updating " . Colorize::colorizeText('centreon', 'blue', 'black', true) . " database... ";
        Db::update($dbName);
        echo Colorize::colorizeText('Done', 'green', 'black', true) . "\n";
        if ($installDefault) {
            Db::loadDefaultDatas($this->moduleDirectory . 'install/datas');
        }
    }
    
    /**
     * @todo After seeing Propel
     */
    public function removeDb()
    {
        
    }
    
    /**
     * @todo Check for form dependencies
     */
    public function installForms()
    {
        $formsFiles = $this->moduleDirectory . '/install/forms/*.xml';
        foreach (glob($formsFiles) as $xmlFile) {
            FormInstaller::installFromXml($this->moduleId, $xmlFile);
        }
    }
    
    /**
     * 
     */
    public function installHooks()
    {
        $hooksFile = $this->moduleDirectory . '/install/hooks.json';
        $moduleHooksFile = $this->moduleDirectory . '/install/registeredHooks.json';
        if (file_exists($hooksFile)) {
            $hooks = json_decode(file_get_contents($hooksFile), true);
            foreach ($hooks as $hook) {
                Hook::insertHook($hook['name'], $hook['description']);
            }
        }
        
        if (file_exists($moduleHooksFile)) {
            $moduleHooks = json_decode(file_get_contents($moduleHooksFile), true);
            foreach ($moduleHooks as $moduleHook) {
                Hook::register(
                    $this->moduleId,
                    $moduleHook['name'],
                    $moduleHook['moduleHook'],
                    $moduleHook['moduleHookDescription']
                );
            }
        }
    }
    
    /**
     * 
     * @return array
     */
    public function isDependenciesSatisfied()
    {
        $dependenciesSatisfied = true;
        $missingDependencies = array();
        foreach ($this->moduleInfo['dependencies'] as $module) {
            if (!Informations::checkDependency($module)) {
                $dependenciesSatisfied = false;
                $missingDependencies[] = $module['name'];
            }
        }
        
        return array(
            'success' => $dependenciesSatisfied,
            'missingDependencies' => $missingDependencies
        );
    }
    
    /**
     * 
     * @throws \Exception
     */
    public function preInstall()
    {
        $newModuleId = Module::getIdByParameter('name', $this->moduleInfo['shortname']);
        if (count($newModuleId) == 0) {
            $params = array(
                'name' => $this->moduleInfo['shortname'],
                'alias' => $this->moduleInfo['name'],
                'description' => $this->moduleInfo['description'],
                'author' => implode(", ", $this->moduleInfo['author']),
                'name' => $this->moduleInfo['shortname'],
                'version' => $this->moduleInfo['version'],
                'isactivated' => '0',
                'isinstalled' => '0',
            );
            Module::insert($params);
            $newModuleId = Module::getIdByParameter('name', $this->moduleInfo['shortname']);
            $this->moduleId = $newModuleId[0];
        } else {
            throw new \Exception("Module already installed");
        }
    }
    
    /**
     * 
     */
    public function postInstall()
    {
        $isinstalled = 1;
        $isactivated = 1;
        
        if (isset($this->moduleInfo['isuninstallable']) && ($this->moduleInfo['isuninstallable'] === false)) {
            $isinstalled = 2;
        }
        
        if (isset($this->moduleInfo['isdisableable']) && ($this->moduleInfo['isdisableable'] === false)) {
            $isactivated = 2;
        }
        
        Module::update(
            $this->moduleId,
            array('isactivated' => $isactivated,'isinstalled' => $isinstalled)
        );
    }
    
    /**
     * 
     */
    public function remove()
    {
        $this->preRemove();
        $this->removeHook();
        $this->removeDb();
        $this->postRemove();
    }
    
    /**
     * 
     */
    public function preRemove()
    {
        if (is_null($this->moduleId)) {
            $this->moduleId = $this->moduleInfo['id'];
        }
    }
    
    /**
     * 
     */
    public function postRemove()
    {
        Module::delete($this->moduleId);
    }
    
    /**
     * 
     */
    public function removeHook()
    {
        $moduleHooksFile = $this->moduleDirectory . '/install/registeredHooks.json';
        if (file_exists($moduleHooksFile)) {
            $moduleHooks = json_decode(file_get_contents($moduleHooksFile), true);
            foreach ($moduleHooks as $moduleHook) {
                Hook::unregister(
                    $this->moduleId,
                    $moduleHook['name'],
                    $moduleHook['moduleHook']
                );
            }
        }
    }
    
    /**
     * 
     * @param int $moduleId
     * @param array $menus
     * @param string $parent
     */
    public static function parseMenuArray($moduleId, $menus, $parent = null)
    {
        $i = 1;
        foreach ($menus as $menu) {
            if (!is_null($parent)) {
                $menu['parent'] = $parent;
            }
            $menu['module'] = $moduleId;
            Informations::setMenu($menu);
            if (isset($menu['menus']) && count($menu['menus'])) {
                self::parseMenuArray($moduleId, $menu['menus'], $menu['short_name']);
            }
        }
    }
}
