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

namespace Tests\Centreon\Domain\Broker;

use Centreon\Domain\Broker\Broker;
use Centreon\Domain\Broker\BrokerService;
use Centreon\Domain\Broker\BrokerConfiguration;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Broker\Interfaces\BrokerRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class BrokerServiceTest extends TestCase
{
    /**
     * @var BrokerRepositoryInterface&MockObject
     */
    private $brokerRepository;

    /**
     * @var BrokerConfiguration
     */
    private $brokerConfigurationPeerRetention;

    /**
     * @var BrokerConfiguration
     */
    private $brokerConfigurationNoPeerRetention;

    protected function setUp(): void
    {
        $this->brokerRepository = $this->createMock(BrokerRepositoryInterface::class);

        $this->brokerConfigurationPeerRetention = (new BrokerConfiguration())
            ->setId(1)
            ->setConfigurationKey('one_peer_retention_mode')
            ->setConfigurationValue('yes');

        $this->brokerConfigurationNoPeerRetention = (new BrokerConfiguration())
            ->setId(1)
            ->setConfigurationKey('one_peer_retention_mode')
            ->setConfigurationValue('no');
    }

    public function testFindConfigurationByMonitoringServerAndConfigKey(): void
    {
        $broker = (new Broker())
            ->setBrokerConfigurations([$this->brokerConfigurationPeerRetention]);
        $this->brokerRepository
            ->expects($this->once())
            ->method('findConfigurationByMonitoringServerAndConfigKey')
            ->willReturn($broker);

        $brokerService = new BrokerService($this->brokerRepository);

        $this->assertInstanceOf(
            Broker::class,
            $brokerService->findConfigurationByMonitoringServerAndConfigKey(1, 'one_peer_retention_mode')
        );
    }

    public function testFindConfigurationByMonitoringServerAndConfigKeyWithInvalidKey(): void
    {
        $brokerService = new BrokerService($this->brokerRepository);
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("No entry for invalid_key key in your Broker configuration");
        $brokerService->findConfigurationByMonitoringServerAndConfigKey(1, 'invalid_key');
    }

    public function testFindConfigurationByMonitoringServerAndConfigKeySetRetention(): void
    {
        $brokerRetentionStub = (new Broker())
            ->setBrokerConfigurations([$this->brokerConfigurationPeerRetention]);

        $brokerNoRetentionStub = (new Broker())
            ->setBrokerConfigurations([$this->brokerConfigurationNoPeerRetention]);

        $this->brokerRepository
            ->expects($this->at(0))
            ->method('findConfigurationByMonitoringServerAndConfigKey')
            ->willReturn($brokerRetentionStub);

        $this->brokerRepository
            ->expects($this->at(1))
            ->method('findConfigurationByMonitoringServerAndConfigKey')
            ->willReturn($brokerNoRetentionStub);

        $brokerService = new BrokerService($this->brokerRepository);

        /**
         * Set Retention Mode at true
         */
        $broker = $brokerService->findConfigurationByMonitoringServerAndConfigKey(1, 'one_peer_retention_mode');
        $this->assertTrue($broker->getIsPeerRetentionMode());

        /**
         * Set Retention Mode at false
         */
        $broker = $brokerService->findConfigurationByMonitoringServerAndConfigKey(1, 'one_peer_retention_mode');
        $this->assertFalse($broker->getIsPeerRetentionMode());
    }
}
