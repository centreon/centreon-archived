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

namespace Centreon\Domain\Acknowledgement;

use Centreon\Domain\Acknowledgement\Interfaces\AcknowledgementRepositoryInterface;
use Centreon\Domain\Acknowledgement\Interfaces\AcknowledgementServiceInterface;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Monitoring\ResourceService;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Monitoring\Exception\ResourceException;

class AcknowledgementService extends AbstractCentreonService implements AcknowledgementServiceInterface
{
    // validation groups
    public const VALIDATION_GROUPS_ADD_HOST_ACKS = ['Default', 'add_host_acks'];
    public const VALIDATION_GROUPS_ADD_SERVICE_ACKS = ['Default', 'add_service_acks'];
    public const VALIDATION_GROUPS_ADD_HOST_ACK = ['Default', 'add_host_ack'];
    public const VALIDATION_GROUPS_ADD_SERVICE_ACK = ['Default'];

    /**
     * @var AcknowledgementRepositoryInterface
     */
    private $acknowledgementRepository;

    /**
     * @var EngineServiceInterface All acknowledgement requests except reading use Engine.
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
     * AcknowledgementService constructor.
     *
     * @param AcknowledgementRepositoryInterface $acknowledgementRepository
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param MonitoringRepositoryInterface $monitoringRepository
     * @param EngineServiceInterface $engineService
     * @param EntityValidator $validator
     */
    public function __construct(
        AcknowledgementRepositoryInterface $acknowledgementRepository,
        AccessGroupRepositoryInterface $accessGroupRepository,
        MonitoringRepositoryInterface $monitoringRepository,
        EngineServiceInterface $engineService,
        EntityValidator $validator
    ) {
        $this->accessGroupRepository = $accessGroupRepository;
        $this->acknowledgementRepository = $acknowledgementRepository;
        $this->monitoringRepository = $monitoringRepository;
        $this->engineService = $engineService;
        $this->validator = $validator;
    }

    /**
     * {@inheritDoc}
     * @param Contact $contact
     * @return AcknowledgementServiceInterface
     */
    public function filterByContact($contact): self
    {
        parent::filterByContact($contact);
        $this->engineService->filterByContact($contact);

        $accessGroups = $this->accessGroupRepository->findByContact($contact);

        $this->monitoringRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($accessGroups);

        $this->acknowledgementRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($accessGroups);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findOneAcknowledgement(int $acknowledgementId): ?Acknowledgement
    {
        if ($this->contact->isAdmin()) {
            return $this->acknowledgementRepository->findOneAcknowledgementForAdminUser($acknowledgementId);
        } else {
            return $this->acknowledgementRepository
                ->findOneAcknowledgementForNonAdminUser($acknowledgementId);
        }
    }

    /**
     * @inheritDoc
     */
    public function findAcknowledgements(): array
    {
        if ($this->contact->isAdmin()) {
            return $this->acknowledgementRepository->findAcknowledgementsForAdminUser();
        } else {
            return $this->acknowledgementRepository
                ->findAcknowledgementsForNonAdminUser();
        }
    }

    /**
     * @inheritDoc
     */
    public function addHostAcknowledgement(Acknowledgement $acknowledgement): void
    {
        // We validate the acknowledgement instance
        $errors = $this->validator->validate(
            $acknowledgement,
            null,
            AcknowledgementService::VALIDATION_GROUPS_ADD_HOST_ACK
        );
        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $host = $this->monitoringRepository->findOneHost($acknowledgement->getResourceId());
        if (is_null($host)) {
            throw new EntityNotFoundException(_('Host not found'));
        }

        $this->engineService->addHostAcknowledgement($acknowledgement, $host);

        if ($acknowledgement->isWithServices()) {
            $services = $this->monitoringRepository->findServicesByHostWithoutRequestParameters($host->getId());
            foreach ($services as $service) {
                $service->setHost($host);
                $this->engineService->addServiceAcknowledgement($acknowledgement, $service);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function addServiceAcknowledgement(Acknowledgement $acknowledgement): void
    {
        // We validate the acknowledgement instance
        $errors = $this->validator->validate(
            $acknowledgement,
            null,
            AcknowledgementService::VALIDATION_GROUPS_ADD_SERVICE_ACK
        );
        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $service = $this->monitoringRepository->findOneService(
            $acknowledgement->getParentResourceId(),
            $acknowledgement->getResourceId()
        );
        if (is_null($service)) {
            throw new EntityNotFoundException(_('Service not found'));
        }

        $host = $this->monitoringRepository->findOneHost($acknowledgement->getParentResourceId());
        if (is_null($host)) {
            throw new EntityNotFoundException(_('Host not found'));
        }
        $service->setHost($host);

        $this->engineService->addServiceAcknowledgement($acknowledgement, $service);
    }

    /**
     * @inheritDoc
     */
    public function addMetaServiceAcknowledgement(Acknowledgement $acknowledgement): void
    {
        // We validate the acknowledgement instance
        $errors = $this->validator->validate(
            $acknowledgement,
            null,
            AcknowledgementService::VALIDATION_GROUPS_ADD_SERVICE_ACK
        );
        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $service = $this->monitoringRepository->findOneServiceByDescription(
            'meta_' . $acknowledgement->getResourceId()
        );

        if (is_null($service)) {
            throw new EntityNotFoundException(_('Service not found'));
        }

        $host = $this->monitoringRepository->findOneHost($service->getId());
        if (is_null($host)) {
            throw new EntityNotFoundException(_('Host not found'));
        }
        $service->setHost($host);

        $this->engineService->addServiceAcknowledgement($acknowledgement, $service);
    }

    /**
     * @inheritDoc
     */
    public function findHostsAcknowledgements(): array
    {
        return $this->acknowledgementRepository->findHostsAcknowledgements();
    }

    /**
     * @inheritDoc
     */
    public function findServicesAcknowledgements(): array
    {
        return $this->acknowledgementRepository->findServicesAcknowledgements();
    }

    /**
     * @inheritDoc
     */
    public function findAcknowledgementsByHost(int $hostId): array
    {
        return $this->acknowledgementRepository->findAcknowledgementsByHost($hostId);
    }

    /**
     * @inheritDoc
     */
    public function findAcknowledgementsByService(int $hostId, int $serviceId): array
    {
        return $this->acknowledgementRepository->findAcknowledgementsByService($hostId, $serviceId);
    }

    /**
     * @inheritDoc
     */
    public function findAcknowledgementsByMetaService(int $metaId): array
    {
        $service = $this->monitoringRepository->findOneServiceByDescription('meta_' . $metaId);
        if (is_null($service)) {
            throw new EntityNotFoundException(_('Service not found'));
        }
        return $this->acknowledgementRepository->findAcknowledgementsByService(
            $service->getHost()->getId(),
            $service->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function disacknowledgeHost(int $hostId): void
    {
        $host = $this->monitoringRepository->findOneHost($hostId);
        if (is_null($host)) {
            throw new EntityNotFoundException(_('Host not found'));
        }
        $acknowledgement = $this->acknowledgementRepository->findLatestHostAcknowledgement($hostId);
        if (is_null($acknowledgement)) {
            throw new AcknowledgementException(_('No acknowledgement found for this host'));
        }
        if (!is_null($acknowledgement->getDeletionTime())) {
            throw new AcknowledgementException(_('Acknowledgement already cancelled for this host'));
        }
        $this->engineService->disacknowledgeHost($host);
    }

    /**
     * @inheritDoc
     */
    public function disacknowledgeMetaService(int $metaId): void
    {
        $service = $this->monitoringRepository->findOneServiceByDescription('meta_' . $metaId);
        if (is_null($service)) {
            throw new EntityNotFoundException(_('Meta service not found'));
        }
        $acknowledgement = $this->acknowledgementRepository->findLatestServiceAcknowledgement(
            $service->getHost()->getId(),
            $service->getId()
        );
        if (is_null($acknowledgement)) {
            throw new AcknowledgementException(_('No acknowledgement found for this meta service'));
        }
        if (!is_null($acknowledgement->getDeletionTime())) {
            throw new AcknowledgementException(_('Acknowledgement already cancelled for this meta service'));
        }
        $this->engineService->disacknowledgeService($service);
    }

    /**
     * @inheritDoc
     */
    public function disacknowledgeService(int $hostId, int $serviceId): void
    {
        $service = $this->monitoringRepository->findOneService($hostId, $serviceId);
        if (is_null($service)) {
            throw new EntityNotFoundException(_('Service not found'));
        }
        $service->setHost(
            $this->monitoringRepository->findOneHost($hostId)
        );
        $acknowledgement = $this->acknowledgementRepository->findLatestServiceAcknowledgement(
            $hostId,
            $serviceId
        );
        if (is_null($acknowledgement)) {
            throw new AcknowledgementException(_('No acknowledgement found for this service'));
        }
        if (!is_null($acknowledgement->getDeletionTime())) {
            throw new AcknowledgementException(_('Acknowledgement already cancelled for this service'));
        }
        $this->engineService->disacknowledgeService($service);
    }

    /**
     * @inheritDoc
     */
    public function disacknowledgeResource(ResourceEntity $resource, Acknowledgement $ack): void
    {
        switch ($resource->getType()) {
            case ResourceEntity::TYPE_HOST:
                $host = $this->monitoringRepository->findOneHost(ResourceService::generateHostIdByResource($resource));
                if (is_null($host)) {
                    throw new EntityNotFoundException(_('Host not found'));
                }
                $this->engineService->disacknowledgeHost($host);
                if ($ack->isWithServices()) {
                    $services = $this->monitoringRepository->findServicesByHostWithoutRequestParameters($host->getId());
                    foreach ($services as $service) {
                        $service->setHost($host);
                        $this->engineService->disacknowledgeService($service);
                    }
                }
                break;
            case ResourceEntity::TYPE_SERVICE:
                $host = $this->monitoringRepository->findOneHost(ResourceService::generateHostIdByResource($resource));
                if (is_null($host)) {
                    throw new EntityNotFoundException(_('Host not found'));
                }
                $service = $this->monitoringRepository->findOneService(
                    (int)$resource->getParent()->getId(),
                    (int)$resource->getId()
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
                $this->engineService->disacknowledgeService($service);
                break;
            case ResourceEntity::TYPE_META:
                $service = $this->monitoringRepository->findOneServiceByDescription('meta_' . $resource->getId());
                if (is_null($service)) {
                    throw new EntityNotFoundException(
                        sprintf(
                            _('Meta Service %d not found'),
                            $resource->getId()
                        )
                    );
                }
                $host = $this->monitoringRepository->findOneHost($service->getHost()->getId());
                if (is_null($host)) {
                    throw new EntityNotFoundException(_('Host not found'));
                }
                $service->setHost($host);
                $this->engineService->disacknowledgeService($service);
                break;
            default:
                throw new ResourceException(sprintf(_('Incorrect Resource type: %s'), $resource->getType()));
        }
    }

    /**
     * @inheritDoc
     */
    public function acknowledgeResource(ResourceEntity $resource, Acknowledgement $ack): void
    {
        switch ($resource->getType()) {
            case ResourceEntity::TYPE_HOST:
                $host = $this->monitoringRepository->findOneHost(ResourceService::generateHostIdByResource($resource));
                if (is_null($host)) {
                    throw new EntityNotFoundException(_('Host not found'));
                }
                $this->engineService->addHostAcknowledgement($ack, $host);
                if ($ack->isWithServices()) {
                    $services = $this->monitoringRepository->findServicesByHostWithoutRequestParameters($host->getId());
                    foreach ($services as $service) {
                        $service->setHost($host);
                        $this->engineService->addServiceAcknowledgement($ack, $service);
                    }
                }
                break;
            case ResourceEntity::TYPE_SERVICE:
                $host = $this->monitoringRepository->findOneHost(ResourceService::generateHostIdByResource($resource));
                if (is_null($host)) {
                    throw new EntityNotFoundException(_('Host not found'));
                }
                $service = $this->monitoringRepository->findOneService(
                    (int)$resource->getParent()->getId(),
                    (int)$resource->getId()
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
                $this->engineService->addServiceAcknowledgement($ack, $service);
                break;
            case ResourceEntity::TYPE_META:
                $service = $this->monitoringRepository->findOneServiceByDescription('meta_' . $resource->getId());
                if (is_null($service)) {
                    throw new EntityNotFoundException(
                        sprintf(
                            _('Meta Service %d not found'),
                            $resource->getId(),
                            $resource->getParent()->getId()
                        )
                    );
                }
                $host = $this->monitoringRepository->findOneHost($service->getHost()->getId());
                if (is_null($host)) {
                    throw new EntityNotFoundException(_('Host not found'));
                }
                $service->setHost($host);
                $this->engineService->addServiceAcknowledgement($ack, $service);
                break;
            default:
                throw new ResourceException(sprintf(_('Incorrect Resource type: %s'), $resource->getType()));
        }
    }
}
