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

namespace Centreon\Domain\Acknowledgement;

use Centreon\Domain\Acknowledgement\Interfaces\AcknowledgementRepositoryInterface;
use Centreon\Domain\Acknowledgement\Interfaces\AcknowledgementServiceInterface;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;
use JMS\Serializer\Exception\ValidationFailedException;

class AcknowledgementService extends AbstractCentreonService implements AcknowledgementServiceInterface
{
    public const VALIDATION_GROUPS_ADD_HOST_ACK = ['add_host_ack'];
    public const VALIDATION_GROUPS_ADD_SERVICE_ACK = ['add_service_ack'];

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

        $host = $this->monitoringRepository->findOneHost($acknowledgement->getHostId());
        if (is_null($host)) {
            throw new AcknowledgementException('Host of acknowledgement not found');
        }

        $this->engineService->addHostAcknowledgement($acknowledgement, $host);
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
            $acknowledgement->getHostId(),
            $acknowledgement->getServiceId()
        );
        if (is_null($service)) {
            throw new AcknowledgementException('Service of acknowledgement not found');
        }

        $host = $this->monitoringRepository->findOneHost(
            $acknowledgement->getHostId()
        );
        if (is_null($host)) {
            throw new AcknowledgementException('Host of acknowledgement not found');
        }
        $service->setHost($host);

        $this->engineService->addServiceAcknowledgement($acknowledgement, $service);
    }

    /**
     * @inheritDoc
     */
    public function findLastHostsAcknowledgements(): array
    {
        return $this->acknowledgementRepository->findLatestAcknowledgementOfAllHosts();
    }

    /**
     * @inheritDoc
     */
    public function findLastServicesAcknowledgements(): array
    {
        return $this->acknowledgementRepository->findLatestAcknowledgementOfAllServices();
    }

    /**
     * @inheritDoc
     */
    public function disacknowledgeHostAcknowledgement(int $hostId): void
    {
        $host = $this->monitoringRepository->findOneHost($hostId);
        if (is_null($host)) {
            throw new EntityNotFoundException('Host not found');
        }
        $acknowledgement = $this->acknowledgementRepository->findLatestHostAcknowledgement($hostId);
        if (is_null($acknowledgement)) {
            throw new AcknowledgementException('No acknowledgement found for this host');
        }
        if (!is_null($acknowledgement->getDeletionTime())) {
            throw new AcknowledgementException('Acknowledgement already cancelled for this host');
        }
        $this->engineService->disacknowledgeHost($host);
    }

    /**
     * @inheritDoc
     */
    public function disacknowledgeServiceAcknowledgement(int $hostId, int $serviceId): void
    {
        $service = $this->monitoringRepository->findOneService($hostId, $serviceId);
        if (is_null($service)) {
            throw new EntityNotFoundException('Service not found');
        }
        $service->setHost(
            $this->monitoringRepository->findOneHost($hostId)
        );
        $acknowledgement = $this->acknowledgementRepository->findLatestServiceAcknowledgement(
            $hostId,
            $serviceId
        );
        if (is_null($acknowledgement)) {
            throw new AcknowledgementException('No acknowledgement found for this service');
        }
        if (!is_null($acknowledgement->getDeletionTime())) {
            throw new AcknowledgementException('Acknowledgement already cancelled for this service');
        }
        $this->engineService->disacknowledgeService($service);
    }
}
