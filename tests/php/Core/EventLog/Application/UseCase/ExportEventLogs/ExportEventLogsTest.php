<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Tests\Core\EventLog\Application\UseCase\ExportEventLogs;

use PHPUnit\Framework\TestCase;
use Core\Security\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\EventLog\Domain\EventLog;
use Core\EventLog\Application\UseCase\ExportEventLogs\ExportEventLogsErrorResponse;
use Core\EventLog\Application\UseCase\ExportEventLogs\FindEventLogsResponse;
use Core\EventLog\Application\Repository\ReadEventLogRepositoryInterface;
use Core\EventLog\Application\UseCase\ExportEventLogs\ExportEventLogsPresenterInterface;
use Core\EventLog\Application\UseCase\ExportEventLogs\ExportEventLogs;
use Centreon\Domain\Contact\Interfaces\ContactInterface;

class ExportEventLogsTest extends TestCase
{
    private ContactInterface $contact;
    private ReadEventLogRepositoryInterface $eventLogRepository;
    private ReadAccessGroupRepositoryInterface $accessGroupRepository;
    private ExportEventLogsPresenterInterface $presenter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contact = $this->createMock(ContactInterface::class);
        $this->eventLogRepository = $this->createMock(ReadEventLogRepositoryInterface::class);
        $this->accessGroupRepository = $this->createMock(ReadAccessGroupRepositoryInterface::class);
        $this->presenter = $this->createMock(ExportEventLogsPresenterInterface::class);
    }

    /**
     * @dataProvider contactDataProvider
     */
    public function testAsAdmin(array $rawEventLogs, array $expectedEventLogs): void
    {
        $this->makeContactAdmin();
        $this->setFindAllReturnOfEventRepository($expectedEventLogs);
        $this->assertFindByContactIsNeverCalledInAccessGroupRepository();
        $expectedResponse = $this->generateFindEventLogsResponse($rawEventLogs);
        $this->setPresentMethodReturnOfPresent($expectedResponse);

        $exportEventLogs = new ExportEventLogs($this->contact, $this->eventLogRepository, $this->accessGroupRepository);

        $exportEventLogs($this->presenter);
    }

    public function contactDataProvider(): iterable
    {
        yield 'no record' => [
            [],
            []
        ];

        yield 'only one record' => [
            [
                ['host_id' => 1],
            ],
            [
                new EventLog(1),
            ]
        ];

        yield 'multiple records' => [
            [
                ['host_id' => 1],
                ['host_id' => 2],
                ['host_id' => 3],
            ],
            [
                new EventLog(1),
                new EventLog(2),
                new EventLog(3),
            ]
        ];
    }

    /**
     * @dataProvider userWithACLDataProvider
     */
    public function testUserWithACL(array $accessGroups, array $rawEventLogs, array $responseData)
    {
        $this->setFindByContactReturnOfAccessGroupRepository($accessGroups);
        $this->assertFindAllIsNeverCalledInEventLogRepository();
        $this->assertFindByAccessGroupsOfEventRepository($rawEventLogs, $accessGroups);
        $expectedResponse = $this->generateFindEventLogsResponse($responseData);
        $this->setPresentMethodReturnOfPresent($expectedResponse);

        $exportEventLogs = new ExportEventLogs($this->contact, $this->eventLogRepository, $this->accessGroupRepository);

        $exportEventLogs($this->presenter);
    }

    public function userWithACLDataProvider(): iterable
    {
        yield 'no record' => [
            [],
            [],
            [],
        ];

        yield 'only one record' => [
            [
                ['id' => 1],
            ],
            [
                new EventLog(1),
            ],
            [
                ['host_id' => 1]
            ],
        ];

        yield 'multiple records' => [
            [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ],
            [
                new EventLog(1),
                new EventLog(2),
                new EventLog(3),
            ],
            [
                ['host_id' => 1],
                ['host_id' => 2],
                ['host_id' => 3],
            ],
        ];
    }

    public function testException(): void
    {
        $this->findByContactMethodThrowsAnException();

        $exportEventLogs = new ExportEventLogs($this->contact, $this->eventLogRepository, $this->accessGroupRepository);

        $this->presenter
            ->expects($this->never())
            ->method('present');

        $this->presenter
            ->expects($this->once())
            ->method('setResponseStatus')
            ->with(
                $this->isInstanceOf(ExportEventLogsErrorResponse::class)
            );

        $exportEventLogs($this->presenter);
    }

    private function assertFindAllIsNeverCalledInEventLogRepository(): void
    {
        $this->eventLogRepository->expects($this->never())->method('findAll');
    }

    private function setFindByContactReturnOfAccessGroupRepository(array $contactAccessGroups): void
    {
        $this->accessGroupRepository
            ->expects($this->once())
            ->method('findByContact')
            ->with($this->equalTo($this->contact))
            ->willReturn($contactAccessGroups);
    }

    private function makeContactAdmin(): void
    {
        $this->contact->method('isAdmin')->willReturn(true);
    }

    private function assertFindByContactIsNeverCalledInAccessGroupRepository(): void
    {
        $this->accessGroupRepository->expects($this->never())->method('findByContact');
    }

    private function setFindAllReturnOfEventRepository(mixed $findAllMethodReturn): void
    {
        $this->eventLogRepository->expects($this->once())->method('findAll')->willReturn($findAllMethodReturn);
    }

    private function assertFindByAccessGroupsOfEventRepository(
        mixed $findByAccessGroupsMethodReturn,
        array $expectedContactAccessGroups
    ): void {
        $this->eventLogRepository
            ->expects($this->once())
            ->method('findByAccessGroups')
            ->with($this->equalTo($expectedContactAccessGroups))
            ->willReturn($findByAccessGroupsMethodReturn);
    }

    private function setPresentMethodReturnOfPresent(mixed $expectedResponse): void
    {
        $this->presenter->expects($this->once())->method('present')->with($this->equalTo($expectedResponse));
    }

    private function generateFindEventLogsResponse(array $eventLogs): FindEventLogsResponse
    {
        $response = new FindEventLogsResponse();
        $response->eventLogs = $eventLogs;

        return $response;
    }

    private function findByContactMethodThrowsAnException(): void
    {
        $this->accessGroupRepository
            ->expects($this->once())
            ->method('findByContact')
            ->with($this->equalTo($this->contact))
            ->willThrowException(new \PDOException('Unable to connect to the database'));
    }
}
