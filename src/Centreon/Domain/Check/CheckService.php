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

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Monitoring\ResourceService;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Exception\EntityNotFoundException;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Check\Interfaces\CheckServiceInterface;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;

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
    public function filterByContact($contact): CheckServiceInterface
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

        $host = $this->monitoringRepository->findOneHost($check->getResourceId());
        if (is_null($host)) {
            throw new EntityNotFoundException(_('Host not found'));
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

        $host = $this->monitoringRepository->findOneHost($check->getParentResourceId());
        if (is_null($host)) {
            throw new EntityNotFoundException(_('Host not found'));
        }

        $service = $this->monitoringRepository->findOneService($check->getParentResourceId(), $check->getResourceId());
        if (is_null($service)) {
            throw new EntityNotFoundException(_('Service not found'));
        }
        $service->setHost($host);

        $this->engineService->scheduleServiceCheck($check, $service);
    }

    /**
     * @inheritDoc
     */
    public function checkMetaService(Check $check): void
    {
        // We validate the check instance
        $errors = $this->validator->validate(
            $check,
            null,
            Check::VALIDATION_GROUPS_META_SERVICE_CHECK
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $metaServiceDescription = 'meta_' . $check->getResourceId();

        $service = $this->monitoringRepository->findOneServiceByDescription($metaServiceDescription);
        if (is_null($service)) {
            throw new EntityNotFoundException(_('Meta service not found'));
        }

        $host = $this->monitoringRepository->findOneHost($service->getHost()->getId());
        if (is_null($host)) {
            throw new EntityNotFoundException(_('Host not found'));
        }
        $service->setHost($host);

        $this->engineService->scheduleServiceCheck($check, $service);
    }

    /**
     * @inheritDoc
     */
    public function checkResource(Check $check, MonitoringResource $monitoringResource): void
    {
        switch ($monitoringResource->getType()) {
            case MonitoringResource::TYPE_HOST:
                $host = $this->monitoringRepository->findOneHost($monitoringResource->getId());
                if ($host === null) {
                    throw new EntityNotFoundException(
                        sprintf(
                            _('Host %d not found'),
                            $monitoringResource->getId()
                        )
                    );
                }
                $this->engineService->scheduleHostCheck($check, $host);
                break;
            case MonitoringResource::TYPE_SERVICE:
                if ($monitoringResource->getParent() === null) {
                    throw new EntityNotFoundException(
                        sprintf(
                            _('Host not found for service %d'),
                            $monitoringResource->getId()
                        )
                    );
                }
                $host = $this->monitoringRepository->findOneHost($monitoringResource->getParent()->getId());
                $service = $this->monitoringRepository->findOneService(
                    $monitoringResource->getParent()->getId(),
                    $monitoringResource->getId()
                );
                if ($service === null) {
                    throw new EntityNotFoundException(
                        sprintf(
                            _('Service %d (parent: %d) not found'),
                            $monitoringResource->getId(),
                            $monitoringResource->getParent()->getId()
                        )
                    );
                }
                $service->setHost($host);
                $this->engineService->scheduleServiceCheck($check, $service);
                break;
            case MonitoringResource::TYPE_META:
                $service = $this->monitoringRepository->findOneServiceByDescription('meta_' . $monitoringResource->getId());
                if ($service === null) {
                    throw new EntityNotFoundException(
                        sprintf(
                            _('Service %d not found'),
                            $monitoringResource->getId()
                        )
                    );
                }
                $host = $this->monitoringRepository->findOneHost($service->getHost()->getId());
                $service->setHost($host);
                $this->engineService->scheduleServiceCheck($check, $service);
                break;
            default:
                throw new \InvalidArgumentException(sprintf(_('Incorrect Resource type: %s'), $monitoringResource->getType()));
        }
    }
}
