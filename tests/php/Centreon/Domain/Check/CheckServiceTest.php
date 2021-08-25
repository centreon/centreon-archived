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

namespace Tests\Centreon\Domain\Check;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Check\Check;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Check\CheckService;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Entity\EntityValidator;
use Symfony\Component\Validator\ConstraintViolation;
use Centreon\Domain\Exception\EntityNotFoundException;
use JMS\Serializer\Exception\ValidationFailedException;
use Symfony\Component\Validator\ConstraintViolationList;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;

class CheckServiceTest extends TestCase
{
    /**
     * @var Contact
     */
    private $adminContact;

    /**
     * @var Host
     */
    private $host;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var Check
     */
    private $check;

    /**
     * @var Check
     */
    private $hostCheck;

    /**
     * @var Check
     */
    private $serviceCheck;

    /**
     * @var MonitoringResource
     */
    private $hostResource;

    /**
     * @var MonitoringResource
     */
    private $serviceResource;

    /**
     * @var AccessGroupRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $accessGroupRepository;

    /**
     * @var MonitoringRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $monitoringRepository;

    /**
     * @var EngineServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $engineService;

    /**
     * @var EntityValidator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $entityValidator;

    /**
     * @var ConstraintViolationList
     */
    protected $violationList;

    protected function setUp(): void
    {
        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $this->host = (new Host())
            ->setId(1);

        $this->service = (new Service())
            ->setId(1);

        $this->hostResource = new MonitoringResource(1, 'hostName', 'host');
        $this->serviceResource = (new MonitoringResource(1, 'serviceName', 'service'))
            ->setParent($this->hostResource);

        $this->check = (new Check())
            ->setCheckTime(new \DateTime());

        $this->hostCheck = (new Check())
            ->setResourceId(1);

        $this->serviceCheck = (new Check())
            ->setResourceId(1)
            ->setParentResourceId(1);

        $this->accessGroupRepository = $this->createMock(AccessGroupRepositoryInterface::class);
        $this->monitoringRepository = $this->createMock(MonitoringRepositoryInterface::class);
        $this->engineService = $this->createMock(EngineServiceInterface::class);
        $this->entityValidator = $this->createMock(EntityValidator::class);

        $violation = new ConstraintViolation(
            'wrong format',
            'wrong format',
            [],
            $this->serviceCheck,
            'propertyPath',
            'InvalidValue'
        );
        $this->violationList = new ConstraintViolationList([$violation]);
    }

    /**
     * test checkHost with not well formated check
     */
    public function testCheckHostNotValidatedCheck(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn($this->violationList);

        $checkService = new CheckService(
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $checkService->filterByContact($this->adminContact);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('Validation failed with 1 error(s).');

        $checkService->checkHost($this->hostCheck);
    }

    /**
     * test checkHost with not found host
     */
    public function testCheckHostNotFoundHost(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn(null);

        $checkService = new CheckService(
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $checkService->filterByContact($this->adminContact);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Host not found');

        $checkService->checkHost($this->hostCheck);
    }

    /**
     * test checkHost which succeed
     */
    public function testCheckHostSucceed(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);

        $this->engineService->expects($this->once())
            ->method('scheduleHostCheck');

        $checkService = new CheckService(
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $checkService->filterByContact($this->adminContact);

        /** @phpstan-ignore-next-line */
        $this->assertNull($checkService->checkHost($this->hostCheck));
    }

    /**
     * test checkService with not well formated check
     */
    public function testCheckServiceNotValidatedCheck(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn($this->violationList);

        $checkService = new CheckService(
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $checkService->filterByContact($this->adminContact);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('Validation failed with 1 error(s).');

        $checkService->checkService($this->serviceCheck);
    }

    /**
     * test checkService with not found host
     */
    public function testCheckServiceNotFoundHost(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn(null);

        $checkService = new CheckService(
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $checkService->filterByContact($this->adminContact);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Host not found');

        $checkService->checkService($this->serviceCheck);
    }

    /**
     * test checkService with not found host
     */
    public function testCheckServiceNotFoundService(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);

        $this->monitoringRepository->expects($this->once())
            ->method('findOneService')
            ->willReturn(null);

        $checkService = new CheckService(
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $checkService->filterByContact($this->adminContact);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Service not found');

        $checkService->checkService($this->serviceCheck);
    }

    /**
     * test checkService which succeed
     */
    public function testCheckServiceSucceed(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);

        $this->monitoringRepository->expects($this->once())
            ->method('findOneService')
            ->willReturn($this->service);

        $this->engineService->expects($this->once())
            ->method('scheduleServiceCheck');

        $checkService = new CheckService(
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $checkService->filterByContact($this->adminContact);

        /** @phpstan-ignore-next-line */
        $this->assertNull($checkService->checkService($this->serviceCheck));
    }

    /**
     * test checkResource with host not found
     */
    public function testCheckResourceHostNotFound(): void
    {
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn(null);

        $checkService = new CheckService(
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $checkService->filterByContact($this->adminContact);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Host 1 not found');

        /** @phpstan-ignore-next-line */
        $this->assertNull($checkService->checkResource($this->check, $this->hostResource));
    }

    /**
     * test checkResource with service not found
     */
    public function testCheckResourceServiceNotFound(): void
    {
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);

        $this->monitoringRepository->expects($this->once())
            ->method('findOneService')
            ->willReturn(null);

        $checkService = new CheckService(
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $checkService->filterByContact($this->adminContact);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Service 1 (parent: 1) not found');

        /** @phpstan-ignore-next-line */
        $this->assertNull($checkService->checkResource($this->check, $this->serviceResource));
    }

    /**
     * test checkResource on host which succeed
     */
    public function testCheckResourceHostSucceed(): void
    {
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);

        $this->engineService->expects($this->once())
            ->method('scheduleHostCheck');

        $checkService = new CheckService(
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $checkService->filterByContact($this->adminContact);

        /** @phpstan-ignore-next-line */
        $this->assertNull($checkService->checkResource($this->check, $this->hostResource));
    }

    /**
     * test checkResource on service which succeed
     */
    public function testCheckResourceServiceSucceed(): void
    {
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);

        $this->monitoringRepository->expects($this->once())
            ->method('findOneService')
            ->willReturn($this->service);

        $this->engineService->expects($this->once())
            ->method('scheduleServiceCheck');

        $checkService = new CheckService(
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $checkService->filterByContact($this->adminContact);

        /** @phpstan-ignore-next-line */
        $this->assertNull($checkService->checkResource($this->check, $this->serviceResource));
    }
}
