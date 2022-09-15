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

namespace Tests\Centreon\Domain\Monitoring;

use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\Monitoring\CommandLineTrait;
use Centreon\Domain\ServiceConfiguration\ServiceMacro;
use Centreon\Domain\Monitoring\Exception\MonitoringServiceException;
use PHPUnit\Framework\TestCase;

class CommandLineTraitTest extends TestCase
{
    use CommandLineTrait;

    /**
     * @var HostMacro
     */
    private $hostMacroWithoutSpace;

    /**
     * @var HostMacro
     */
    private $hostMacroWithSpace;

    /**
     * @var ServiceMacro
     */
    private $serviceMacroWithoutSpace;

    /**
     * @var ServiceMacro
     */
    private $serviceMacroWithSpace;

    /**
     * @var string
     */
    private $configurationCommand;

    /**
     * @var string
     */
    private $replacementValue;

    protected function setUp(): void
    {
        $this->hostMacroWithoutSpace = (new HostMacro())->setName('$_HOSTWITHOUTSPACE$')->setValue('value1');
        $this->hostMacroWithSpace = (new HostMacro())->setName('$_HOSTWITHSPACE$')->setValue('value 2');

        $this->serviceMacroWithoutSpace = (new ServiceMacro())->setName('$_SERVICEWITHOUTSPACE$')->setValue('value1');
        $this->serviceMacroWithSpace = (new ServiceMacro())->setName('$_SERVICEWITHSPACE$')->setValue('value 2');

        $this->configurationCommand = '$USER1$/plugin.pl --a="' . $this->hostMacroWithoutSpace->getName() . '" '
            . $this->serviceMacroWithoutSpace->getName() . ' '
            . '-b "' . $this->hostMacroWithSpace->getName() . '" '
            . $this->serviceMacroWithSpace->getName() . ' -f $_SERVICEEXTRAOPTIONS$';

        $this->replacementValue = '*****';
    }

    /**
     * Test built command line which does not contain any password
     *
     * @return void
     */
    public function testBuildCommandLineFromConfigurationWithoutPassword(): void
    {
        $macros = [
            $this->hostMacroWithoutSpace,
            $this->hostMacroWithSpace,
            $this->serviceMacroWithoutSpace,
            $this->serviceMacroWithSpace
        ];

        $monitoringCommand = '/centreon/plugins/plugin.pl --a="' . $this->hostMacroWithoutSpace->getValue() . '" '
            . $this->serviceMacroWithoutSpace->getValue() . ' '
            . '-b "' . $this->hostMacroWithSpace->getValue() . '" '
            . $this->serviceMacroWithSpace->getValue() . ' -f extra options';
        $result = $this->buildCommandLineFromConfiguration(
            $this->configurationCommand,
            $monitoringCommand,
            $macros,
            $this->replacementValue
        );

        $this->assertEquals($monitoringCommand, $result);
    }

    /**
     * Test built command line which contains host & service macros password
     *
     * @return void
     */
    public function testBuildCommandLineFromConfigurationWithPasswords(): void
    {
        $this->hostMacroWithoutSpace->setPassword(true);
        $this->hostMacroWithSpace->setPassword(true);
        $this->serviceMacroWithoutSpace->setPassword(true);
        $this->serviceMacroWithSpace->setPassword(true);

        $macros = [
            $this->hostMacroWithoutSpace,
            $this->hostMacroWithSpace,
            $this->serviceMacroWithoutSpace,
            $this->serviceMacroWithSpace
        ];

        $monitoringCommand = '/centreon/plugins/plugin.pl --a="' . $this->replacementValue . '" '
            . $this->replacementValue . ' '
            . '-b "' . $this->replacementValue . '" '
            . $this->replacementValue . ' -f extra options';
        $result = $this->buildCommandLineFromConfiguration(
            $this->configurationCommand,
            $monitoringCommand,
            $macros,
            $this->replacementValue
        );

        $this->assertEquals($monitoringCommand, $result);
    }

    /**
     * Test built command line which contains service macros password which are glued
     * it cannot be parsed so it should throw an exception
     *
     * @return void
     */
    public function testBuildCommandLineFromConfigurationWithGluedPasswords(): void
    {
        $this->hostMacroWithoutSpace->setPassword(true);
        $this->hostMacroWithSpace->setPassword(true);
        $this->serviceMacroWithoutSpace->setPassword(true);
        $this->serviceMacroWithSpace->setPassword(true);

        $serviceMacroExtraOptions = (new ServiceMacro())
            ->setName('$_SERVICEEXTRAOPTIONS$')
            ->setValue('password')
            ->setPassword(true);

        $serviceMacroExtraOptions2 = (new ServiceMacro())
            ->setName('$_SERVICEEXTRAOPTIONS2$')
            ->setValue('password2')
            ->setPassword(true);

        // end of configuration command : $_SERVICEEXTRAOPTIONS$$_SERVICEEXTRAOPTIONS2$
        $this->configurationCommand .= '$_SERVICEEXTRAOPTIONS2$';

        $macros = [
            $this->hostMacroWithoutSpace,
            $this->hostMacroWithSpace,
            $this->serviceMacroWithoutSpace,
            $this->serviceMacroWithSpace,
            $serviceMacroExtraOptions,
            $serviceMacroExtraOptions2
        ];

        $monitoringCommand = '/centreon/plugins/plugin.pl --a="' . $this->replacementValue . '" '
            . $this->replacementValue . ' '
            . '-b "' . $this->replacementValue . '" '
            . $this->replacementValue . ' -f extra options';

        $this->expectException(MonitoringServiceException::class);
        $this->expectExceptionMessage('Macro passwords cannot be detected');

        $this->buildCommandLineFromConfiguration(
            $this->configurationCommand,
            $monitoringCommand,
            $macros,
            $this->replacementValue
        );
    }

    /**
     * Test built host command line which contains service macros which can be replaced and applied spaces
     *
     * @return void
     */
    public function testBuildCommandLineFromConfigurationWithSpaceSeparatedValues(): void
    {
        $configurationCommand = '$CENTREONPLUGINS$/centreon_linux_snmp.pl --plugin=os::linux::snmp::plugin '
            . '--mode=time --hostname=$HOSTADDRESS$ --snmp-version="$_HOSTSNMPVERSION$" '
            . '--snmp-community="$_HOSTSNMPCOMMUNITY$" $_HOSTSNMPEXTRAOPTIONS$ '
            . '--ntp-hostname="$_SERVICENTPADDR$" --ntp-port="$_SERVICENTPPORT$" '
            . '--warning-offset="$_SERVICEWARNING$" --critical-offset="$_SERVICECRITICAL$" '
            . '--timezone="$_SERVICETIMEZONE$" $_SERVICEEXTRAOPTIONS$ $_HOSTSNMPEXTRAOPTIONS_2$';
        $macros = [
            (new HostMacro())
                ->setName('$_HOSTSNMPEXTRAOPTIONS$')
                ->setValue('test_host_snmp_extra'),
            (new HostMacro())
                ->setName('$_HOSTSNMPEXTRAOPTIONS_2$')
                ->setValue('test_host_snmp_extra_2 extra texte')
                ->setPassword(true),
        ];

        $monitoringCommand = '/usr/lib/centreon/plugins//centreon_linux_snmp.pl --plugin=os::linux::snmp::plugin '
            . '--mode=time --hostname=localhost --snmp-version="2c" '
            . '--snmp-community="public" test_host_snmp_extra '
            . '--ntp-hostname="" --ntp-port="" '
            . '--warning-offset="" --critical-offset="" '
            . '--timezone=""  test_host_snmp_extra_2 extra texte';

        // $_SERVICEEXTRAOPTIONS$ $_HOSTSNMPEXTRAOPTIONS_2$ will be converted as "  test_host_snmp_extra_2 extra texte"
        // then, pattern cannot detect which word is from $_SERVICEEXTRAOPTIONS$ or $_HOSTSNMPEXTRAOPTIONS_2$
        $this->expectException(MonitoringServiceException::class);
        $this->expectExceptionMessage('Macro passwords cannot be detected');

        $this->buildCommandLineFromConfiguration(
            $configurationCommand,
            $monitoringCommand,
            $macros,
            $this->replacementValue
        );
    }
}
