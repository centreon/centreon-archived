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

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Check\CheckService;
use Centreon\Domain\Check\Check;
use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Entity\EntityValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Exception\EntityNotFoundException;
use PHPUnit\Framework\TestCase;

class CheckServiceTest extends TestCase
{
    protected $adminContact;
    protected $aclContact;

    protected $host;
    protected $service;

    protected $check;
    protected $hostCheck;
    protected $serviceCheck;

    protected $hostResource;
    protected $serviceResource;

    protected $monitoringRepository;
    protected $engineService;
    protected $entityValidator;

    protected $violationList;

    protected function setUp(): void
    {
        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $this->aclContact = (new Contact())
            ->setId(2)
            ->setName('contact')
            ->setAdmin(false);

        $this->host = (new Host())
            ->setId(1);

        $this->service = (new Service())
            ->setId(1);

        $this->hostResource = (new Resource())
            ->setType('host')
            ->setId(1);
        $this->serviceResource = (new Resource())
            ->setType('service')
            ->setId(1)
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
    public function testCheckHostNotValidatedCheck()
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
    public function testCheckHostNotFoundHost()
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
    public function testCheckHostSucceed()
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

        $this->assertNull($checkService->checkHost($this->hostCheck));
    }

    /**
     * test checkService with not well formated check
     */
    public function testCheckServiceNotValidatedCheck()
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
    public function testCheckServiceNotFoundHost()
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
    public function testCheckServiceNotFoundService()
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
    public function testCheckServiceSucceed()
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

        $this->assertNull($checkService->checkService($this->serviceCheck));
    }

    /**
     * test checkResource with host not found
     */
    public function testCheckResourceHostNotFound()
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

        $this->assertNull($checkService->checkResource($this->check, $this->hostResource));
    }

    /**
     * test checkResource with service not found
     */
    public function testCheckResourceServiceNotFound()
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

        $this->assertNull($checkService->checkResource($this->check, $this->serviceResource));
    }

    /**
     * test checkResource on host which succeed
     */
    public function testCheckResourceHostSucceed()
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

        $this->assertNull($checkService->checkResource($this->check, $this->hostResource));
    }

    /**
     * test checkResource on service which succeed
     */
    public function testCheckResourceServiceSucceed()
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

        $this->assertNull($checkService->checkResource($this->check, $this->serviceResource));
    }
}
