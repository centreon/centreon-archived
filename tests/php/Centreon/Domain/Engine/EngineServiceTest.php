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

namespace Tests\Centreon\Domain\Engine;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Engine\EngineService;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Monitoring\Comment\Comment;
use Symfony\Component\Validator\ConstraintViolationList;
use Centreon\Domain\Monitoring\SubmitResult\SubmitResult;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Engine\Interfaces\EngineRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationRepositoryInterface;

class EngineServiceTest extends TestCase
{
    protected $engineRepository;
    protected $entityValidator;
    protected $host;
    protected $service;
    protected $hostResult;
    protected $serviceResult;
    protected $adminContact;
    protected $engineService;
    protected $commandHeaderRegex;
    protected $monitoringRepository;
    protected $engineConfigurationService;
    protected $hostComment;
    protected $serviceComment;

    protected function setUp(): void
    {
        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true)
            ->setAlias('adminAlias');

        $this->host = (new Host())
            ->setId(1)
            ->setName('host-test')
            ->setPollerId(1);

        $this->service = (new Service())
            ->setId(1)
            ->setDescription('service-test')
            ->setHost($this->host);

        $this->hostResult = (new SubmitResult($this->host->getId(), 2))
            ->setOutput('Host went down')
            ->setPerformanceData('ping: 0');

        $this->serviceResult = (new SubmitResult($this->service->getId(), 2))
            ->setParentResourceId($this->service->getHost()->getId())
            ->setOutput('Service went critical')
            ->setPerformanceData('ping: 0');

        $this->hostComment = (new Comment($this->host->getID(), 'Simple host comment'))
            ->setDate(new \DateTime('now'));

        $this->serviceComment = (new Comment($this->service->getID(), 'Simple service comment'))
            ->setDate(new \DateTime('now'));
        /**
         * commandHeader should look like 'EXTERNALCMD:<pollerid>:[timestamp] '
         */
        $this->commandHeaderRegex = 'EXTERNALCMD\:' .
            $this->host->getPollerId() . '\:\[\d+\] ';

        $this->engineRepository = $this->createMock(EngineRepositoryInterface::class);
        $this->engineConfigurationService = $this->createMock(EngineConfigurationRepositoryInterface::class);
        $this->engineService = $this->createMock(EngineServiceInterface::class);
        $this->entityValidator = $this->createMock(EntityValidator::class);
        $this->monitoringRepository = $this->createMock(MonitoringRepositoryInterface::class);
    }

    /**
     * Testing the addHostComment EngineService function in a nominal case.
     */
    public function testAddHostComment()
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        /**
         * Creating the command to check that the code
         * will send the same to the sendExternalCommand
         * repository function
         */
        $command = sprintf(
            'ADD_HOST_COMMENT;%s;1;%s;%s',
            $this->host->getName(),
            $this->adminContact->getAlias(),
            $this->hostComment->getComment()
        );

        $this->engineRepository->expects($this->once())
            ->method('sendExternalCommand')
            ->with(
                $this->matchesRegularExpression(
                    '/^' . $this->commandHeaderRegex . str_replace('|', '\|', $command) . '$/'
                )
            );

        $engineService = new EngineService(
            $this->engineRepository,
            $this->engineConfigurationService,
            $this->entityValidator
        );

        $engineService->filterByContact($this->adminContact);
        $this->assertNull($engineService->addHostComment($this->hostComment, $this->host));
    }

    /**
    * Testing the addServiceComment EngineService function in a nominal case.
    */
    public function testServiceComment()
    {
        $this->entityValidator->expects($this->once())
           ->method('validate')
           ->willReturn(new ConstraintViolationList());

        /**
         * Creating the command to check that the code
         * will send the same to the sendExternalCommand
         * repository function
         */
        $command = sprintf(
            'ADD_SVC_COMMENT;%s;%s;1;%s;%s',
            $this->host->getName(),
            $this->service->getDescription(),
            $this->adminContact->getAlias(),
            $this->serviceComment->getComment()
        );

        $this->engineRepository->expects($this->once())
           ->method('sendExternalCommand')
           ->with(
               $this->matchesRegularExpression(
                   '/^' . $this->commandHeaderRegex . str_replace('|', '\|', $command) . '$/'
               )
           );

        $engineService = new EngineService(
            $this->engineRepository,
            $this->engineConfigurationService,
            $this->entityValidator
        );

        $engineService->filterByContact($this->adminContact);
        $this->assertNull($engineService->addServiceComment($this->serviceComment, $this->service));
    }

    /**
     * Testing the submitHostResult EngineService function in a nominal case.
     */
    public function testSubmitHostResult()
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        /**
         * Creating the command to check that the code
         * will send the same to the sendExternalCommand
         * repository function
         */
        $command = sprintf(
            '%s;%s;%d;%s|%s',
            'PROCESS_HOST_CHECK_RESULT',
            $this->host->getName(),
            $this->hostResult->getStatus(),
            $this->hostResult->getOutput(),
            $this->hostResult->getPerformanceData()
        );

        $this->engineRepository->expects($this->once())
            ->method('sendExternalCommand')
            ->with(
                $this->matchesRegularExpression(
                    '/^' . $this->commandHeaderRegex . str_replace('|', '\|', $command) . '$/'
                )
            );

        $engineService = new EngineService(
            $this->engineRepository,
            $this->engineConfigurationService,
            $this->entityValidator
        );

        $engineService->filterByContact($this->adminContact);
        $this->assertNull($engineService->submitHostResult($this->hostResult, $this->host));
    }

    /**
     * Testing the submitServiceResult EngineService function in a nominal case.
     */
    public function testSubmitServiceResult()
    {
        $this->entityValidator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        /**
         * Creating the command to check that the code
         * will send the same to the sendExternalCommand
         * repository function
         */
        $command = sprintf(
            '%s;%s;%s;%d;%s|%s',
            'PROCESS_SERVICE_CHECK_RESULT',
            $this->service->getHost()->getName(),
            $this->service->getDescription(),
            $this->serviceResult->getStatus(),
            $this->serviceResult->getOutput(),
            $this->serviceResult->getPerformanceData()
        );

        $this->engineRepository->expects($this->once())
            ->method('sendExternalCommand')
            ->with(
                $this->matchesRegularExpression(
                    '/^' . $this->commandHeaderRegex . str_replace('|', '\|', $command) . '$/'
                )
            );

        $engineService = new EngineService(
            $this->engineRepository,
            $this->engineConfigurationService,
            $this->entityValidator
        );

        $engineService->filterByContact($this->adminContact);
        $this->assertNull($engineService->submitServiceResult($this->serviceResult, $this->service));
    }

    /**
     * Testing the disacknowledgeHost EngineService method in a nominal case.
     */
    public function testDisacknowledgeHost()
    {
        /**
         * Creating the command to check that the code
         * will send the same to the sendExternalCommand
         * repository function
         */
        $command = sprintf(
            '%s;%s',
            'REMOVE_HOST_ACKNOWLEDGEMENT',
            $this->host->getName()
        );

        $this->engineRepository->expects($this->once())
            ->method('sendExternalCommand')
            ->with($this->matchesRegularExpression('/^' . $this->commandHeaderRegex . $command . '$/'));

        $engineService = new EngineService(
            $this->engineRepository,
            $this->engineConfigurationService,
            $this->entityValidator
        );

        $engineService->filterByContact($this->adminContact);
        $this->assertNull($engineService->disacknowledgeHost($this->host));
    }

    /**
     * Testing the disacknowledgeService EngineService method in a nominal case.
     */
    public function testDisacknowledgeService()
    {
        /**
         * Creating the command to check that the code
         * will send the same to the sendExternalCommand
         * repository function
         */
        $command = sprintf(
            '%s;%s;%s',
            'REMOVE_SVC_ACKNOWLEDGEMENT',
            $this->service->getHost()->getName(),
            $this->service->getDescription()
        );

        $this->engineRepository->expects($this->once())
            ->method('sendExternalCommand')
            ->with($this->matchesRegularExpression('/^' . $this->commandHeaderRegex . $command . '$/'));

        $engineService = new EngineService(
            $this->engineRepository,
            $this->engineConfigurationService,
            $this->entityValidator
        );

        $engineService->filterByContact($this->adminContact);
        $this->assertNull($engineService->disacknowledgeService($this->service));
    }
}
