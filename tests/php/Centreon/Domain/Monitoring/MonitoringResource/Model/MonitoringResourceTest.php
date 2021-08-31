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

namespace Tests\Centreon\Domain\Monitoring\MonitoringResource\Model;

use Centreon\Domain\Acknowledgement\Acknowledgement;
use PHPUnit\Framework\TestCase;
use Centreon\Domain\Monitoring\Icon;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Monitoring\ResourceGroup;
use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Domain\Monitoring\Notes;
use Centreon\Domain\Monitoring\ResourceExternalLinks;
use Centreon\Domain\Monitoring\ResourceLinks;
use Centreon\Domain\Monitoring\ResourceStatus;

/**
 * This class is designed to test all setters of the MonitoringResource entity, especially those with exceptions.
 *
 * @package Tests\Centreon\Domain\Monitoring\MonitoringResource\Model
 */
class MonitoringResourceTest extends TestCase
{
    /**
     * test Name too long exception
     */
    public function testNameTooLongException(): void
    {
        $resourceName = str_repeat('.', MonitoringResource::MAX_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $resourceName,
                strlen($resourceName),
                MonitoringResource::MAX_NAME_LENGTH,
                'MonitoringResource::name'
            )->getMessage()
        );
        (self::createServiceMonitoringResourceEntity())->setName($resourceName);
    }

    /**
     * test Uuid creation
     */
    public function testUuidGeneration(): void
    {
        $uuid = (self::createServiceMonitoringResourceEntity())->getUuid();
        $this->assertEquals('h1-s10', $uuid);
    }

    /**
     * test Unhandled resource type
     */
    public function testUnhandledTypeForResource(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid resource type %s', 'meta'));
        (self::createServiceMonitoringResourceEntity())->setType('meta');
    }

    /**
     * test Not full downtime array on setDowntimes
     */
    public function testNotFullDowntimeArray(): void
    {
        $downtimes = [
            new Downtime(),
            'downtimeFake'
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('One of the elements provided is not a Downtime instance');

        (self::createServiceMonitoringResourceEntity())->setDowntimes($downtimes); // @phpstan-ignore-line
    }

    /**
     * test Not full resource group array on setGroups
     */
    public function testNotFullResourceGroupArray(): void
    {
        $resourceGroups = [
            new ResourceGroup(1, 'resourceGroupName'),
            'fakeResourceGroup'
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('One of the elements provided is not a ResourceGroup type');

        (self::createServiceMonitoringResourceEntity())->setGroups($resourceGroups); // @phpstan-ignore-line
    }

    /**
     * test Resource short type generation
     */
    public function testShortTypeGeneration(): void
    {
        $serviceMonitoringResourceType = self::createServiceMonitoringResourceEntity();
        $hostMonitoringResourceType = $serviceMonitoringResourceType->getParent();
        $metaServiceMonitoringResourceType = new MonitoringResource(1, 'metaName', 'metaservice');
        $this->assertEquals('s', $serviceMonitoringResourceType->getShortType());
        $this->assertEquals('h', $hostMonitoringResourceType->getShortType());
        $this->assertEquals('m', $metaServiceMonitoringResourceType->getShortType());
    }

    /**
     * @return MonitoringResource
     * @throws \Assert\AssertionFailedException
     * @throws \InvalidArgumentException
     */
    public static function createHostMonitoringResourceEntity(): MonitoringResource
    {
        $externalLinks = (new ResourceExternalLinks())
            ->setNotes((new Notes('http://www.notes-url.com'))->setLabel('Notes Label'))
            ->setActionUrl('http://action-url.com');

        return (new MonitoringResource(1, 'parentResourceName', 'host'))
            ->setAlias('parentResourceAlias')
            ->setFqdn('localhost')
            ->setHostId(null)
            ->setServiceId(null)
            ->setIcon((new Icon())->setName('dog')->setUrl('/dog.png'))
            ->setCommandLine('/usr/lib64/nagios/plugins/check_icmp -H 127.0.0.1 -n 3 -w 200,20% -c 400,50%')
            ->setMonitoringServerName('Central')
            ->setTimeZone(':Europe/Paris')
            ->setParent(null)
            ->setStatus((new ResourceStatus())
                ->setCode(0)
                ->setName(ResourceStatus::STATUS_NAME_UP)
                ->setSeverityCode(ResourceStatus::SEVERITY_OK))
            ->setFlapping(false)
            ->setInDowntime(false)
            ->setAcknowledged(true)
            ->setActiveChecks(true)
            ->setPassiveChecks(false)
            ->setSeverityLevel(10)
            ->setLastStatusChange(new \DateTime('yesterday'))
            ->setLastNotification(new \DateTime('yesterday'))
            ->setTries('1/3 (H)')
            ->setLastCheck(new \DateTime('tomorrow'))
            ->setNextCheck(new \DateTime('tomorrow'))
            ->setInformation('Host check output')
            ->setPerformanceData('rta=0.342ms;200.000;400.000;0; pl=0%;20;50;0;100 rtmax=0.439ms;;;; rtmin=0.260ms;;;;')
            ->setExecutionTime(0.1)
            ->setLatency(0.214)
            ->setAcknowledgement((new Acknowledgement())
                    ->setNotifyContacts(true)
                    ->setPersistentComment(true)
                    ->setSticky(true))
            ->setGroups([new ResourceGroup(1, 'resourceGroupName')])
            ->setCalculationType('average')
            ->setNotificationEnabled(true)
            ->setHasGraphData(false)
            ->setLinks((new ResourceLinks())
                ->setExternals($externalLinks));
    }

    /**
     * @return MonitoringResource
     * @throws \Assert\AssertionFailedException
     * @throws \InvalidArgumentException
     */
    public static function createServiceMonitoringResourceEntity(): MonitoringResource
    {
        $externalLinks = (new ResourceExternalLinks())
            ->setNotes((new Notes('http://www.notes-url.com'))->setLabel('Notes Label'))
            ->setActionUrl('http://action-url.com');

        $parentResource = MonitoringResourceTest::createHostMonitoringResourceEntity();

        return (new MonitoringResource(10, 'resourceName', 'service'))
            ->setAlias('resourceAlias')
            ->setFqdn('localhost')
            ->setHostId(null)
            ->setServiceId(null)
            ->setIcon((new Icon())->setName('dog')->setUrl('/dog.png'))
            ->setCommandLine('/usr/lib64/nagios/plugins/check_icmp -H 127.0.0.1 -n 3 -w 200,20% -c 400,50%')
            ->setMonitoringServerName('Central')
            ->setTimeZone(':Europe/Paris')
            ->setStatus((new ResourceStatus())
                ->setCode(2)
                ->setName(ResourceStatus::STATUS_NAME_CRITICAL)
                ->setSeverityCode(ResourceStatus::SEVERITY_HIGH))
            ->setFlapping(false)
            ->setInDowntime(false)
            ->setAcknowledged(true)
            ->setActiveChecks(true)
            ->setPassiveChecks(false)
            ->setSeverityLevel(10)
            ->setLastStatusChange(new \DateTime('yesterday'))
            ->setLastNotification(new \DateTime('yesterday'))
            ->setTries('1/3 (H)')
            ->setLastCheck(new \DateTime('yesterday'))
            ->setNextCheck(new \DateTime('tomorrow'))
            ->setInformation('Service check output')
            ->setPerformanceData('rta=0.342ms;200.000;400.000;0; pl=0%;20;50;0;100 rtmax=0.439ms;;;; rtmin=0.260ms;;;;')
            ->setExecutionTime(0.1)
            ->setLatency(0.214)
            ->setAcknowledgement((new Acknowledgement())
                    ->setNotifyContacts(true)
                    ->setPersistentComment(true)
                    ->setSticky(true))
            ->setGroups([new ResourceGroup(1, 'resourceGroupName')])
            ->setCalculationType('average')
            ->setNotificationEnabled(true)
            ->setHasGraphData(true)
            ->setLinks((new ResourceLinks())
                ->setExternals($externalLinks))
            ->setParent($parentResource);
    }
}
