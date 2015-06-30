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

namespace Centreon\Commands\Database;

use Centreon\Internal\Command\AbstractCommand;
use Centreon\Internal\Exception;
use Centreon\Internal\Di;
use Centreon\Internal\Utils\CommandLine\InputOutput;
use Centreon\Internal\Database\GenerateDiff;
use Centreon\Internal\Database\Migrate;
use Centreon\Internal\Installer\Migration\Manager;

/**
 * Description of MigrateCommand
 *
 * @author Lionel Assepo <lassepo@centreon.com>
 */
class MigrateCommand extends AbstractCommand
{
    
    public $options = array(
        "statusAction" => array(
            "module" => array(
                "functionParams" => "module",
                "help" => "",
                "type" => "string",
                "toTransform" => "",
                "multiple" => false,
                "required" => false
            )
        ),
        "migrateAction" => array(
            "module" => array(
                "functionParams" => "module",
                "help" => "",
                "type" => "string",
                "toTransform" => "",
                "multiple" => false,
                "required" => false
            )
        ),
        "initAction" => array(
            "module" => array(
                "functionParams" => "module",
                "help" => "",
                "type" => "string",
                "toTransform" => "",
                "multiple" => false,
                "required" => false
            )
        ),
        "createAction" => array(
            "module" => array(
                "functionParams" => "module",
                "help" => "",
                "type" => "string",
                "toTransform" => "",
                "multiple" => false,
                "required" => false
            ),
            "class" => array(
                "functionParams" => "class",
                "help" => "",
                "type" => "string",
                "toTransform" => "",
                "multiple" => false,
                "required" => false
            )
        )
    );
    
    /**
     * 
     * @param string $module
     */
    public function statusAction($module)
    {
        InputOutput::display(_("Lists the migrations yet to be executed"));
        $migrationManager = new Manager($module, 'production');
        $cmd = $this->getPhinxCallLine() .'status ';
        $cmd .= '-c '. $migrationManager->getPhinxConfigurationFile();
        $cmd .= '-e '. $module;
        shell_exec($cmd);
    }
    
    /**
     * 
     * @param string $module
     */
    public function migrateAction($module)
    {
        InputOutput::display(_("Executes all migrations"));
        $migrationManager = new Manager($module, 'production');
        $cmd = $this->getPhinxCallLine() .'migrate ';
        $cmd .= '-c '. $migrationManager->getPhinxConfigurationFile();
        $cmd .= ' -e '. $module;
        shell_exec($cmd);
    }
    
    /**
     * 
     * @param string $module
     */
    public function rollbackAction($module)
    {
        InputOutput::display(_("Revert all migrations"));
        $migrationManager = new Manager($module, 'production');
        $cmd = $this->getPhinxCallLine() .'rollback ';
        $cmd .= '-c '. $migrationManager->getPhinxConfigurationFile();
        $cmd .= '-e '. $module;
        shell_exec($cmd);
    }
    
    /**
     * 
     * @param type $module
     */
    public function initAction($module)
    {
        $migrationManager = new Manager($module, 'development');
        $migrationManager->generateConfiguration();
    }
    
    /**
     * 
     * @param string $module
     * @param string $class
     */
    public function createAction($module, $class)
    {
        $migrationManager = new Manager($module, 'development');
        $cmd = $this->getPhinxCallLine() .'create ';
        $cmd .= '-c '. $migrationManager->getPhinxConfigurationFile();
        $cmd .= ' ' . $class;
        shell_exec($cmd);
    }
    
    /**
     * 
     * @return string
     */
    private function getPhinxCallLine()
    {
        $di = Di::getDefault();
        $centreonPath = $di->get('config')->get('global', 'centreon_path');
        $callCmd = 'php ' . $centreonPath . '/vendor/bin/phinx ';
        return $callCmd;
    }
}
