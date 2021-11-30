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

namespace Core\Application\RealTime\UseCase\FindHost;

use Centreon\Domain\Contact\Contact;
use Core\Domain\RealTime\Model\Host;
use Core\Domain\RealTime\Model\Downtime;
use Centreon\Domain\Security\AccessGroup;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Application\Common\UseCase\NotFoundResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Monitoring\Host as LegacyHost;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\DbReadHostRepositoryInterface;
use Core\Application\RealTime\UseCase\FindHost\FindHostPresenterInterface;
use Core\Application\RealTime\Repository\DbReadDowntimeRepositoryInterface;
use Core\Application\RealTime\Repository\DbReadHostgroupRepositoryInterface;
use Core\Application\RealTime\Repository\DbReadAcknowledgementRepositoryInterface;

class FindHost
{
    use LoggerTrait;

    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * @param DbReadHostRepositoryInterface $repository
     * @param DbReadHostgroupRepositoryInterface $hostgroupRepository
     * @param ContactInterface $contact
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param DbReadDowntimeRepositoryInterface $downtimeRepository
     */
    public function __construct(
        private DbReadHostRepositoryInterface $repository,
        private DbReadHostgroupRepositoryInterface $hostgroupRepository,
        ContactInterface $contact,
        private AccessGroupRepositoryInterface $accessGroupRepository,
        private DbReadDowntimeRepositoryInterface $downtimeRepository,
        private DbReadAcknowledgementRepositoryInterface $acknowledgementRepository,
        private MonitoringServiceInterface $monitoringService,
    ) {
        $this->contact = $contact;
    }

    /**
     * @param int $hostId
     * @param FindHostPresenterInterface $presenter
     * @return void
     */
    public function __invoke(int $hostId, FindHostPresenterInterface $presenter): void
    {
        /**
         * @var Contact
         */
        $contact = $this->contact;

        $this->debug(
            "[FindHost] Searching details for host",
            [
                "id" => $hostId
            ]
        );

        if ($contact->isAdmin() === true) {
            $host = $this->repository->findHostById($hostId);
            if ($host === null) {
                $this->debug(
                    "[FindHost] Host not found",
                    [
                        'id' => $hostId,
                        'userId' => $contact->getId()
                    ]
                );
                $presenter->setResponseStatus(new NotFoundResponse('Host'));
                return;
            }
            $hostgroups = $this->hostgroupRepository->findAllByHostId($hostId);
        } else {
            /**
             * @var AccessGroup[]
             */
            $accessGroups = $this->accessGroupRepository->findByContact($contact);
            $accessGroupIds = array_map(
                function ($accessGroup) {
                    return $accessGroup->getId();
                },
                $accessGroups
            );
            $host = $this->repository->findHostByIdAndAccessGroupIds($hostId, $accessGroupIds);
            if ($host === null) {
                $this->debug(
                    "[FindHost] Host not found",
                    [
                        'id' => $hostId,
                        'userId' => $contact->getId()
                    ]
                );
                $presenter->setResponseStatus(new NotFoundResponse('Host'));
                return;
            }
            $hostgroups = $this->hostgroupRepository->findAllByHostIdAndAccessGroupIds($hostId, $accessGroupIds);
        }

        if (!empty($hostgroups)) {
            foreach ($hostgroups as $hostgroup) {
                $host->addHostgroup($hostgroup);
            }
        }

        /**
         * Check if user can see the commandLine.
         * If so, then hide potential passwords.
         */
        if (
            $contact->isAdmin() ||
            $contact->hasRole(Contact::ROLE_DISPLAY_COMMAND)
        ) {
            try {
                /**
                 * @todo Workaround as we did not moved the monitoring service into new architecture
                 */
                $legacyHost = (new LegacyHost())
                    ->setId($host->getId())
                    ->setCheckCommand($host->getCommandLine());

                $this->monitoringService->hidePasswordInHostCommandLine($legacyHost);
            } catch (\Throwable $ex) {
                $this->debug(
                    "[FindHost] Failed to hide password in host command line",
                    [
                        'id' => $hostId,
                        'reason' => $ex->getMessage()
                    ]
                );
                $host->setCommandLine(
                    sprintf(_('Unable to hide passwords in command (Reason: %s)'), $ex->getMessage())
                );
            }
        } else {
            $host->setCommandLine(null);
        }

        $presenter->present(
            $this->createResponse(
                $host,
                $this->downtimeRepository->findDowntimesByHostId($hostId),
                $this->acknowledgementRepository->findOnGoingAcknowledgementByHostId($hostId)
            )
        );
    }

    /**
     * @param Host $host
     * @param Downtime[] $downtimes
     * @param Acknowledgement|null $acknowledgement
     * @return FindHostResponse
     */
    private function createResponse(Host $host, array $downtimes, ?Acknowledgement $acknowledgement): FindHostResponse
    {
        return new FindHostResponse(
            $host->getId(),
            $host->getName(),
            $host->getAddress(),
            $host->getMonitoringServerName(),
            $host->getTimezone(),
            $host->getAlias(),
            $host->isFlapping(),
            $host->isAcknowledged(),
            $host->isInDowntime(),
            $host->getOutput(),
            $host->getPerformanceData(),
            $host->getCommandLine(),
            $host->getNotificationNumber(),
            $host->getLastStatusChange(),
            $host->getLastNotification(),
            $host->getLatency(),
            $host->getExecutionTime(),
            $host->getStatusChangePercentage(),
            $host->getNextCheck(),
            $host->getLastCheck(),
            $host->hasPassiveChecks(),
            $host->hasActiveChecks(),
            $host->getLastTimeUp(),
            $host->getSeverityLevel(),
            $host->getCheckAttemps(),
            $host->getMaxCheckAttemps(),
            $host->getStatus(),
            $host->getIcon(),
            $host->getHostgroups(),
            $downtimes,
            $acknowledgement
        );
    }
}
