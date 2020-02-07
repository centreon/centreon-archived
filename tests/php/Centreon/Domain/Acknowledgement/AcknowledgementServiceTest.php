<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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
use Centreon\Domain\Acknowledgement\AcknowledgementException;
use PHPUnit\Framework\TestCase;

class AcknowledgementServiceTest extends TestCase
{
    protected $adminContact;
    protected $aclContact;

    protected $hostAcknowledgement;
    protected $serviceAcknowledgement;

    protected $acknowledgementRepository;
    protected $accessGroupRepository;
    protected $monitoringRepository;
    protected $engineService;
    protected $entityValidator;

    protected $violationList;

    protected function setUp()
    {
        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $this->aclContact = (new Contact())
            ->setId(2)
            ->setName('contact')
            ->setAdmin(false);

        $this->hostAcknowledgement = (new Acknowledgement())
            ->setId(1)
            ->setAuthorId(1)
            ->setComment('comment')
            ->setDeletionTime(null)
            ->setEntryTime(new \Datetime())
            ->setHostId(1)
            ->setServiceId(1)
            ->setPollerId(1)
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
            ->setServiceId(0)
            ->setPollerId(1)
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
    public function testFindOneAcknowledgementWithAdminUser()
    {
        $this->acknowledgementRepository->expects(self::any())
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
    public function testFindOneAcknowledgementWithAclUser()
    {
        $this->acknowledgementRepository->expects(self::any())
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
    public function testFindAcknowledgementsWithAdminUser()
    {
        $this->acknowledgementRepository->expects(self::any())
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
    public function testFindAcknowledgementsWithAclUser()
    {
        $this->acknowledgementRepository->expects(self::any())
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
     * test addHostAcknowledgement which is not validated
     */
    public function testAddHostAcknowledgementNotValidated()
    {
        $this->entityValidator->expects(self::any())
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
        $acknowledgementService->addHostAcknowledgement(1, $this->serviceAcknowledgement);
    }

    /**
     * test addHostAcknowledgement whith not found host
     */
    public function testAddHostAcknowledgementNotFoundHost()
    {
        $this->entityValidator->expects(self::any())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));
        $this->monitoringRepository->expects(self::any())
            ->method('findOneHost')
            ->willReturn(null);

        $acknowledgementService = new AcknowledgementService(
            $this->acknowledgementRepository,
            $this->accessGroupRepository,
            $this->monitoringRepository,
            $this->engineService,
            $this->entityValidator
        );

        $this->expectException(AcknowledgementException::class);
        $acknowledgementService->addHostAcknowledgement(10, $this->hostAcknowledgement);
    }
}
