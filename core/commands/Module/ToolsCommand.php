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

namespace Centreon\Commands\Module;

use \Centreon\Internal\Utils\String\CamelCaseTransformation;
use \Centreon\Internal\Module\Generator;
use \Centreon\Internal\Module\Informations;
use \Centreon\Internal\Utils\CommandLine\InputOutput;

/**
 * Description of Generate
 *
 * @author lionel
 */
class ToolsCommand extends \Centreon\Internal\Command\AbstractCommand
{
    /**
     * 
     */
    public function generateAction()
    {
        $moduleCanonicalName = InputOutput::prompt(
            _("Type the module canonical name here (in CamelCase, it must not contains Module at the ends)"),
            function($params, &$result) {
                call_user_func_array(
                    array('\Centreon\Internal\Module\Informations', 'isCanonicalNameValid'),
                    array($params, &$result)
                );
            }
        );
        
        // Display Name and short name
        $moduleDisplayName = CamelCaseTransformation::CamelCaseToCustom($moduleCanonicalName, " ");
        $moduleShortname = strtolower(CamelCaseTransformation::CamelCaseToCustom($moduleCanonicalName, "-"));
        $moduleGenerator = new Generator($moduleCanonicalName);
        $moduleGenerator->setModuleShortName($moduleShortname);
        $moduleGenerator->setModuleDisplayName($moduleDisplayName);

        // Get Module ShortName set by user
        $userAnswer = InputOutput::prompt(
            _("Type the module shortname here (seperate by -) [" . $moduleShortname ."]")
        );
        if (!empty($userAnswer)) {
            $moduleShortname = $userAnswer;
        }
        
        // Type User Name
        $moduleAuthor = InputOutput::prompt(
            _("Type your name here")
        );
        $moduleGenerator->setModuleAuthor($moduleAuthor);
        
        // Ask For generating Directory Structure
        InputOutput::display(_("Generating module full structure... "), false);
        $moduleGenerator->generateModuleStructure();
        $moduleGenerator->generateConfigFile();
        $moduleGenerator->createSampleInstaller();
        InputOutput::display(_("Done\n"), true, "bgreen");
        
        // Ask For sample Controller/View
        $generateController = InputOutput::prompt(_("Generate sample controller/view (yes/no)? [yes]"));
        if (empty($generateController) || $generateController == "yes" || $generateController == "y") {
            $moduleGenerator->createSampleController();
            $moduleGenerator->createSampleView();
        }
        
        // Ask to install the module
        $installModule = InputOutput::prompt(_("Install the module(yes/no)? [no] "));
        if (!empty($installModule) && ($installModule == "yes" || $installModule == "y")) {
            $moduleInstaller = Informations::getModuleInstaller($moduleShortname);
            $moduleInstaller->install();
        }
    }
}
