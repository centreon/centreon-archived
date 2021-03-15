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

namespace Tests\Centreon\Domain\MonitoringServer\Model;

use DateTime;
use PHPUnit\Framework\TestCase;
use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\MonitoringServer\Model\RealTimeMonitoringServer;

/**
 * This class is designed to test all setters of the RealTimeMonitoringServer entity, especially those with exceptions.
 *
 * @package Tests\Centreon\Domain\MonitoringServer\Model
 */
class RealTimeMonitoringServerTest extends TestCase
{
    /**
     * Too long name test
     */
    public function testNameTooShortException(): void
    {
        $name = str_repeat('.', RealTimeMonitoringServer::MIN_NAME_LENGTH - 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::minLength(
                $name,
                strlen($name),
                RealTimeMonitoringServer::MIN_NAME_LENGTH,
                'RealTimeMonitoringServer::name'
            )->getMessage()
        );
        new RealTimeMonitoringServer(1, $name);
    }

    /**
     * Too long name test
     */
    public function testNameTooLongException(): void
    {
        $name = str_repeat('.', RealTimeMonitoringServer::MAX_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                RealTimeMonitoringServer::MAX_NAME_LENGTH,
                'RealTimeMonitoringServer::name'
            )->getMessage()
        );
        new RealTimeMonitoringServer(1, $name);
    }

    /**
     * Too long address test
     */
    public function testAddressTooLongException(): void
    {
        $address = str_repeat('.', RealTimeMonitoringServer::MAX_ADDRESS_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $address,
                strlen($address),
                RealTimeMonitoringServer::MAX_ADDRESS_LENGTH,
                'RealTimeMonitoringServer::address'
            )->getMessage()
        );
        (new RealTimeMonitoringServer(1, 'Central'))
            ->setAddress($address);
    }

    /**
     * Too long version test
     */
    public function testVersionTooLongException(): void
    {
        $version = str_repeat('.', RealTimeMonitoringServer::MAX_VERSION_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $version,
                strlen($version),
                RealTimeMonitoringServer::MAX_VERSION_LENGTH,
                'RealTimeMonitoringServer::version'
            )->getMessage()
        );
        (new RealTimeMonitoringServer(1, 'Central'))
            ->setVersion($version);
    }

    /**
     * Too long address test
     */
    public function testDescriptionTooLongException(): void
    {
        $description = str_repeat('.', RealTimeMonitoringServer::MAX_DESCRIPTION_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $description,
                strlen($description),
                RealTimeMonitoringServer::MAX_DESCRIPTION_LENGTH,
                'RealTimeMonitoringServer::description'
            )->getMessage()
        );
        (new RealTimeMonitoringServer(1, 'Central'))
            ->setDescription($description);
    }

    /**
     * isRunning property test
     */
    public function testIsRunningProperty(): void
    {
        $realTimeMonitoringServer = new RealTimeMonitoringServer(1, 'Central');
        $this->assertFalse($realTimeMonitoringServer->isRunning());
        $realTimeMonitoringServer->setRunning(true);
        $this->assertTrue($realTimeMonitoringServer->isRunning());
    }

    /**
     * @return RealTimeMonitoringServer
     * @throws \Assert\AssertionFailedException
     */
    public static function createEntity(): RealTimeMonitoringServer
    {
        return (new RealTimeMonitoringServer(1, 'Central'))
            ->setDescription('Monitoring Server description')
            ->setLastAlive((new DateTime())->getTimestamp())
            ->setVersion('99.99.99')
            ->setAddress('0.0.0.0')
            ->setRunning(true);
    }
}
