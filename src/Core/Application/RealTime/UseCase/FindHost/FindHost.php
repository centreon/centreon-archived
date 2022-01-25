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

namespace Core\Application\RealTime\UseCase\FindHost;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Log\LoggerTrait;
use Core\Domain\RealTime\Model\Host;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Acknowledgement;
use Centreon\Domain\Monitoring\Host as LegacyHost;
use Core\Application\Common\UseCase\NotFoundResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\RealTime\UseCase\FindHost\FindHostResponse;
use Core\Application\RealTime\UseCase\FindHost\HostNotFoundResponse;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Application\RealTime\Repository\ReadHostgroupRepositoryInterface;
use Core\Application\RealTime\UseCase\FindHost\FindHostPresenterInterface;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;

class FindHost
{
    use LoggerTrait;

    /**
     * @param ReadHostRepositoryInterface $repository
     * @param ReadHostgroupRepositoryInterface $hostgroupRepository
     * @param ContactInterface $contact
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param ReadDowntimeRepositoryInterface $downtimeRepository
     */
    public function __construct(
        private ReadHostRepositoryInterface $repository,
        private ReadHostgroupRepositoryInterface $hostgroupRepository,
        private ContactInterface $contact,
        private AccessGroupRepositoryInterface $accessGroupRepository,
        private ReadDowntimeRepositoryInterface $downtimeRepository,
        private ReadAcknowledgementRepositoryInterface $acknowledgementRepository,
        private MonitoringServiceInterface $monitoringService,
    ) {
    }

    /**
     * @param int $hostId
     * @param FindHostPresenterInterface $presenter
     * @return void
     */
    public function __invoke(int $hostId, FindHostPresenterInterface $presenter): void
    {
        $hostgroups = [];

        $this->info(
            "Searching details for host",
            [
                "id" => $hostId
            ]
        );

        if ($this->contact->isAdmin()) {
            $host = $this->repository->findHostById($hostId);
            if ($host === null) {
                $this->critical(
                    "Host not found",
                    [
                        'id' => $hostId,
                        'userId' => $this->contact->getId()
                    ]
                );
                $presenter->setResponseStatus(new NotFoundResponse('Host'));
                return;
            }
            $hostgroups = $this->hostgroupRepository->findAllByHostId($hostId);
        } else {
            $accessGroups = $this->accessGroupRepository->findByContact($this->contact);
            $accessGroupIds = array_map(
                fn($accessGroup) => $accessGroup->getId(),
                $accessGroups
            );
            $host = $this->repository->findHostByIdAndAccessGroupIds($hostId, $accessGroupIds);
            if ($host === null) {
                $this->critical(
                    "Host not found",
                    [
                        'id' => $hostId,
                        'userId' => $this->contact->getId()
                    ]
                );
                $presenter->setResponseStatus(new NotFoundResponse('Host'));
                return;
            }
            $hostgroups = $this->hostgroupRepository->findAllByHostIdAndAccessGroupIds($hostId, $accessGroupIds);
        }

        foreach ($hostgroups as $hostgroup) {
            $host->addHostgroup($hostgroup);
        }

        /**
         * Obfuscate the passwords in Host commandLine
         * @todo Re-write this code when monitoring repository will be migrated to new architecture
         */
        $host->setCommandLine($this->obfuscatePasswordInHostCommandLine($host));

        $presenter->present(
            $this->createResponse(
                $host,
                $this->downtimeRepository->findOnGoingDowntimesByHostId($hostId),
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
        $findHostResponse = new FindHostResponse(
            $host->getId(),
            $host->getName(),
            $host->getAddress(),
            $host->getMonitoringServerName(),
            $host->getStatus(),
            $host->getIcon(),
            $host->getHostgroups(),
            $downtimes,
            $acknowledgement
        );

        $findHostResponse->timezone = $host->getTimezone();
        $findHostResponse->alias = $host->getAlias();
        $findHostResponse->isFlapping = $host->isFlapping();
        $findHostResponse->isAcknowledged = $host->isAcknowledged();
        $findHostResponse->isInDowntime = $host->isInDowntime();
        $findHostResponse->output = $host->getOutput();
        $findHostResponse->performanceData = $host->getPerformanceData();
        $findHostResponse->commandLine = $host->getCommandLine();
        $findHostResponse->notificationNumber = $host->getNotificationNumber();
        $findHostResponse->lastStatusChange = $host->getLastStatusChange();
        $findHostResponse->lastNotification = $host->getLastNotification();
        $findHostResponse->latency = $host->getLatency();
        $findHostResponse->executionTime = $host->getExecutionTime();
        $findHostResponse->statusChangePercentage = $host->getStatusChangePercentage();
        $findHostResponse->nextCheck = $host->getNextCheck();
        $findHostResponse->lastCheck = $host->getLastCheck();
        $findHostResponse->hasPassiveChecks = $host->hasPassiveChecks();
        $findHostResponse->hasActiveChecks = $host->hasActiveChecks();
        $findHostResponse->lastTimeUp = $host->getLastTimeUp();
        $findHostResponse->severityLevel = $host->getSeverityLevel();
        $findHostResponse->checkAttempts = $host->getCheckAttempts();
        $findHostResponse->maxCheckAttempts = $host->getMaxCheckAttempts();

        return $findHostResponse;
    }

    /**
     * Offuscate passwords in the commandline
     *
     * @param Host $host
     * @return string|null
     */
    private function obfuscatePasswordInHostCommandLine(Host $host): ?string
    {
        $obfuscatedCommandLine = null;

        /**
         * Check if user can see the commandLine.
         * If so, then hide potential passwords.
         */
        if (
            $this->contact->isAdmin()
            || $this->contact->hasRole(Contact::ROLE_DISPLAY_COMMAND)
            || $host->getCommandLine() !== null
        ) {
            try {
                $legacyHost = (new LegacyHost())
                    ->setId($host->getId())
                    ->setCheckCommand($host->getCommandLine());

                $this->monitoringService->hidePasswordInHostCommandLine($legacyHost);
                $obfuscatedCommandLine = $legacyHost->getCheckCommand();
            } catch (\Throwable $ex) {
                $this->debug(
                    "Failed to hide password in host command line",
                    [
                        'id' => $host->getId(),
                        'reason' => $ex->getMessage()
                    ]
                );
                $obfuscatedCommandLine = sprintf(
                    _('Unable to hide passwords in command (Reason: %s)'),
                    $ex->getMessage()
                );
            }
        }

        return $obfuscatedCommandLine;
    }
}
