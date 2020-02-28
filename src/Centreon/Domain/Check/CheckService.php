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
declare(strict_types=1);

namespace Centreon\Domain\Check;

use Centreon\Domain\Check\Interfaces\CheckServiceInterface;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;
use JMS\Serializer\Exception\ValidationFailedException;

class CheckService extends AbstractCentreonService implements CheckServiceInterface
{
    /**
     * @var EngineServiceInterface Used to send external commands to engine.
     */
    private $engineService;

    /**
     * @var EntityValidator
     */
    private $validator;

    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * CheckService constructor.
     *
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param MonitoringRepositoryInterface $monitoringRepository
     * @param EngineServiceInterface $engineService
     * @param EntityValidator $validator
     */
    public function __construct(
        AccessGroupRepositoryInterface $accessGroupRepository,
        MonitoringRepositoryInterface $monitoringRepository,
        EngineServiceInterface $engineService,
        EntityValidator $validator
    ) {
        $this->accessGroupRepository = $accessGroupRepository;
        $this->monitoringRepository = $monitoringRepository;
        $this->engineService = $engineService;
        $this->validator = $validator;
    }

    /**
     * {@inheritDoc}
     * @param Contact $contact
     * @return CheckServiceInterface
     */
    public function filterByContact($contact): self
    {
        parent::filterByContact($contact);
        $this->engineService->filterByContact($contact);

        $accessGroups = $this->accessGroupRepository->findByContact($contact);

        $this->monitoringRepository
            ->setContact($contact)
            ->filterByAccessGroups($accessGroups);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function checkHost(Check $check): void
    {
        // We validate the check instance
        $errors = $this->validator->validate(
            $check,
            null,
            Check::VALIDATION_GROUPS_HOST_CHECK
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $host = $this->monitoringRepository->findOneHost($check->getHostId());
        if (is_null($host)) {
            throw new EntityNotFoundException('Host not found');
        }

        $this->engineService->scheduleHostCheck($check, $host);
    }

    /**
     * @inheritDoc
     */
    public function checkService(Check $check): void
    {
        // We validate the check instance
        $errors = $this->validator->validate(
            $check,
            null,
            Check::VALIDATION_GROUPS_SERVICE_CHECK
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $host = $this->monitoringRepository->findOneHost($check->getHostId());
        if (is_null($host)) {
            throw new EntityNotFoundException('Host not found');
        }

        $service = $this->monitoringRepository->findOneService($check->getHostId(), $check->getServiceId());
        if (is_null($service)) {
            throw new EntityNotFoundException('Service not found');
        }
        $service->setHost($host);

        $this->engineService->scheduleServiceCheck($check, $service);
    }
}
