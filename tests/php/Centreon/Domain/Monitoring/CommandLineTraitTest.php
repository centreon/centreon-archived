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

namespace Tests\Centreon\Domain\Monitoring;

use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\Monitoring\CommandLineTrait;
use Centreon\Domain\ServiceConfiguration\ServiceMacro;
use PHPUnit\Framework\TestCase;

class CommandLineTraitTest extends TestCase
{
    use CommandLineTrait;

    public function testExplodeSpacesButKeepValuesByMacro()
    {
        $hostMacro1 = (new HostMacro())->setName('VALUE1')->setValue('value  1');
        $hostMacro2 = (new HostMacro())->setName('VALUE2')->setValue('value2');
        $hostMacros[] = $hostMacro1;
        $hostMacros[] = $hostMacro2;

        $serviceMacro1 = (new ServiceMacro())->setName('VALUE1')->setValue('value1');
        $serviceMacro2 = (new ServiceMacro())->setName('VALUE2')->setValue('value 2');
        $serviceMacros[] = $serviceMacro1;
        $serviceMacros[] = $serviceMacro2;

        $configurationCommand = ' --a="$_HOSTVALUE1$" $_SERVICEVALUE1$ -b $_HOSTVALUE2$ $_SERVICEVALUE2$ -f ';
        $monitoringCommand = " --a=\"{$hostMacro1->getValue()}\" {$serviceMacro1->getValue()} -b "
            . "{$hostMacro2->getValue()} {$serviceMacro2->getValue()} -f ";
        $result = $this->explodeSpacesButKeepValuesByMacro(
            $configurationCommand,
            $monitoringCommand,
            $serviceMacros,
            $hostMacros
        );
        $expectedArray = [
            '',
            "--a=\"{$hostMacro1->getValue()}\"",
            "{$serviceMacro1->getValue()}",
            '-b',
            "{$hostMacro2->getValue()}",
            "{$serviceMacro2->getValue()}",
            '-f',
            ''
        ];
        $this->assertCount(
            0,
            array_diff($expectedArray, $result),
            "Processing error on CommandLineTrait::explodeSpacesButKeepValuesByMacro"
        );
    }
}
