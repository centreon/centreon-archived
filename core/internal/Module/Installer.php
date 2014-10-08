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
 *
 */

namespace Centreon\Internal\Module;

use \Centreon\Internal\Utils\CommandLine\Colorize;

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
     * @var type 
     */
    protected $moduleDirectory;
    
    /**
     *
     * @var int 
     */
    protected $moduleId;


    /**
     * 
     * @param type $moduleDirectory
     * @param type $moduleInfo
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
        $di = \Centreon\Internal\Di::getDefault();
        $config = $di->get('config');
        $dbName = $config->get('db_centreon', 'dbname');
        echo "Updating " . Colorize::colorizeText('centreon', 'blue', 'black', true) . " database... ";
        \Centreon\Internal\Install\Db::update($dbName);
        echo Colorize::colorizeText('Done', 'green', 'black', true) . "\n";
        if ($installDefault) {
            \Centreon\Internal\Install\Db::loadDefaultDatas($this->moduleDirectory . 'install/datas');
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
            \Centreon\Internal\Form\Installer::installFromXml($this->moduleId, $xmlFile);
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
                \Centreon\Internal\Hook::insertHook($hook['name'], $hook['description']);
            }
        }
        
        if (file_exists($moduleHooksFile)) {
            $moduleHooks = json_decode(file_get_contents($moduleHooksFile), true);
            foreach ($moduleHooks as $moduleHook) {
                \Centreon\Internal\Hook::register(
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
            if (!\Centreon\Internal\Module\Informations::checkDependency($module)) {
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
        $newModuleId = \Centreon\Models\Module::getIdByParameter('name', $this->moduleInfo['shortname']);
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
            \Centreon\Models\Module::insert($params);
            $newModuleId = \Centreon\Models\Module::getIdByParameter('name', $this->moduleInfo['shortname']);
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
        
        \Centreon\Models\Module::update(
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
        \Centreon\Models\Module::delete($this->moduleId);
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
                \Centreon\Internal\Hook::unregister(
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
            $menu['order'] = $i;
            if (!is_null($parent)) {
                $menu['parent'] = $parent;
            }
            $menu['module'] = $moduleId;
            \Centreon\Internal\Module\Informations::setMenu($menu);
            if (isset($menu['menus']) && count($menu['menus'])) {
                self::parseMenuArray($moduleId, $menu['menus'], $menu['short_name']);
            }
            $i++;
        }
    }
}
