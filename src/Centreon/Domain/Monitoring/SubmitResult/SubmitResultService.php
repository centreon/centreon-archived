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

namespace Centreon\Domain\Monitoring\SubmitResult;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Exception\EntityNotFoundException;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Monitoring\SubmitResult\SubmitResult;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\SubmitResult\Interfaces\SubmitResultServiceInterface;

/**
 * Monitoring class used to manage result submitting to services, hosts and resources
 *
 * @package Centreon\Domain\Monitoring\SubmitResult
 */
class SubmitResultService extends AbstractCentreonService implements SubmitResultServiceInterface
{
    public const VALIDATION_GROUPS_HOST_SUBMIT_RESULT = ['submit_result_host'];
    public const VALIDATION_GROUPS_SERVICE_SUBMIT_RESULT = ['submit_result_service'];

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
     * @var ReadAccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * SubmitResultService constructor.
     *
     * @param ReadAccessGroupRepositoryInterface $accessGroupRepository
     * @param MonitoringRepositoryInterface $monitoringRepository
     * @param EngineServiceInterface $engineService
     * @param EntityValidator $validator
     */
    public function __construct(
        ReadAccessGroupRepositoryInterface $accessGroupRepository,
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
     * @return SubmitResultServiceInterface
     */
    public function filterByContact($contact): SubmitResultServiceInterface
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
    public function submitServiceResult(SubmitResult $result): void
    {
        // We validate the check instance
        $errors = $this->validator->validate(
            $result,
            null,
            self::VALIDATION_GROUPS_SERVICE_SUBMIT_RESULT
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $host = $this->monitoringRepository->findOneHost($result->getParentResourceId());
        if (is_null($host)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Host %d not found'),
                    $result->getParentResourceId()
                )
            );
        }

        $service = $this->monitoringRepository->findOneService(
            $result->getParentResourceId(),
            $result->getResourceId()
        );
        if (is_null($service)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Service %d not found'),
                    $result->getResourceId()
                )
            );
        }
        $service->setHost($host);

        $this->engineService->submitServiceResult($result, $service);
    }

    /**
     * @inheritDoc
     */
    public function submitMetaServiceResult(SubmitResult $result): void
    {
        // We validate the check instance
        $errors = $this->validator->validate(
            $result,
            null,
            self::VALIDATION_GROUPS_SERVICE_SUBMIT_RESULT
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $service = $this->monitoringRepository->findOneServiceByDescription('meta_' . $result->getResourceId());

        if (is_null($service)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Meta Service %d not found'),
                    $result->getResourceId()
                )
            );
        }

        $host = $this->monitoringRepository->findOneHost($service->getHost()->getId());

        if (is_null($host)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Meta Host %d not found'),
                    $service->getHost()->getId()
                )
            );
        }

        $service->setHost($host);

        $this->engineService->submitServiceResult($result, $service);
    }

    /**
     * @inheritDoc
     */
    public function submitHostResult(SubmitResult $result): void
    {
        // We validate the SubmitResult instance instance
        $errors = $this->validator->validate(
            $result,
            null,
            self::VALIDATION_GROUPS_HOST_SUBMIT_RESULT
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $host = $this->monitoringRepository->findOneHost($result->getResourceId());
        if (is_null($host)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Host %d not found'),
                    $result->getResourceId()
                )
            );
        }

        $this->engineService->submitHostResult($result, $host);
    }
}
