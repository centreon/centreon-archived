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

namespace Core\Application\RealTime\UseCase\FindService;

use Centreon\Domain\Log\LoggerTrait;
use Core\Domain\RealTime\Model\Service;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Acknowledgement;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
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
     * @param ReadServicegroupRepositoryInterface $servicegroupRepository
     * @param ReadHostRepositoryInterface $hostRepository
     * @param ContactInterface $contact
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param ReadDowntimeRepositoryInterface $downtimeRepository
     * @param ReadAcknowledgementRepositoryInterface $acknowledgementRepository
     * @param MonitoringServiceInterface $monitoringService
     */
    public function __construct(
        private ReadServiceRepositoryInterface $repository,
        private ReadServicegroupRepositoryInterface $servicegroupRepository,
        private ReadHostRepositoryInterface $hostRepository,
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
        ?Acknowledgement $acknowledgement
    ): FindServiceResponse {
        return new FindServiceResponse();
    }
}
