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

namespace Centreon\Commands\Module;

use Centreon\Internal\Module\Informations;
use Centreon\Internal\Command\AbstractCommand;
use Centreon\Internal\Installer\StaticFiles;
use Centreon\Internal\Installer\Form;

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
    
    public $options = array();
    
    /*public $options = array(
        "installAction" => array(
            "module" => array(
                "functionParams" => "module",
                "help" => "The module name",
                "type" => "string",
                "toTransform" => "",
                "multiple" => false,
                "required" => true
            ),
            "verbose" => array(
                "functionParams" => "verbose",
                "help" => "verbose",
                "type" => "number",
                "toTransform" => "",
                "multiple" => false,
                "required" => false
            )
        ),
        "upgradeAction" => array(
            "module" => array(
                "functionParams" => "module",
                "help" => "The module name",
                "type" => "string",
                "toTransform" => "",
                "multiple" => false,
                "required" => true
            ),
            "verbose" => array(
                "functionParams" => "verbose",
                "help" => "verbose",
                "type" => "number",
                "toTransform" => "",
                "multiple" => false,
                "required" => false
            )
        ),
        "uninstallAction" => array(
            "module" => array(
                "functionParams" => "module",
                "help" => "The module name",
                "type" => "string",
                "toTransform" => "",
                "multiple" => false,
                "required" => true
            ),
            "verbose" => array(
                "functionParams" => "verbose",
                "help" => "verbose",
                "type" => "number",
                "toTransform" => "",
                "multiple" => false,
                "required" => false
            )
        ),
        "deployStaticAction" => array(
            "module" => array(
                "functionParams" => "module",
                "help" => "The module name",
                "type" => "string",
                "toTransform" => "",
                "multiple" => false,
                "required" => true
            ),
            "removeOld" => array(
                "functionParams" => "removeOld",
                "help" => "removeOld",
                "type" => "number",
                "toTransform" => "",
                "multiple" => false,
                "required" => false
            )
        ),
        "deployFormsAction" => array(
            "module" => array(
                "functionParams" => "module",
                "help" => "The module name",
                "type" => "string",
                "toTransform" => "",
                "multiple" => false,
                "required" => true
            )
        )
    );*/
            
            
    /**
     * 
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
     * @cmdObject string module the host
     * @cmdParam boolean|true verbose optional is verbose ?
     */
    public function upgradeAction($object, $params = null)
    {
        $moduleInstaller = Informations::getModuleInstaller('console', $object['module']);
        $moduleInstaller->upgrade($params['verbose']);
    }
    
    /**
     * 
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
     * @cmdObject string module the host
     * @cmdParam boolean|true removeOld optional is verbose ?
     */
    public function deployStaticAction($object, $params = null)
    {
        if ($params['removeOld'] == true) {
            StaticFiles::remove($object['module']);
        }
        StaticFiles::deploy($object['module']);
    }
    
    /**
     * 
     * @cmdObject string module the host
     */
    public function deployFormsAction($object)
    {
        $modulePath = Informations::getModulePath($object['module']);
        $moduleId = Informations::getModuleIdByName($object['module']);
        $formsFiles = $modulePath . '/install/forms/*.xml';
        foreach (glob($formsFiles) as $xmlFile) {
            Form::installFromXml($moduleId, $xmlFile);
        }
    }

}
