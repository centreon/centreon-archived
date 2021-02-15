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

use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\ServiceConfiguration\ServiceMacro;
use Centreon\Domain\Monitoring\Exception\MonitoringServiceException;

trait CommandLineTrait
{
    /**
     * Build command line by comparing monitoring & configuration commands
     * and by replacing macros in configuration command
     *
     * @param string $configurationCommand
     * @param string $monitoringCommand
     * @param HostMacro[]|ServiceMacro[] $macros
     * @param string $replacementValue
     * @return string
     */
    private function buildCommandLineFromConfiguration(
        string $configurationCommand,
        string $monitoringCommand,
        array $macros,
        string $replacementValue
    ): string {
        $macroPasswordNames = [];
        foreach ($macros as $macro) {
            if ($macro->isPassword()) {
                $macroPasswordNames[] = $macro->getName();
            } elseif ($macro->getName() !== null && $macro->getValue() !== null) {
                $configurationCommand = str_replace($macro->getName(), $macro->getValue(), $configurationCommand);
            }
        }

        if (count($macroPasswordNames) === 0) {
            return $monitoringCommand;
        }

        $macroPattern = $this->generateCommandMacroPattern($configurationCommand);

        $foundMacroNames = [];
        if (preg_match_all('/(\$\S+?\$)/', $configurationCommand, $matches)) {
            if (isset($matches[0])) {
                $foundMacroNames = $matches[0];
            }
        }

        if (preg_match('/' . $macroPattern . '/', $monitoringCommand, $foundMacroValues)) {
            array_shift($foundMacroValues); // remove global string matching

            foreach ($foundMacroNames as $index => $foundMacroName) {
                $foundMacroValue = $foundMacroValues[$index];
                $macroValue = in_array($foundMacroName, $macroPasswordNames) ? $replacementValue : $foundMacroValue;
                $configurationCommand = str_replace($foundMacroName, $macroValue, $configurationCommand);
            }
        } else {
            throw MonitoringServiceException::configurationhasChanged();
        }

        return $configurationCommand;
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

        // if two macros are glued, regex cannot detect properly password string
        if (str_contains($macroPattern, '(.*)(.*)')) {
            throw MonitoringServiceException::macroPasswordNotDetected();
        }

        return $macroPattern;
    }
}
