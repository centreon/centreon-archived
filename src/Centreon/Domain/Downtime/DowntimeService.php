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
declare(strict_types=1);

namespace Centreon\Domain\Downtime;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Downtime\Interfaces\DowntimeRepositoryInterface;
use Centreon\Domain\Downtime\Interfaces\DowntimeServiceInterface;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;

class DowntimeService extends AbstractCentreonService implements DowntimeServiceInterface
{
    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;
    /**
     * @var EngineServiceInterface All downtime requests except reading use Engine.
     */
    private $engineService;
    /**
     * @var EntityValidator
     */
    private $validator;
    /**
     * @var DowntimeRepositoryInterface
     */
    private $downtimeRepository;
    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    public function __construct(
        AccessGroupRepositoryInterface $accessGroupRepository,
        EngineServiceInterface $engineService,
        EntityValidator $validator,
        DowntimeRepositoryInterface $downtimeRepository,
        MonitoringRepositoryInterface $monitoringRepository
    ) {
        $this->accessGroupRepository = $accessGroupRepository;
        $this->engineService = $engineService;
        $this->validator = $validator;
        $this->downtimeRepository = $downtimeRepository;
        $this->monitoringRepository = $monitoringRepository;
    }

    /**
     * {@inheritDoc}
     * @param Contact $contact
     * @return DowntimeServiceInterface
     */
    public function filterByContact($contact): self
    {
        parent::filterByContact($contact);
        $this->engineService->filterByContact($contact);

        $accessGroups = $this->accessGroupRepository->findByContact($contact);

        $this->monitoringRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($accessGroups);

        $this->downtimeRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($accessGroups);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findHostDowntime(): array
    {
        return $this->downtimeRepository->findHostDowntime();
    }

}
