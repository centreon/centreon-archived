<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

trait CommandLineTrait
{
    /**
     * The purpose is to analyze the command line completed by Engine and to detect the value of a macro
     * that contains spaces to place the value only in one element of the array.
     *
     * <i><b>This process works only with the on-demand macros.</b></i><br\><br\>
     *
     * example: \-\-a='$\_HOSTVALUE$' with $\_HOSTVALUE$ = ' my value '
     *
     * Actually with a simple explode(' ', ...):
     * (array[0] => "\-\-a='", array[1] => "my", array[2] => "value", array[3] => "'")
     *
     * Transform into:
     * (array[0] => "\-\-a=' my value '")
     *
     * @param string $configurationCommand Configuration command line
     * @param string $monitoringCommand Monitoring command line
     * @param ServiceMacro[] $onDemandServiceMacros List of on-demand service macros
     * @param HostMacro[] $onDemandHostMacros List of on-demand host macros
     * @return array<int, string>
     */
    private function explodeSpacesButKeepValuesByMacro(
        string $configurationCommand,
        string $monitoringCommand,
        array $onDemandServiceMacros,
        array $onDemandHostMacros
    ): array {
        $allMacros = array_merge($onDemandServiceMacros, $onDemandHostMacros);
        $configurationTokens = explode(' ', $configurationCommand);
        $monitoringToken = explode(' ', $monitoringCommand);
        $macrosByName = [];
        foreach ($allMacros as $macro) {
            $macrosByName[$macro->getName()] = $macro;
        }

        $indexMonitoring = 0;
        foreach ($configurationTokens as $indexConfiguration => $token) {
            if (preg_match_all('~\$_(HOST|SERVICE)[^$]*\$~', $token, $matches, PREG_SET_ORDER)) {
                if (array_key_exists($matches[0][0], $macrosByName)) {
                    $macroToAnalyse = $macrosByName[$matches[0][0]];
                    if (!empty($macroToAnalyse->getValue())) {
                        $numberSpacesInMacroValue = count(explode(' ', $macroToAnalyse->getValue())) - 1;
                        if ($numberSpacesInMacroValue > 0) {
                            $replacementValue = implode(
                                ' ',
                                array_slice(
                                    $monitoringToken,
                                    $indexMonitoring,
                                    $numberSpacesInMacroValue + 1
                                )
                            );
                            array_splice(
                                $monitoringToken,
                                $indexMonitoring,
                                $numberSpacesInMacroValue + 1,
                                $replacementValue
                            );
                            $indexMonitoring += $numberSpacesInMacroValue + 1;
                        }
                    }
                }
            } else {
                $indexMonitoring++;
            }
        }
        return $monitoringToken;
    }
}
