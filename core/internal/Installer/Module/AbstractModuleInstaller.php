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
namespace Centreon\Internal\Installer\Module;

use Centreon\Internal\Module\Informations;
use Centreon\Internal\Installer\StaticFiles;
use Centreon\Internal\Module\Dependency;
use Centreon\Internal\Utils\CommandLine\Colorize;
use Centreon\Internal\Utils\CommandLine\InputOutput;
use Centreon\Internal\Utils\Dependency\PhpDependencies;
use Centreon\Internal\Exception\Module\MissingDependenciesException;

class AbstractModuleInstaller
{
    /**
     *
     * @var type 
     */
    protected $moduleSlug;
    
    /**
     *
     * @var type 
     */
    protected $moduleFullName;
    
    /**
     *
     * @var type 
     */
    protected $moduleDescription;
    
    /**
     *
     * @var type 
     */
    protected $moduleInfo;
    
    /**
     *
     * @var type 
     */
    protected $moduleDirectory;
    
    /**
     *
     * @var type 
     */
    protected $launcher;


    /**
     * 
     * @param type $moduleDirectory
     * @param type $moduleInfo
     * @param type $launcher
     */
    public function __construct($moduleDirectory, $moduleInfo, $launcher)
    {
        $this->moduleInfo = $moduleInfo;
        $this->moduleDirectory = $moduleDirectory;
        $this->launcher = $launcher;
        $this->moduleFullName = $this->moduleInfo['name'];
        $this->moduleSlug = $this->moduleInfo['shortname'];
    }
    
    /**
     * Perform Install operation for module
     * 
     * @param type $verbose
     */
    public function install()
    {
        // Starting Message
        $message = _("Starting installation of %s module");
        $this->displayOperationMessage(
            $this->colorizeMessage(
                sprintf($message, $this->moduleFullName),
                'info'
            )
        );
        
        // Performing pre operation check
        $this->checkOperationValidity('install');
        
        // Deploy module Static files
        $this->deployStaticFiles();
        
        // Ending Message
        $message = _("Installation of %s module complete");
        $this->displayOperationMessage(
            $this->colorizeMessage(
                sprintf($message, $this->moduleFullName),
                'success'
            )
        );
    }
    
    /**
     * Perform upgrade operation for module
     * 
     * @param type $verbose
     */
    public function upgrade()
    {
        // Starting Message
        $message = _("Starting upgrade of %s module");
        $this->displayOperationMessage(
            $this->colorizeMessage(
                sprintf($message, $this->moduleFullName),
                'info'
            )
        );
        
        // Performing pre operation check
        $this->checkOperationValidity('upgrade');
        
        // Remove old static files and deploy new ones
        $this->removeStaticFiles();
        $this->deployStaticFiles();
        
        // Ending Message
        $message = _("Upgrade of %s module complete");
        $this->displayOperationMessage(
            $this->colorizeMessage(
                sprintf($message, $this->moduleFullName),
                'success'
            )
        );
    }
    
    /**
     * Perform uninstall operation for module
     * 
     * @param type $verbose
     */
    public function uninstall()
    {
        // Starting Message
        $message = _("Starting removal of %s module");
        $this->displayOperationMessage(
            $this->colorizeMessage(
                sprintf($message, $this->moduleFullName),
                'info'
            )
        );
        
        // Performing pre operation check
        $this->checkOperationValidity('uninstall');
        
        // Remove old static files
        $this->removeStaticFiles();
        
        // Ending Message
        $message = _("Removal of %s module complete");
        $this->displayOperationMessage(
            $this->colorizeMessage(
                sprintf($message, $this->moduleFullName),
                'success'
            )
        );
    }
    
    /**
     * 
     * Deploy module's static files
     */
    protected function deployStaticFiles()
    {
        StaticFiles::deploy($this->moduleSlug);
    }
    
    /**
     * 
     * Remove module's static files
     */
    protected function removeStaticFiles()
    {
        StaticFiles::remove($this->moduleSlug);
    }
    
    /**
     * 
     * @param type $message
     */
    protected function displayOperationMessage($message, $withEndOfLine = true)
    {
        if ($this->launcher == 'console') {
            InputOutput::display($message, $withEndOfLine);
        } elseif ($this->launcher == 'web') {
            
        }
    }
    
    /**
     * 
     * @param type $text
     * @param type $color
     * @param type $background
     * @param type $bold
     * @return type
     */
    protected function colorizeText($text, $color = 'white', $background = 'black', $bold = false)
    {
        $finalMessage = '';
        
        if ($this->launcher == 'console') {
            $finalMessage .= Colorize::colorizeText($text, $color, $background, $bold);
        } elseif ($this->launcher == 'web') {
            
        }
        
        return $finalMessage;
    }
    
    /**
     * 
     * @param type $message
     * @param type $status
     * @param type $background
     * @return type
     */
    protected function colorizeMessage($message, $status = 'success', $background = 'black')
    {
        $finalMessage = '';
        
        if ($this->launcher == 'console') {
            $finalMessage .= Colorize::colorizeMessage($message, $status, $background);
        } elseif ($this->launcher == 'web') {
            
        }
        
        return $finalMessage;
    }


    /**
     * 
     * @throws MissingDependenciesException
     */
    protected function checkModulesDependencies()
    {
        $dependenciesSatisfied = true;
        $missingDependencies = array();
        foreach ($this->moduleInfo['dependencies'] as $module) {
            if (!Informations::checkDependency($module)) {
                $dependenciesSatisfied = false;
                $missingDependencies[] = $module['name'];
            }
        }
        
        if ($dependenciesSatisfied === false) {
            $exceptionMessage = _("The following dependencies are not satisfied") . " :\n";
            $exceptionMessage .= implode("\n    - ", $missingDependencies);
            throw new MissingDependenciesException($this->colorizeMessage($exceptionMessage, 'red'), 1104);
        }
    }
    
    /**
     * 
     * @throws MissingDependenciesException
     */
    protected function checkSystemDependencies()
    {
        $status = PhpDependencies::checkDependencies($this->moduleInfo['php module dependencies'], false);
        if ($status['success'] === false) {
            $exceptionMessage = _("The following dependencies are not satisfied") . " :\n";
            $exceptionMessage .= implode("\n    - ", $status['errors']);
            throw new MissingDependenciesException($this->colorizeMessage($exceptionMessage, 'red'), 1004);
        }
    }
    
    /**
     * 
     * @param type $operation
     */
    protected function checkOperationValidity($operation)
    {
        $message = $this->colorizeText(_("Checking operation validity..."));
        $this->displayOperationMessage($message, false);
        
        if ($operation === 'uninstall') {
            
        } else {
            // Check modules dependencies
            $this->checkModulesDependencies();
        
            // Check system dependencies
            $this->checkSystemDependencies();
        }
        
        $message = $this->colorizeMessage(_("     Done"), 'green');
        $this->displayOperationMessage($message);
    }
}
