<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Monitoring\Exception\ResourceException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;

/**
 * Service manage the resources in real-time monitoring : hosts and services.
 *
 * @package Centreon\Domain\Monitoring
 */
class ResourceService extends AbstractCentreonService implements ResourceServiceInterface
{
    /**
     * @var ResourceRepositoryInterface
     */
    private $resourceRepository;

    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * @param ResourceRepositoryInterface $resourceRepository
     * @param MonitoringRepositoryInterface $monitoringRepository,
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        ResourceRepositoryInterface $resourceRepository,
        MonitoringRepositoryInterface $monitoringRepository,
        AccessGroupRepositoryInterface $accessGroupRepository
    ) {
        $this->resourceRepository = $resourceRepository;
        $this->monitoringRepository = $monitoringRepository;
        $this->accessGroupRepository = $accessGroupRepository;
    }

    /**
     * @inheritDoc
     */
    public function filterByContact($contact): self
    {
        parent::filterByContact($contact);

        $accessGroups = $this->accessGroupRepository->findByContact($contact);

        $this->resourceRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($accessGroups);

        $this->monitoringRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($accessGroups);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function extractResourcesWithGraphData(array $resources): array
    {
        return $this->resourceRepository->extractResourcesWithGraphData($resources);
    }

    /**
     * {@inheritDoc}
     */
    public function findResources(ResourceFilter $filter): array
    {
        // try to avoid exception from the regexp bad syntax in search criteria
        try {
            $list = $this->resourceRepository->findResources($filter);
            // replace macros in external links
            foreach ($list as $resource) {
                $this->replaceMacrosInExternalLinks($resource);
            }
        } catch (RepositoryException $ex) {
            throw new ResourceException($ex->getMessage(), 0, $ex);
        } catch (\Exception $ex) {
            throw new ResourceException($ex->getMessage(), 0, $ex);
        }

        return $list;
    }

    /**
     * Find host id by resource
     * @param ResourceEntity $resource
     * @return int|null
     */
    public static function generateHostIdByResource(ResourceEntity $resource): ?int
    {
        $hostId = null;
        if ($resource->getType() === ResourceEntity::TYPE_HOST) {
            $hostId = (int) $resource->getId();
        } elseif (
            $resource->getParent() !== null
            && $resource->getType() === ResourceEntity::TYPE_SERVICE
        ) {
            $hostId = (int) $resource->getParent()->getId();
        }

        return $hostId;
    }

    /**
     * Replaces macros in the URL for host resource type
     *
     * @param ResourceEntity $resource
     * @param string $url
     * @return string
     */
    private function replaceMacrosInUrlsForHostResource(ResourceEntity $resource, string $url): string
    {
        $url = str_replace('$HOSTADDRESS$', $resource->getFqdn() ?? '', $url);
        $url = str_replace('$HOSTNAME$', $resource->getName(), $url);
        $url = str_replace('$HOSTSTATE$', $resource->getStatus()->getName(), $url);
        $url = str_replace('$HOSTSTATEID$', (string) $resource->getStatus()->getCode(), $url);
        $url = str_replace('$HOSTALIAS$', $resource->getAlias() ?? '', $url);

        return $url;
    }

    /**
     * Replaces macros in the URL for service resource type
     *
     * @param ResourceEntity $resource
     * @param string $url
     * @return string
     */
    private function replaceMacrosInUrlsForServiceResource(ResourceEntity $resource, string $url): string
    {
        $url = str_replace('$HOSTADDRESS$', $resource->getParent()?->getFqdn() ?? '', $url);
        $url = str_replace('$HOSTNAME$', $resource->getParent()?->getName() ?? '', $url);
        $url = str_replace('$HOSTSTATE$', $resource->getParent()?->getStatus()->getName() ?? '', $url);
        $url = str_replace('$HOSTSTATEID$', (string) $resource->getParent()?->getStatus()->getCode(), $url);
        $url = str_replace('$HOSTALIAS$', $resource->getParent()?->getAlias() ?? '', $url);
        $url = str_replace('$SERVICEDESC$', $resource->getName(), $url);
        $url = str_replace('$SERVICESTATE$', $resource->getStatus()->getName(), $url);
        $url = str_replace('$SERVICESTATEID$', (string) $resource->getStatus()->getCode(), $url);

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function replaceMacrosInExternalLinks(ResourceEntity $resource): void
    {
        $actionUrl = $resource->getLinks()->getExternals()->getActionUrl();
        $notesObject = $resource->getLinks()->getExternals()->getNotes();
        $notesUrl = ($notesObject !== null) ? $notesObject->getUrl() : null;
        $resourceType = $resource->getType();

        if (! empty($actionUrl)) {
            if ($resourceType === ResourceEntity::TYPE_HOST) {
                $actionUrl = $this->replaceMacrosInUrlsForHostResource($resource, $actionUrl);
            } elseif ($resourceType === ResourceEntity::TYPE_SERVICE) {
                $actionUrl = $this->replaceMacrosInUrlsForServiceResource($resource, $actionUrl);
            }
            $resource->getLinks()->getExternals()->setActionUrl($actionUrl);
        }

        if (! empty($notesUrl)) {
            if ($resourceType === ResourceEntity::TYPE_HOST) {
                $notesUrl = $this->replaceMacrosInUrlsForHostResource($resource, $notesUrl);
            } elseif ($resourceType === ResourceEntity::TYPE_SERVICE) {
                $notesUrl = $this->replaceMacrosInUrlsForServiceResource($resource, $notesUrl);
            }
            $resource->getLinks()->getExternals()->getNotes()?->setUrl($notesUrl);
        }
    }

    /**
     * Validates input for resource based on groups
     * @param EntityValidator $validator
     * @param ResourceEntity $resource
     * @param array<string> $contextGroups
     * @return ConstraintViolationListInterface<mixed>
     */
    public static function validateResource(
        EntityValidator $validator,
        ResourceEntity $resource,
        array $contextGroups
    ): ConstraintViolationListInterface {
        return $validator->validate(
            $resource,
            null,
            $contextGroups
        );
    }
}
