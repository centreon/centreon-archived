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

use Centreon\Internal\Utils\String\CamelCaseTransformation;
use Centreon\Internal\Module\Generator;
use Centreon\Internal\Module\Informations;
use Centreon\Internal\Utils\CommandLine\InputOutput;
use Centreon\Internal\Command\AbstractCommand;

/**
 * Description of Generate
 *
 * @author lionel
 */
class ToolsCommand extends AbstractCommand
{
    /**
     * 
     */
    public function generateAction()
    {
        $moduleCanonicalName = InputOutput::prompt(
            _("Type the module canonical name here (in CamelCase, it must not contains Module at the ends)"),
            function ($params, &$result) {
                call_user_func_array(
                    array('\Centreon\Internal\Module\Informations', 'isCanonicalNameValid'),
                    array($params, &$result)
                );
            }
        );
        
        // Display Name and short name
        $moduleDisplayName = CamelCaseTransformation::camelCaseToCustom($moduleCanonicalName, " ");
        $moduleShortname = strtolower(CamelCaseTransformation::camelCaseToCustom($moduleCanonicalName, "-"));
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
