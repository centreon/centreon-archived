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

namespace Centreon\Domain\Acknowledgement\Interfaces;

use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Acknowledgement\AcknowledgementException;
use Centreon\Domain\Contact\Interfaces\ContactFilterInterface;
use Centreon\Domain\Engine\EngineException;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Infrastructure\RequestParameters\RequestParametersTranslatorException;
use JMS\Serializer\Exception\ValidationFailedException;

interface AcknowledgementServiceInterface extends ContactFilterInterface
{
    /**
     * Find one acknowledgement.
     *
     * @param int $acknowledgementId Acknowledgement id
     * @return Acknowledgement|null Return NULL if the acknowledgement has not been found
     * @throws \Exception
     */
    public function findOneAcknowledgement(int $acknowledgementId): ?Acknowledgement;

    /**
     * Find all acknowledgements.
     *
     * @return Acknowledgement[]
     * @throws \Exception
     */
    public function findAcknowledgements(): array;

    /**
     * Find all acknowledgements of all hosts.
     *
     * @return Acknowledgement[]
     * @throws RequestParametersTranslatorException
     * @throws \Exception
     */
    public function findHostsAcknowledgements(): array;

    /**
     * Find all acknowledgements of all services.
     *
     * @return Acknowledgement[]
     * @throws RequestParametersTranslatorException
     * @throws \Exception
     */
    public function findServicesAcknowledgements(): array;

    /**
     * Find all acknowledgements by host id.
     *
     * @param int $hostId
     * @return Acknowledgement[]
     * @throws RequestParametersTranslatorException
     * @throws \Exception
     */
    public function findAcknowledgementsByHost(int $hostId): array;

    /**
     * Find all acknowledgements by host id and service id.
     *
     * @param int $hostId
     * @param int $serviceId
     * @return Acknowledgement[]
     * @throws RequestParametersTranslatorException
     * @throws \Exception
     */
    public function findAcknowledgementsByService(int $hostId, int $serviceId): array;

    /**
     * Find all acknowledgements by metaservice id.
     *
     * @param int $metaId
     * @return Acknowledgement[]
     * @throws RequestParametersTranslatorException
     * @throws \Exception
     */
    public function findAcknowledgementsByMetaService(int $metaId): array;

    /**
     * Adds a host acknowledgement.
     *
     * @param Acknowledgement $acknowledgement Host acknowledgement to add
     * @throws AcknowledgementException
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws \Exception
     * @throws ValidationFailedException
     */
    public function addHostAcknowledgement(Acknowledgement $acknowledgement): void;

    /**
     * Adds a service acknowledgement.
     *
     * @param Acknowledgement $acknowledgement Host acknowledgement to add
     * @throws AcknowledgementException
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws \Exception
     * @throws ValidationFailedException
     */
    public function addServiceAcknowledgement(Acknowledgement $acknowledgement): void;

    /**
     * Adds a Meta service acknowledgement.
     *
     * @param Acknowledgement $acknowledgement Meta service acknowledgement to add
     * @throws AcknowledgementException
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws \Exception
     * @throws ValidationFailedException
     */
    public function addMetaServiceAcknowledgement(Acknowledgement $acknowledgement): void;

    /**
     * Disacknowledge a host.
     *
     * @param int $hostId Host id of acknowledgement to be cancelled
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    public function disacknowledgeHost(int $hostId): void;

    /**
     * Disacknowledge a service.
     *
     * @param int $hostId Host id linked to the service
     * @param int $serviceId Service id of acknowledgement to be cancelled
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    public function disacknowledgeService(int $hostId, int $serviceId): void;

    /**
     * Disacknowledge a metaservice.
     *
     * @param int $metaId ID of the metaservice
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    public function disacknowledgeMetaService(int $metaId): void;

    /**
     * Acknowledge resource and its services if needed.
     *
     * @param ResourceEntity $resource Resource to be acknowledged
     * @param Acknowledgement $ack
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    public function acknowledgeResource(ResourceEntity $resource, Acknowledgement $ack): void;

    /**
     * Discknowledge resource and its services if needed.
     *
     * @param ResourceEntity $resource Resource to be acknowledged
     * @param Acknowledgement $ack
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    public function disacknowledgeResource(ResourceEntity $resource, Acknowledgement $ack): void;
}
