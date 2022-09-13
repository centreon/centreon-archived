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
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Monitoring\ResourceService;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Exception\EntityNotFoundException;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Check\Interfaces\CheckServiceInterface;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
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
     * @var ReadAccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * CheckService constructor.
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
    public function checkResource(Check $check, ResourceEntity $resource): void
    {
        switch ($resource->getType()) {
            case ResourceEntity::TYPE_HOST:
                $host = $this->monitoringRepository->findOneHost(ResourceService::generateHostIdByResource($resource));
                if (is_null($host)) {
                    throw new EntityNotFoundException(
                        sprintf(
                            _('Host %d not found'),
                            $resource->getId()
                        )
                    );
                }
                $this->engineService->scheduleHostCheck($check, $host);
                break;
            case ResourceEntity::TYPE_SERVICE:
                $host = $this->monitoringRepository->findOneHost(ResourceService::generateHostIdByResource($resource));
                $service = $this->monitoringRepository->findOneService(
                    $resource->getParent()->getId(),
                    $resource->getId()
                );
                if (is_null($service)) {
                    throw new EntityNotFoundException(
                        sprintf(
                            _('Service %d (parent: %d) not found'),
                            $resource->getId(),
                            $resource->getParent()->getId()
                        )
                    );
                }
                $service->setHost($host);
                $this->engineService->scheduleServiceCheck($check, $service);
                break;
            case ResourceEntity::TYPE_META:
                $service = $this->monitoringRepository->findOneServiceByDescription('meta_' . $resource->getId());
                if (is_null($service)) {
                    throw new EntityNotFoundException(
                        sprintf(
                            _('Service %d (parent: %d) not found'),
                            $resource->getId(),
                            $resource->getParent()->getId()
                        )
                    );
                }
                $host = $this->monitoringRepository->findOneHost($service->getHost()->getId());
                $service->setHost($host);
                $this->engineService->scheduleServiceCheck($check, $service);
                break;
            default:
                throw new \InvalidArgumentException(sprintf(_('Incorrect Resource type: %s'), $resource->getType()));
        }
    }
}
