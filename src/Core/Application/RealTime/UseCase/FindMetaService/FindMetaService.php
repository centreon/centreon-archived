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

namespace Core\Application\RealTime\UseCase\FindMetaService;

use Centreon\Domain\Log\LoggerTrait;
use Core\Domain\RealTime\Model\Downtime;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;
use Core\Domain\RealTime\Model\MetaService;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Application\Common\UseCase\NotFoundResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Application\RealTime\Repository\ReadMetaServiceRepositoryInterface;
use Core\Domain\Configuration\Model\MetaService as MetaServiceConfiguration;
use Core\Application\RealTime\UseCase\FindMetaService\FindMetaServiceResponse;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;
use Core\Application\RealTime\UseCase\FindMetaService\FindMetaServicePresenterInterface;
use Core\Application\Configuration\MetaService\Repository\ReadMetaServiceRepositoryInterface as
    ReadMetaServiceConfigurationRepositoryInterface;

class FindMetaService
{
    use LoggerTrait;

    /**
     * @param ReadMetaServiceRepositoryInterface $repository
     * @param ReadMetaServiceConfigurationRepositoryInterface $configurationRepository
     * @param ContactInterface $contact
     * @param ReadAccessGroupRepositoryInterface $accessGroupRepository
     * @param ReadDowntimeRepositoryInterface $downtimeRepository
     * @param ReadAcknowledgementRepositoryInterface $acknowledgementRepository
     */
    public function __construct(
        private ReadMetaServiceRepositoryInterface $repository,
        private ReadMetaServiceConfigurationRepositoryInterface $configurationRepository,
        private ContactInterface $contact,
        private ReadAccessGroupRepositoryInterface $accessGroupRepository,
        private ReadDowntimeRepositoryInterface $downtimeRepository,
        private ReadAcknowledgementRepositoryInterface $acknowledgementRepository,
    ) {
    }

    /**
     * @param int $metaId
     * @param FindMetaServicePresenterInterface $presenter
     * @return void
     */
    public function __invoke(
        int $metaId,
        FindMetaServicePresenterInterface $presenter
    ): void {
        $this->info(
            "Searching details for Meta Service",
            [
                "id" => $metaId
            ]
        );

        if ($this->contact->isAdmin()) {
            $this->debug('Find MetaService as an admin user');

            $metaServiceConfiguration = $this->configurationRepository->findMetaServiceById($metaId);
            if ($metaServiceConfiguration === null) {
                $this->handleMetaServiceConfigurationNotFound($metaId, $presenter);
                return;
            }

            $metaService = $this->repository->findMetaServiceById($metaId);
            if ($metaService === null) {
                $this->handleMetaServiceNotFound($metaId, $presenter);
                return;
            }
        } else {
            $this->debug('Find MetaService as an non-admin user');
            $accessGroups = $this->accessGroupRepository->findByContact($this->contact);
            $accessGroupIds = array_map(
                fn (AccessGroup $accessGroup) => $accessGroup->getId(),
                $accessGroups
            );

            $metaServiceConfiguration = $this->configurationRepository->findMetaServiceByIdAndAccessGroupIds(
                $metaId,
                $accessGroupIds
            );
            if ($metaServiceConfiguration === null) {
                $this->handleMetaServiceConfigurationNotFound($metaId, $presenter);
                return;
            }
            $metaService = $this->repository->findMetaServiceByIdAndAccessGroupIds(
                $metaId,
                $accessGroupIds
            );
            if ($metaService === null) {
                $this->handleMetaServiceNotFound($metaId, $presenter);
                return;
            }
        }

        $hostId = $metaService->getHostId();
        $serviceId = $metaService->getServiceId();

        $acknowledgement = $metaService->isAcknowledged() === true
            ? $this->acknowledgementRepository->findOnGoingAcknowledgementByHostIdAndServiceId($hostId, $serviceId)
            : null;

        $downtimes = $metaService->isInDowntime() === true
            ? $this->downtimeRepository->findOnGoingDowntimesByHostIdAndServiceId($hostId, $serviceId)
            : [];

        $presenter->present(
            $this->createResponse(
                $metaService,
                $metaServiceConfiguration,
                $downtimes,
                $acknowledgement,
            )
        );
    }

    /**
     * Handle Meta Service Configuration not found. This method will log the error and set the ResponseStatus
     *
     * @param int $metaId
     * @param FindMetaServicePresenterInterface $presenter
     * @return void
     */
    private function handleMetaServiceConfigurationNotFound(
        int $metaId,
        FindMetaServicePresenterInterface $presenter
    ): void {
        $this->error(
            "Meta Service configuration not found",
            [
                'id' => $metaId,
                'userId' => $this->contact->getId()
            ]
        );
        $presenter->setResponseStatus(new NotFoundResponse('MetaService configuration'));
    }

    /**
     * Handle Meta Service not found. This method will log the error and set the ResponseStatus
     *
     * @param int $metaId
     * @param FindMetaServicePresenterInterface $presenter
     * @return void
     */
    private function handleMetaServiceNotFound(
        int $metaId,
        FindMetaServicePresenterInterface $presenter
    ): void {
        $this->error(
            "Meta Service not found",
            [
                'id' => $metaId,
                'userId' => $this->contact->getId()
            ]
        );
        $presenter->setResponseStatus(new NotFoundResponse('MetaService'));
    }

    /**
     * @param MetaService $metaService
     * @param MetaServiceConfiguration $metaServiceConfiguration
     * @param Downtime[] $downtimes
     * @param Acknowledgement|null $acknowledgement
     * @return FindMetaServiceResponse
     */
    public function createResponse(
        MetaService $metaService,
        MetaServiceConfiguration $metaServiceConfiguration,
        array $downtimes,
        ?Acknowledgement $acknowledgement
    ): FindMetaServiceResponse {
        $findMetaServiceResponse = new FindMetaServiceResponse(
            $metaService->getId(),
            $metaService->getHostId(),
            $metaService->getServiceId(),
            $metaService->getName(),
            $metaService->getMonitoringServerName(),
            $metaService->getStatus(),
            $metaServiceConfiguration->getCalculationType(),
            $downtimes,
            $acknowledgement
        );

        $findMetaServiceResponse->isFlapping = $metaService->isFlapping();
        $findMetaServiceResponse->isAcknowledged = $metaService->isAcknowledged();
        $findMetaServiceResponse->isInDowntime = $metaService->isInDowntime();
        $findMetaServiceResponse->output = $metaService->getOutput();
        $findMetaServiceResponse->performanceData = $metaService->getPerformanceData();
        $findMetaServiceResponse->commandLine = $metaService->getCommandLine();
        $findMetaServiceResponse->notificationNumber = $metaService->getNotificationNumber();
        $findMetaServiceResponse->lastStatusChange = $metaService->getLastStatusChange();
        $findMetaServiceResponse->lastNotification = $metaService->getLastNotification();
        $findMetaServiceResponse->latency = $metaService->getLatency();
        $findMetaServiceResponse->executionTime = $metaService->getExecutionTime();
        $findMetaServiceResponse->statusChangePercentage = $metaService->getStatusChangePercentage();
        $findMetaServiceResponse->nextCheck = $metaService->getNextCheck();
        $findMetaServiceResponse->lastCheck = $metaService->getLastCheck();
        $findMetaServiceResponse->hasPassiveChecks = $metaService->hasPassiveChecks();
        $findMetaServiceResponse->hasActiveChecks = $metaService->hasActiveChecks();
        $findMetaServiceResponse->lastTimeOk = $metaService->getLastTimeOk();
        $findMetaServiceResponse->checkAttempts = $metaService->getCheckAttempts();
        $findMetaServiceResponse->maxCheckAttempts = $metaService->getMaxCheckAttempts();
        $findMetaServiceResponse->hasGraphData = $metaService->hasGraphData();

        return $findMetaServiceResponse;
    }
}
