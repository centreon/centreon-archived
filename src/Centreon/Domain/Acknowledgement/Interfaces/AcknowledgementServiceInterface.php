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
use Centreon\Infrastructure\RequestParameters\RequestParametersTranslatorException;
use JMS\Serializer\Exception\ValidationFailedException;

interface AcknowledgementServiceInterface extends ContactFilterInterface
{
    /**
     * Find all acknowledgements of all hosts.
     *
     * @return Acknowledgement[]
     * @throws RequestParametersTranslatorException
     * @throws \Exception
     */
    public function findLastHostsAcknowledgements(): array;

    /**
     * Find all acknowledgements of all services.
     *
     * @return Acknowledgement[]
     * @throws RequestParametersTranslatorException
     * @throws \Exception
     */
    public function findLastServicesAcknowledgements(): array;

    /**
     * Adds a host acknowledgement.
     *
     * @param Acknowledgement $acknowledgement Host acknowledgment to add
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
     * @param Acknowledgement $acknowledgement Host acknowledgment to add
     * @throws AcknowledgementException
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws \Exception
     * @throws ValidationFailedException
     */
    public function addServiceAcknowledgement(Acknowledgement $acknowledgement): void;

    /**
     * Disacknowledge a host acknowledgement.
     *
     * @param int $hostId Host id of acknowledgement to be cancelled
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    public function disacknowledgeHostAcknowledgement(int $hostId): void;

    /**
     * Disacknowledge a service acknowledgement.
     *
     * @param int $hostId Host id linked to the service
     * @param int $serviceId Service id of acknowledgement to be cancelled
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    public function disacknowledgeServiceAcknowledgement(int $hostId, int $serviceId): void;
}
