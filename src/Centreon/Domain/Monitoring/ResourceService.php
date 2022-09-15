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

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Monitoring\Exception\ResourceException;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Core\Resources\Application\Repository\ReadResourceRepositoryInterface;

/**
 * Service manage the resources in real-time monitoring : hosts and services.
 *
 * @package Centreon\Domain\Monitoring
 */
class ResourceService extends AbstractCentreonService implements ResourceServiceInterface
{
    public function __construct(
        private ReadAccessGroupRepositoryInterface $accessGroupRepository,
        private ReadResourceRepositoryInterface $resourceRepository
    ) {
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
            $list = $this->getResources($filter);
            // replace macros in external links
            foreach ($list as $resource) {
                $this->replaceMacrosInExternalLinks($resource);
            }
        } catch (\Exception $ex) {
            throw new ResourceException($ex->getMessage(), 0, $ex);
        }

        return $list;
    }

    /**
     * @return \Centreon\Domain\Monitoring\Resource[]
     */
    private function getResources(ResourceFilter $filter): array
    {
        if (!$this->contact instanceof ContactInterface) {
            return [];
        }

        if ($this->contact->isAdmin()) {
            return $this->resourceRepository->findResources($filter);
        }

        $accessGroupIds = array_map(
            function (AccessGroup $accessGroup) {
                return $accessGroup->getId();
            },
            $this->accessGroupRepository->findByContact($this->contact)
        );

        if (!empty($accessGroupIds)) {
            return $this->resourceRepository->findResourcesByAccessGroupIds($filter, $accessGroupIds);
        }

        return [];
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
