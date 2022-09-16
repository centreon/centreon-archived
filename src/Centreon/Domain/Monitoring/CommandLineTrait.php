<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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
declare(strict_types=1);

namespace Centreon\Domain\Monitoring;

use Centreon\Domain\Macro\Interfaces\MacroInterface;
use Centreon\Domain\Monitoring\Exception\MonitoringServiceException;

trait CommandLineTrait
{
    /**
     * Build command line by comparing monitoring & configuration commands
     * and by replacing macros in configuration command
     *
     * @param string $configurationCommand
     * @param string $monitoringCommand
     * @param MacroInterface[] $macros
     * @param string $replacementValue
     * @return string
     */
    private function buildCommandLineFromConfiguration(
        string $configurationCommand,
        string $monitoringCommand,
        array $macros,
        string $replacementValue
    ): string {
        // if the command line contains $$ after a macro (so $$$), delete one of them to match with
        // the command executed by centreon-engine
        $configurationCommand = str_replace('$$$', '$$', $configurationCommand);
        $macroPasswordNames = [];
        foreach ($macros as $macro) {
            if ($macro->isPassword()) {
                // if macro is a password, store its name and let macro in configuration command
                $macroPasswordNames[] = $macro->getName();
            } elseif ($macro->getName() !== null && $macro->getValue() !== null) {
                // if macro is not a password, replace it by its configuration value
                $configurationCommand = str_replace($macro->getName(), trim($macro->getValue()), $configurationCommand);
            }
        }

        if (count($macroPasswordNames) === 0) {
            return $monitoringCommand;
        }

        $foundMacroNames = $this->extractMacroNamesFromCommandLine($configurationCommand);

        $macroPattern = $this->generateCommandMacroPattern($configurationCommand);
        $macroLazyPattern = str_replace('(.*)', '(.*?)', $macroPattern);

        if (preg_match('/' . $macroPattern . '/', $monitoringCommand, $foundMacroValues)) {
            // lazy and greedy regex should return the same result
            // otherwise, it is not possible to know which section is the password
            if (
                preg_match('/' . $macroLazyPattern . '/', $monitoringCommand, $foundMacroLazyValues)
                && $foundMacroLazyValues !== $foundMacroValues
            ) {
                throw MonitoringServiceException::macroPasswordNotDetected();
            }

            array_shift($foundMacroValues); // remove global string matching

            // replace macros found in configuration command by matched value from monitoring command
            foreach ($foundMacroNames as $index => $foundMacroName) {
                $foundMacroValue = $foundMacroValues[$index];

                // if macro is a password, we replace it by replacement value (usually ****)
                $macroValue = in_array($foundMacroName, $macroPasswordNames) ? $replacementValue : $foundMacroValue;
                $configurationCommand = str_replace($foundMacroName, $macroValue, $configurationCommand);
            }
        } else {
            // configuration and monitoring commands do not match
            // so last configuration has not been applied
            throw MonitoringServiceException::configurationHasChanged();
        }

        return $configurationCommand;
    }

    /**
     * Extra macro names from configuration command line
     * example : ['$HOSTADDRESS$', '$_SERVICEPASSWORD$']
     *
     * @param string $commandLine
     * @return string[] The list of macro names
     */
    private function extractMacroNamesFromCommandLine(string $commandLine): array
    {
        $foundMacroNames = [];

        if (preg_match_all('/(\$\S+?\$)/', $commandLine, $matches)) {
            if (isset($matches[0])) {
                $foundMacroNames = $matches[0];
            }
        }

        return $foundMacroNames;
    }

    /**
     * Build a regex to identify macro associated value
     * example :
     *   - configuration command : $USER1$/check_icmp -H $HOSTADDRESS$ $_HOSTPASSWORD$
     *   - generated regex : ^(.*)\/check_icmp \-H (.*) (.*)$
     *   - monitoring : /usr/lib64/nagios/plugins/check_icmp -H 127.0.0.1 hiddenPassword
     *   ==> matched values : [/usr/lib64/nagios/plugins/check_icmp, hiddenPassword]
     *
     * @param string $configurationCommand
     * @return string
     * @throws MonitoringServiceException
     */
    private function generateCommandMacroPattern(string $configurationCommand): string
    {
        $countFoundMacros = 0;
        if (preg_match_all('/(\$\S+?\$)/', $configurationCommand, $matches)) {
            if (isset($matches[0])) {
                $countFoundMacros = count($matches[0]);
            }
        }

        $commandSplittedByMacros = preg_split('/(\$\S+?\$)/', $configurationCommand);
        if ($commandSplittedByMacros === false) {
            throw MonitoringServiceException::configurationCommandNotSplitted();
        }

        $macroPattern = '^';
        foreach ($commandSplittedByMacros as $index => $commandSection) {
            $macroMatcher = (($index + 1) <= $countFoundMacros) ? '(.*)' : '';

            $macroPattern .= preg_quote($commandSection, '/') . $macroMatcher;
        }
        $macroPattern .= '$';

        // if two macros are glued or separated by spaces, regex cannot detect properly password string
        if (preg_match('/\(\.\*\)\s*\(\.\*\)/', $macroPattern)) {
            throw MonitoringServiceException::macroPasswordNotDetected();
        }

        return $macroPattern;
    }
}
