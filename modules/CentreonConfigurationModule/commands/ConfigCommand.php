<?php
/*
 * Copyright 2005-2014 CENTREON
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

namespace CentreonConfiguration\Commands;

use Centreon\Internal\Command\AbstractCommand;
use Centreon\Internal\Utils\CommandLine\Colorize;
use CentreonConfiguration\Repository\ConfigApplyRepository;
use CentreonConfiguration\Repository\ConfigGenerateRepository;
use CentreonConfiguration\Repository\ConfigMoveRepository;
use CentreonConfiguration\Repository\ConfigTestRepository;

/**
 * @authors Lionel Assepo
 * @package CentreonConfiguration
 * @subpackage Commands                                 
 */
class ConfigCommand extends AbstractCommand
{
    /**
     * Action for Generating configuration files
     * @param integer $id Poller id
     */
    public function generateAction($id)
    {
        $obj = new ConfigGenerateRepository($id);
        $obj->generate();
        echo $obj->getOutput();
    }

    /**
     * Action for Move configuration files
     * @param integer $id Poller id
     */
    public function moveAction($id)
    {
        $obj = new ConfigMoveRepository($id);
        $obj->moveConfig();
        echo $obj->getOutput();
    }

    /**
     * Action for testing configuration files
     * @param integer $id Poller id
     */
    public function testAction($id)
    {
        $obj = new ConfigTestRepository($id);
        $obj->checkConfig();
        // Only CentEngine is tested at the moment
        // We are formatting the output to have a colored, readable ouput on terminal
        $totalWarningsStr = Colorize::colorizeMessage('Total Warnings', 'warning');
        $warningStr = Colorize::colorizeMessage('Warning', 'warning');
        $totalErrorsStr = Colorize::colorizeMessage('Total Errors', 'danger');
        $errorStr = Colorize::colorizeMessage('Error', 'danger');
        $finalStr = $obj->getOutput();
        $finalStr = str_replace("\nTotal Warnings", "\n".$totalWarningsStr, $finalStr);
        $finalStr = str_replace("\nWarning", "\n".$warningStr, $finalStr);
        $finalStr = str_replace("\nTotal Errors", "\n".$totalErrorsStr, $finalStr);
        $finalStr = str_replace("\nError", "\n".$errorStr, $finalStr);
        echo $finalStr;
    }

    /**
     * Action for Apply configuration
     * @param integer $id Poller id
     * @param string $action Action to be applied
     */
    public function applyAction($id, $action)
    {
        $obj = new ConfigApplyRepository($id);
        $obj->action($action);
        echo $obj->getOutput();
    }
}
