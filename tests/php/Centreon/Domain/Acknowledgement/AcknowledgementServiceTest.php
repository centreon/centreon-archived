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

namespace Tests\Centreon\Domain\Acknowledgement;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Acknowledgement\Interfaces\AcknowledgementRepositoryInterface;
use Centreon\Domain\Acknowledgement\AcknowledgementService;
use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Entity\EntityValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Acknowledgement\AcknowledgementException;
use PHPUnit\Framework\TestCase;

class AcknowledgementServiceTest extends TestCase
{
    /**
     * @var Contact
     */
    private $adminContact;

    /**
     * @var Contact
     */
    private $aclContact;

    /**
     * @var Host
     */
    private $host;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var Acknowledgement
     */
    private $hostAcknowledgement;

    /**
     * @var Acknowledgement
     */
    private $serviceAcknowledgement;

    /**
     * @var AcknowledgementRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $acknowledgementRepository;

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
    private $violationList;

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

        $this->hostAcknowledgement = (new Acknowledgement())
            ->setId(1)
            ->setAuthorId(1)
            ->setComment('comment')
            ->setDeletionTime(null)
            ->setEntryTime(new \Datetime())
            ->setHostId(1)
            ->setPollerId(1)
            ->setResourceId(1)
            ->setParentResourceId(null)
            ->setNotifyContacts(true)
            ->setPersistentComment(true)
            ->setSticky(true)
            ->setState(0)
            ->setType(0);

        $this->serviceAcknowledgement = (new Acknowledgement())
            ->setId(2)
            ->setAuthorId(1)
            ->setComment('comment')
            ->setDeletionTime(null)
            ->setEntryTime(new \Datetime())
            ->setHostId(1)
            ->setServiceId(1)
            ->setPollerId(1)
            ->setResourceId(1)
            ->setParentResourceId(1)
            ->setNotifyContacts(true)
            ->setPersistentComment(true)
            ->setSticky(true)
            ->setState(0)
            ->setType(1);

        $this->acknowledgementRepository = $this->createMock(AcknowledgementRepositoryInterface::class);
        $this->accessGroupRepository = $this->createMock(AccessGroupRepositoryInterface::class);
        $this->monitoringRepository = $this->createMock(MonitoringRepositoryInterface::class);
        $this->engineService = $this->createMock(EngineServiceInterface::class);
        $this->entityValidator = $this->createMock(EntityValidator::class);

        $violation = new ConstraintViolation(
            'wrong format',
            'wrong format',
            [],
            $this->serviceAcknowledgement,
            'propertyPath',
            'InvalidValue'
        );
        $this->violationList = new ConstraintViolationList([$violation]);
    }

    /**
     * test findOneAcknowledgement with admin user
     */
    public function testFindOneAcknowledgementWithAdminUser(): void
    {
        $this->acknowledgementRepository->expects($this->once())
            ->method('findOneAcknowledgementForAdminUser')
            ->willReturn($this->hostAcknowledgement);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $acknowledgementService->filterByContact($this->adminContact);

        $acknowledgement = $acknowledgementService->findOneAcknowledgement(1);
        $this->assertEquals($acknowledgement, $this->hostAcknowledgement);
    }

    /**
     * test findOneAcknowledgement with acl user
     */
    public function testFindOneAcknowledgementWithAclUser(): void
    {
        $this->acknowledgementRepository->expects($this->once())
            ->method('findOneAcknowledgementForNonAdminUser')
            ->willReturn($this->serviceAcknowledgement);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $acknowledgementService->filterByContact($this->aclContact);

        $acknowledgement = $acknowledgementService->findOneAcknowledgement(2);
        $this->assertEquals($acknowledgement, $this->serviceAcknowledgement);
    }

    /**
     * test findAcknowledgements with admin user
     */
    public function testFindAcknowledgementsWithAdminUser(): void
    {
        $this->acknowledgementRepository->expects($this->once())
            ->method('findAcknowledgementsForAdminUser')
            ->willReturn([$this->hostAcknowledgement]);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $acknowledgementService->filterByContact($this->adminContact);

        $acknowledgement = $acknowledgementService->findAcknowledgements();
        $this->assertEquals($acknowledgement, [$this->hostAcknowledgement]);
    }

    /**
     * test findAcknowledgements with acl user
     */
    public function testFindAcknowledgementsWithAclUser(): void
    {
        $this->acknowledgementRepository->expects($this->once())
            ->method('findAcknowledgementsForNonAdminUser')
            ->willReturn([$this->serviceAcknowledgement]);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );
        $acknowledgementService->filterByContact($this->aclContact);

        $acknowledgement = $acknowledgementService->findAcknowledgements();
        $this->assertEquals($acknowledgement, [$this->serviceAcknowledgement]);
    }

    /**
     * test addHostAcknowledgement which is not valid (eg: missing hostId)
     */
    public function testAddHostAcknowledgementNotValidated(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn($this->violationList);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $this->expectException(ValidationFailedException::class);
        $acknowledgementService->addHostAcknowledgement($this->serviceAcknowledgement);
    }

    /**
     * test addHostAcknowledgement with not found host
     */
    public function testAddHostAcknowledgementNotFoundHost(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn(null);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $this->expectException(EntityNotFoundException::class);
        $acknowledgementService->addHostAcknowledgement($this->hostAcknowledgement);
    }

    /**
     * test addHostAcknowledgement which succeed
     */
    public function testAddHostAcknowledgementWhichSucceed(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);
        $this->engineService->expects($this->once())
            ->method('addHostAcknowledgement')
            ->willReturn(null);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        /** @phpstan-ignore-next-line */
        $this->assertNull($acknowledgementService->addHostAcknowledgement($this->hostAcknowledgement));
    }

    /**
     * test addServiceAcknowledgement which is not validated
     */
    public function testAddServiceAcknowledgementNotValidated(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn($this->violationList);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $this->expectException(ValidationFailedException::class);
        $acknowledgementService->addServiceAcknowledgement($this->serviceAcknowledgement);
    }

    /**
     * test addServiceAcknowledgement with not found service
     */
    public function testAddServiceAcknowledgementNotFoundService(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));
        $this->monitoringRepository->expects($this->once())
            ->method('findOneService')
            ->willReturn(null);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $this->expectException(EntityNotFoundException::class);
        $acknowledgementService->addServiceAcknowledgement($this->serviceAcknowledgement);
    }

    /**
     * test addServiceAcknowledgement with not found host
     */
    public function testAddServiceAcknowledgementNotFoundHost(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));
        $this->monitoringRepository->expects($this->once())
            ->method('findOneService')
            ->willReturn($this->service);
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn(null);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $this->expectException(EntityNotFoundException::class);
        $acknowledgementService->addServiceAcknowledgement($this->serviceAcknowledgement);
    }

    /**
     * test addServiceAcknowledgement which succeed
     */
    public function testAddServiceAcknowledgementWhichSucceed(): void
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));
        $this->monitoringRepository->expects($this->once())
            ->method('findOneService')
            ->willReturn($this->service);
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);
        $this->engineService->expects($this->once())
            ->method('addServiceAcknowledgement')
            ->willReturn(null);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        /** @phpstan-ignore-next-line */
        $this->assertNull($acknowledgementService->addServiceAcknowledgement($this->serviceAcknowledgement));
    }

    /**
     * test findHostsAcknowledgements
     */
    public function testFindHostsAcknowledgements(): void
    {
        $this->acknowledgementRepository->expects($this->once())
            ->method('findHostsAcknowledgements')
            ->willReturn([$this->hostAcknowledgement]);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $acknowledgement = $acknowledgementService->findHostsAcknowledgements();
        $this->assertEquals($acknowledgement, [$this->hostAcknowledgement]);
    }

    /**
     * test findServicesAcknowledgements
     */
    public function testFindServicesAcknowledgements(): void
    {
        $this->acknowledgementRepository->expects($this->once())
            ->method('findServicesAcknowledgements')
            ->willReturn([$this->serviceAcknowledgement]);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $acknowledgement = $acknowledgementService->findServicesAcknowledgements();
        $this->assertEquals($acknowledgement, [$this->serviceAcknowledgement]);
    }

    /**
     * test findAcknowledgementsByHost
     */
    public function testFindAcknowledgementsByHost(): void
    {
        $this->acknowledgementRepository->expects($this->once())
            ->method('findAcknowledgementsByHost')
            ->willReturn([$this->hostAcknowledgement]);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $acknowledgement = $acknowledgementService->findAcknowledgementsByHost(1);
        $this->assertEquals($acknowledgement, [$this->hostAcknowledgement]);
    }

    /**
     * test findAcknowledgementsByService
     */
    public function testFindAcknowledgementsByService(): void
    {
        $this->acknowledgementRepository->expects($this->once())
            ->method('findAcknowledgementsByService')
            ->willReturn([$this->serviceAcknowledgement]);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $acknowledgement = $acknowledgementService->findAcknowledgementsByService(1, 1);
        $this->assertEquals($acknowledgement, [$this->serviceAcknowledgement]);
    }

    /**
     * test disacknowledgeHost with not found host
     */
    public function testDisacknowledgeHostNotFoundHost(): void
    {
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn(null);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $this->expectException(EntityNotFoundException::class);
        $acknowledgementService->disacknowledgeHost(1);
    }

    /**
     * test disacknowledgeHost with not found acknowledgement
     */
    public function testDisacknowledgeHostNotFoundAcknowledgement(): void
    {
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);
        $this->acknowledgementRepository->expects($this->once())
            ->method('findLatestHostAcknowledgement')
            ->willReturn(null);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $this->expectException(AcknowledgementException::class);
        $acknowledgementService->disacknowledgeHost(1);
    }

    /**
     * test disacknowledgeHost which is already disacknowledge
     */
    public function testDisacknowledgeHostAlreadyDisacknowledged(): void
    {
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);
        $hostAcknowledgement = $this->hostAcknowledgement->setDeletionTime(new \DateTime());
        $this->acknowledgementRepository->expects($this->once())
            ->method('findLatestHostAcknowledgement')
            ->willReturn($hostAcknowledgement);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $this->expectException(AcknowledgementException::class);
        $acknowledgementService->disacknowledgeHost(1);
    }

    /**
     * test disacknowledgeHost which succeed
     */
    public function testDisacknowledgeHostSucceed(): void
    {
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);
        $this->acknowledgementRepository->expects($this->once())
            ->method('findLatestHostAcknowledgement')
            ->willReturn($this->hostAcknowledgement);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        /** @phpstan-ignore-next-line */
        $this->assertNull($acknowledgementService->disacknowledgeHost(1));
    }

    /**
     * test disacknowledgeService with not found service
     */
    public function testDisacknowledgeServiceNotFoundService(): void
    {
        $this->monitoringRepository->expects($this->once())
            ->method('findOneservice')
            ->willReturn(null);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $this->expectException(EntityNotFoundException::class);
        $acknowledgementService->disacknowledgeService(1, 1);
    }

    /**
     * test disacknowledgeService with not found acknowledgement
     */
    public function testDisacknowledgeServiceNotFoundAcknowledgement(): void
    {
        $this->monitoringRepository->expects($this->once())
            ->method('findOneService')
            ->willReturn($this->service);
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);
        $this->acknowledgementRepository->expects($this->once())
            ->method('findLatestServiceAcknowledgement')
            ->willReturn(null);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $this->expectException(AcknowledgementException::class);
        $acknowledgementService->disacknowledgeService(1, 1);
    }

    /**
     * test disacknowledgeService which is already disacknowledge
     */
    public function testDisacknowledgeServiceAlreadyDisacknowledged(): void
    {
        $this->monitoringRepository->expects($this->once())
            ->method('findOneService')
            ->willReturn($this->service);
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);
        $serviceAcknowledgement = $this->serviceAcknowledgement->setDeletionTime(new \DateTime());
        $this->acknowledgementRepository->expects($this->once())
            ->method('findLatestServiceAcknowledgement')
            ->willReturn($serviceAcknowledgement);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $this->expectException(AcknowledgementException::class);
        $acknowledgementService->disacknowledgeService(1, 1);
    }

    /**
     * test disacknowledgeService which succeed
     */
    public function testDisacknowledgeServiceSucceed(): void
    {
        $this->monitoringRepository->expects($this->once())
            ->method('findOneService')
            ->willReturn($this->service);
        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);
        $this->acknowledgementRepository->expects($this->once())
            ->method('findLatestServiceAcknowledgement')
            ->willReturn($this->hostAcknowledgement);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        /** @phpstan-ignore-next-line */
        $this->assertNull($acknowledgementService->disacknowledgeService(1, 1));
    }
}
