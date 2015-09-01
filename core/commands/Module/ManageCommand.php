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

namespace Centreon\Commands\Module;

use Centreon\Internal\Installer\Form;
use Centreon\Internal\Module\Informations;
use Centreon\Internal\Installer\StaticFiles;
use Centreon\Internal\Installer\Versioning;
use Centreon\Internal\Command\AbstractCommand;
use Centreon\Internal\Utils\CommandLine\Colorize;

/**
 * Command line for module management
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class ManageCommand extends AbstractCommand
{
    /**
     *
     * @var array 
     */
    public $options = array();
    
    /**
     * 
     * @param string $object
     * @param array $params
     * @cmdObject string module the host
     * @cmdParam boolean|true verbose optional is verbose ?
     */
    public function installAction($object, $params = null)
    {
        $moduleInstaller = Informations::getModuleInstaller('console', $object['module']);
        $moduleInstaller->install($params['verbose']);
    }
    
    /**
     * 
     * @param string $object
     * @param array $params
     * @cmdObject string module the host
     * @cmdParam boolean|true force required Force the upgrade
     * @cmdParam boolean|true verbose required is verbose ?
     */
    public function upgradeAction($object, $params = null)
    {
        $moduleInstaller = Informations::getModuleInstaller('console', $object['module']);
        $moduleInstaller->setForceMode($params['force']);
        $moduleInstaller->upgrade($params['verbose']);
    }
    
    /**
     * 
     * @param string $object
     * @param array $params
     * @cmdObject string module the host
     * @cmdParam boolean|true verbose optional is verbose ?
     */
    public function uninstallAction($object, $params = null)
    {
        $moduleInstaller = Informations::getModuleInstaller('console', $object['module']);
        $moduleInstaller->uninstall($params['verbose']);
    }
    
    /**
     * 
     * @param string $object
     * @param array $params
     * @cmdObject string module the host
     * @cmdParam boolean|true removeOld optional is verbose ?
     */
    public function deployStaticAction($object, $params = null)
    {
        echo Colorize::colorizeMessage("Deployment of statics...", "info");
        try {
            if ($params['removeOld'] == true) {
                StaticFiles::remove($object['module']);
            }
            StaticFiles::deploy($object['module']);
            echo Colorize::colorizeMessage("     Done", "success");
        } catch (FilesystemException $ex) {
            throw new \Exception("     ".$ex->getMessage(), 1);
        }
    }
    
    /**
     * 
     * @param string $object
     * @cmdObject string module the host
     */
    public function deployFormsAction($object)
    {
        echo Colorize::colorizeMessage("Deployment of Forms...", "info");
        
        try {
            $modulePath = Informations::getModulePath($object['module']);
            $moduleId = Informations::getModuleIdByName($object['module']);
            $formsFiles = $modulePath . '/install/forms/*.xml';
            foreach (glob($formsFiles) as $xmlFile) {
                Form::installFromXml($moduleId, $xmlFile);
            }

            echo Colorize::colorizeMessage("     Done", "success");

        } catch (\Exception $ex) {
            throw new \Exception("     ".$ex->getMessage(), 1);
        }
        
    }
    
    /**
     * 
     * @param type $object
     * @cmdObject string module Module name
     * @cmdObject string version version to compare
     */
    public function compareVersionAction($object)
    {
        $module = $object['module'];
        $version = $object['version'];
        
        $versionManager = new Versioning($module);
        $versionManager->compareVersion($version);
    }
}
