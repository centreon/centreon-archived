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

namespace Centreon\Domain\Monitoring;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Monitoring\ResourceFilter;

/**
 * Service manage the resources in real-time monitoring : hosts and services.
 *
 * @package Centreon\Domain\Monitoring
 */
class ResourceService extends AbstractCentreonService implements ResourceServiceInterface
{
    /**
     * @var \Centreon\Domain\Monitoring\Interfaces\ResourceRepositoryInterface
     */
    private $resourceRepository;

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @param ResourceRepositoryInterface $resourceRepository
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param UrlGeneratorInterface $router
     */
    public function __construct(
        ResourceRepositoryInterface $resourceRepository,
        AccessGroupRepositoryInterface $accessGroupRepository,
        UrlGeneratorInterface $router
    ) {
        $this->resourceRepository = $resourceRepository;
        $this->accessGroupRepository = $accessGroupRepository;
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     * @param Contact $contact
     * @return self
     */
    public function filterByContact($contact): self
    {
        parent::filterByContact($contact);

        $this->resourceRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($this->accessGroupRepository->findByContact($contact));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function findResources(ResourceFilter $filter): array
    {
        $list = $this->resourceRepository->findResources($filter);

        // set paths to endpoints
        foreach ($list as $resource) {
            $routeNameAcknowledgement = 'centreon_application_acknowledgement_addhostacknowledgement';
            $routeNameDowntime = 'monitoring.downtime.addHostDowntime';
            $parameters = [
                'hostId' => $resource->getId(),
            ];

            if ($resource->getType() === Resource::TYPE_SERVICE && $resource->getParent()) {
                $routeNameAcknowledgement = 'centreon_application_acknowledgement_addserviceacknowledgement';
                $routeNameDowntime = 'monitoring.downtime.addServiceDowntime';

                $parameters['hostId'] = $resource->getParent()->getId();
                $parameters['serviceId'] = $resource->getId();
            }

            $resource->setAcknowledgementEndpoint($this->router->generate($routeNameAcknowledgement, $parameters));
            $resource->setDowntimeEndpoint($this->router->generate($routeNameDowntime, $parameters));
        }

        return $list;
    }
}
