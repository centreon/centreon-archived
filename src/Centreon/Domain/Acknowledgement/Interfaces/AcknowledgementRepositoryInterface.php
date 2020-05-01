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
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Infrastructure\RequestParameters\RequestParametersTranslatorException;

interface AcknowledgementRepositoryInterface
{
    /**
     * Sets the access groups that will be used to filter acknowledgements.
     *
     * @param AccessGroup[]|null $accessGroups
     * @return self
     */
    public function filterByAccessGroups(?array $accessGroups): self;

    /**
     * Find the latest service acknowledgement.
     *
     * @param int $hostId Host id linked to the service
     * @param int $serviceId Service id for which we want the latest acknowledgement
     * @return Acknowledgement|null
     * @throws \Exception
     */
    public function findLatestServiceAcknowledgement(int $hostId, int $serviceId): ?Acknowledgement;

    /**
     * Find the latest host acknowledgement.
     *
     * @param int $hostId Host id for which we want to find the lastest acknowledgement
     * @return Acknowledgement|null
     * @throws \Exception
     */
    public function findLatestHostAcknowledgement(int $hostId): ?Acknowledgement;

    /**
     * Find one acknowledgement **without taking into account** the ACLs of user.
     *
     * @param int $acknowledgementId Acknowledgement id
     * @return Acknowledgement|null Return NULL if the acknowledgement has not been found
     * @throws \Exception
     */
    public function findOneAcknowledgementForAdminUser(int $acknowledgementId): ?Acknowledgement;

    /**
     * Find one acknowledgement **taking into account** the ACLs of user.
     *
     * @param int $acknowledgementId Acknowledgement id
     * @return Acknowledgement|null Return NULL if the acknowledgement has not been found
     * @throws \Exception
     */
    public function findOneAcknowledgementForNonAdminUser(int $acknowledgementId): ?Acknowledgement;

    /**
     * Find all acknowledgements **without taking into account** the ACLs of user.
     *
     * @return Acknowledgement[] Return the acknowledgements found
     * @throws \Exception
     */
    public function findAcknowledgementsForAdminUser(): array;

    /**
     * Find all acknowledgements **taking into account** the ACLs of user.
     *
     * @return Acknowledgement[] Return the acknowledgements found
     * @throws \Exception
     */
    public function findAcknowledgementsForNonAdminUser(): array;

    /**
     * Find acknowledgements of all hosts.
     *
     * @return Acknowledgement[]
     * @throws \Exception
     * @throws RequestParametersTranslatorException
     */
    public function findHostsAcknowledgements();

    /**
     * Find acknowledgements of all services.
     *
     * @return Acknowledgement[]
     * @throws \Exception
     * @throws RequestParametersTranslatorException
     */
    public function findServicesAcknowledgements();

    /**
     * Find host acknowledgements.
     *
     * @param int $hostId Host id for which we want to find the acknowledgements
     * @return Acknowledgement[]
     * @throws \Exception
     */
    public function findAcknowledgementsByHost(int $hostId): array;

    /**
     * Find service acknowledgements.
     *
     * @param int $hostId Host id linked to the service
     * @param int $serviceId Service id for which we want the acknowledgements
     * @return Acknowledgement[]
     * @throws \Exception
     */
    public function findAcknowledgementsByService(int $hostId, int $serviceId): array;

    /**
     * Indicates whether the contact is an admin or not.
     *
     * @param bool $isAdmin Set TRUE if the contact is an admin
     * @return self
     */
    public function setAdmin(bool $isAdmin): self;

    /**
     * @param ContactInterface $contact
     * @return AcknowledgementRepositoryInterface
     */
    public function setContact(ContactInterface $contact): self;
}
