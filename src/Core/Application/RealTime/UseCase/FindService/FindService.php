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

namespace Core\Application\RealTime\UseCase\FindService;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Log\LoggerTrait;
use Core\Domain\RealTime\Model\Host;
use Core\Domain\RealTime\Model\Service;
use Core\Domain\RealTime\Model\Downtime;
use Centreon\Domain\Security\AccessGroup;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Application\Common\UseCase\NotFoundResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Service as LegacyService;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface;
use Core\Application\RealTime\UseCase\FindService\FindServiceResponse;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadServiceRepositoryInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Application\RealTime\Repository\ReadServicegroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;
use Core\Application\RealTime\UseCase\FindService\FindServicePresenterInterface;

class FindService
{
    use LoggerTrait;

    /**
     * @param ReadServiceRepositoryInterface $repository
     * @param ReadHostRepositoryInterface $hostRepository
     * @param ReadServicegroupRepositoryInterface $servicegroupRepository
     * @param ContactInterface $contact
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param ReadDowntimeRepositoryInterface $downtimeRepository
     * @param ReadAcknowledgementRepositoryInterface $acknowledgementRepository
     * @param MonitoringServiceInterface $monitoringService
     */
    public function __construct(
        private ReadServiceRepositoryInterface $repository,
        private ReadHostRepositoryInterface $hostRepository,
        private ReadServicegroupRepositoryInterface $servicegroupRepository,
        private ContactInterface $contact,
        private AccessGroupRepositoryInterface $accessGroupRepository,
        private ReadDowntimeRepositoryInterface $downtimeRepository,
        private ReadAcknowledgementRepositoryInterface $acknowledgementRepository,
        private MonitoringServiceInterface $monitoringService
    ) {
    }

    /**
     * @param int $hostId
     * @param int $serviceId
     * @param FindServicePresenterInterface $presenter
     */
    public function __invoke(
        int $hostId,
        int $serviceId,
        FindServicePresenterInterface $presenter
    ): void {
        $servicegroups = [];

        $this->info(
            "Searching details for service",
            [
                "id" => $serviceId
            ]
        );

        if ($this->contact->isAdmin()) {
            $host = $this->hostRepository->findHostById($hostId);
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
            $service = $this->repository->findServiceById($hostId, $serviceId);
            if ($service === null) {
                $this->critical(
                    "Service not found",
                    [
                        'id' => $serviceId,
                        'hostId' => $hostId,
                        'userId' => $this->contact->getId()
                    ]
                );
                $presenter->setResponseStatus(new NotFoundResponse('Service'));
                return;
            }

            $servicegroups = $this->servicegroupRepository->findAllByHostIdAndServiceId(
                $hostId,
                $serviceId
            );
        } else {
            $accessGroups = $this->accessGroupRepository->findByContact($this->contact);
            $accessGroupIds = array_map(
                fn (AccessGroup $accessGroup) => $accessGroup->getId(),
                $accessGroups
            );

            $host = $this->hostRepository->findHostByIdAndAccessGroupIds($hostId, $accessGroupIds);

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

            $service = $this->repository->findServiceByIdAndAccessGroupIds($hostId, $serviceId, $accessGroupIds);

            if ($service === null) {
                $this->critical(
                    "Service not found",
                    [
                        'id' => $serviceId,
                        'hostId' => $hostId,
                        'userId' => $this->contact->getId()
                    ]
                );
                $presenter->setResponseStatus(new NotFoundResponse('Service'));
                return;
            }

            $servicegroups = $this->servicegroupRepository->findAllByHostIdAndServiceIdAndAccessGroupIds(
                $hostId,
                $serviceId,
                $accessGroupIds
            );
        }

        foreach ($servicegroups as $servicegroup) {
            $service->addServicegroup($servicegroup);
        }

        /**
         * Obfuscate the passwords in Service commandLine
         */
        $service->setCommandLine($this->obfuscatePasswordInServiceCommandLine($service));

        $presenter->present(
            $this->createResponse(
                $service,
                $this->downtimeRepository->findOnGoingDowntimesByHostIdAndServiceId($hostId, $serviceId),
                $this->acknowledgementRepository->findOnGoingAcknowledgementByHostIdAndServiceId($hostId, $serviceId),
                $host
            )
        );
    }

    /**
     * @param Service $service
     * @param Downtime[] $downtimes
     * @param Acknowledgement|null $acknowledgement
     * @return FindServiceResponse
     */
    public function createResponse(
        Service $service,
        array $downtimes,
        ?Acknowledgement $acknowledgement,
        Host $host
    ): FindServiceResponse {
        $findServiceResponse = new FindServiceResponse(
            $service->getId(),
            $service->getHostId(),
            $service->getName(),
            $service->getStatus(),
            $service->getIcon(),
            $service->getServicegroups(),
            $downtimes,
            $acknowledgement,
            $host
        );

        $findServiceResponse->isFlapping = $service->isFlapping();
        $findServiceResponse->isAcknowledged = $service->isAcknowledged();
        $findServiceResponse->isInDowntime = $service->isInDowntime();
        $findServiceResponse->output = $service->getOutput();
        $findServiceResponse->performanceData = $service->getPerformanceData();
        $findServiceResponse->commandLine = $service->getCommandLine();
        $findServiceResponse->notificationNumber = $service->getNotificationNumber();
        $findServiceResponse->lastStatusChange = $service->getLastStatusChange();
        $findServiceResponse->lastNotification = $service->getLastNotification();
        $findServiceResponse->latency = $service->getLatency();
        $findServiceResponse->executionTime = $service->getExecutionTime();
        $findServiceResponse->statusChangePercentage = $service->getStatusChangePercentage();
        $findServiceResponse->nextCheck = $service->getNextCheck();
        $findServiceResponse->lastCheck = $service->getLastCheck();
        $findServiceResponse->hasPassiveChecks = $service->hasPassiveChecks();
        $findServiceResponse->hasActiveChecks = $service->hasActiveChecks();
        $findServiceResponse->lastTimeOk = $service->getLastTimeOk();
        $findServiceResponse->severityLevel = $service->getSeverityLevel();
        $findServiceResponse->checkAttempts = $service->getCheckAttempts();
        $findServiceResponse->maxCheckAttempts = $service->getMaxCheckAttempts();

        return $findServiceResponse;
    }

    /**
     * Obfuscate passwords in the commandline
     *
     * @param Service $service
     * @return string|null
     */
    private function obfuscatePasswordInServiceCommandLine(Service $service): ?string
    {
        $obfuscatedCommandLine = null;

        /**
         * Check if user can see the commandLine.
         * If so, then hide potential passwords.
         */
        if (
            $this->contact->isAdmin()
            || $this->contact->hasRole(Contact::ROLE_DISPLAY_COMMAND)
            || $service->getCommandLine() !== null
        ) {
            try {
                $legacyService = (new LegacyService())
                    ->setId($service->getId())
                    ->setCheckCommand($service->getCommandLine());

                $this->monitoringService->hidePasswordInServiceCommandLine($legacyService);
                $obfuscatedCommandLine = $legacyService->getCheckCommand();
            } catch (\Throwable $ex) {
                $this->debug(
                    "Failed to hide password in service command line",
                    [
                        'id' => $service->getId(),
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
