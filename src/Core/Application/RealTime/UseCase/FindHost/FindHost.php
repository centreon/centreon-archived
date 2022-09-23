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
use Core\Tag\RealTime\Domain\Model\Tag;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Severity\RealTime\Domain\Model\Severity;
use Centreon\Domain\Monitoring\Host as LegacyHost;
use Core\Application\Common\UseCase\NotFoundResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface;
use Core\Tag\RealTime\Application\Repository\ReadTagRepositoryInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Application\RealTime\Repository\ReadHostgroupRepositoryInterface;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;
use Core\Severity\RealTime\Application\Repository\ReadSeverityRepositoryInterface;

class FindHost
{
    use LoggerTrait;

    /**
     * @param ReadHostRepositoryInterface $repository
     * @param ReadHostgroupRepositoryInterface $hostgroupRepository
     * @param ContactInterface $contact
     * @param ReadAccessGroupRepositoryInterface $accessGroupRepository
     * @param ReadDowntimeRepositoryInterface $downtimeRepository
     * @param ReadAcknowledgementRepositoryInterface $acknowledgementRepository
     * @param MonitoringServiceInterface $monitoringService
     * @param ReadTagRepositoryInterface $tagRepository
     * @param ReadSeverityRepositoryInterface $severityRepository
     */
    public function __construct(
        private ReadHostRepositoryInterface $repository,
        private ReadHostgroupRepositoryInterface $hostgroupRepository,
        private ContactInterface $contact,
        private ReadAccessGroupRepositoryInterface $accessGroupRepository,
        private ReadDowntimeRepositoryInterface $downtimeRepository,
        private ReadAcknowledgementRepositoryInterface $acknowledgementRepository,
        private MonitoringServiceInterface $monitoringService,
        private ReadTagRepositoryInterface $tagRepository,
        private ReadSeverityRepositoryInterface $severityRepository
    ) {
    }

    /**
     * @param int $hostId
     * @param FindHostPresenterInterface $presenter
     * @return void
     */
    public function __invoke(int $hostId, FindHostPresenterInterface $presenter): void
    {
        $this->info('Searching details for host', ['id' => $hostId]);

        if ($this->contact->isAdmin()) {
            $host = $this->repository->findHostById($hostId);
            if ($host === null) {
                $this->handleHostNotFound($hostId, $presenter);
                return;
            }
            $hostGroups = $this->hostgroupRepository->findAllByHostId($hostId);
        } else {
            $accessGroups = $this->accessGroupRepository->findByContact($this->contact);
            $accessGroupIds = array_map(
                fn($accessGroup) => $accessGroup->getId(),
                $accessGroups
            );
            $host = $this->repository->findHostByIdAndAccessGroupIds($hostId, $accessGroupIds);
            if ($host === null) {
                $this->handleHostNotFound($hostId, $presenter);
                return;
            }
            $hostGroups = $this->hostgroupRepository->findAllByHostIdAndAccessGroupIds($hostId, $accessGroupIds);
        }

        $host->setGroups($hostGroups);

        $categories = $this->tagRepository->findAllByResourceAndTypeId($host->getId(), 0, Tag::HOST_CATEGORY_TYPE_ID);

        $host->setCategories($categories);

        $this->info(
            'Fetching severity from the database for host',
            [
                'hostId' => $hostId,
                'typeId' => Severity::HOST_SEVERITY_TYPE_ID
            ]
        );

        $severity = $this->severityRepository->findByResourceAndTypeId(
            $hostId,
            0,
            Severity::HOST_SEVERITY_TYPE_ID
        );

        $host->setSeverity($severity);

        $acknowledgement = $host->isAcknowledged() === true
            ? $this->acknowledgementRepository->findOnGoingAcknowledgementByHostId($hostId)
            : null;

        $downtimes = $host->isInDowntime() === true
            ? $this->downtimeRepository->findOnGoingDowntimesByHostId($hostId)
            : [];

        /**
         * Obfuscate the passwords in Host commandLine
         * @todo Re-write this code when monitoring repository will be migrated to new architecture
         */
        $host->setCommandLine($this->obfuscatePasswordInHostCommandLine($host));

        $presenter->present(
            $this->createResponse(
                $host,
                $downtimes,
                $acknowledgement
            )
        );
    }

    /**
     * Handle Host not found. This method will log the error and set the ResponseStatus
     *
     * @param int $hostId
     * @param FindHostPresenterInterface $presenter
     * @return void
     */
    private function handleHostNotFound(int $hostId, FindHostPresenterInterface $presenter): void
    {
        $this->error('Host not found', ['id' => $hostId, 'userId' => $this->contact->getId()]);
        $presenter->setResponseStatus(new NotFoundResponse('Host'));
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
            $host->getGroups(),
            $downtimes,
            $acknowledgement,
            $host->getCategories(),
            $host->getSeverity()
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
                $errorMsg = 'Failed to hide password in host command line';
                $this->debug($errorMsg, ['id' => $host->getId(), 'reason' => $ex->getMessage()]);
                $obfuscatedCommandLine = sprintf(
                    _('Unable to hide passwords in command (Reason: %s)'),
                    $ex->getMessage()
                );
            }
        }

        return $obfuscatedCommandLine;
    }
}
