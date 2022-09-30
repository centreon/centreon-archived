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

namespace Centreon\Application\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\Context\Context;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Icon;
use JMS\Serializer\SerializerInterface;
use Centreon\Domain\Entity\EntityValidator;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\ResourceStatus;
use Core\Domain\RealTime\ResourceTypeInterface;
use Core\Domain\RealTime\Model\Icon as NewIconModel;
use Centreon\Application\Normalizer\IconUrlNormalizer;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Monitoring\Exception\ResourceException;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Centreon\Domain\Monitoring\Serializer\ResourceExclusionStrategy;
use Core\Infrastructure\RealTime\Hypermedia\HypermediaProviderInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;

/**
 * Resource APIs for the Unified View page
 *
 * @package Centreon\Application\Controller
 */
class MonitoringResourceController extends AbstractController
{
    /**
     * List of external parameters for list action
     *
     * @var array<string>
     */
    public const EXTRA_PARAMETERS_LIST = [
        'types',
        'states',
        'statuses',
        'hostgroup_names',
        'servicegroup_names',
        'monitoring_server_names',
        'service_category_names',
        'host_category_names',
        'service_severity_names',
        'host_severity_names',
        'host_severity_levels',
        'service_severity_levels',
        'status_types',
    ];

    /**
     * @var ResourceTypeInterface[]
     */
    private array $resourceTypes = [];

    /**
     * @var HypermediaProviderInterface[]
     */
    private array $hyperMediaProviders = [];

    public const FILTER_RESOURCES_ON_PERFORMANCE_DATA_AVAILABILITY = 'only_with_performance_data';


    private const RESOURCE_LISTING_URI = '/monitoring/resources';

    public const TAB_DETAILS_NAME = 'details';
    public const TAB_GRAPH_NAME = 'graph';
    public const TAB_SERVICES_NAME = 'services';
    public const TAB_TIMELINE_NAME = 'timeline';
    public const TAB_SHORTCUTS_NAME = 'shortcuts';

    private const ALLOWED_TABS = [
        self::TAB_DETAILS_NAME,
        self::TAB_GRAPH_NAME,
        self::TAB_SERVICES_NAME,
        self::TAB_TIMELINE_NAME,
        self::TAB_SHORTCUTS_NAME,
    ];

    // Groups for serialization
    public const SERIALIZER_GROUPS_LISTING = [
        ResourceEntity::SERIALIZER_GROUP_MAIN,
        ResourceEntity::SERIALIZER_GROUP_PARENT,
        Icon::SERIALIZER_GROUP_MAIN,
        ResourceStatus::SERIALIZER_GROUP_MAIN,
        self::ICON_GROUP_MAIN,
        self::SEVERITY_GROUP_MAIN,
    ];

    // Groups for validation
    public const VALIDATION_GROUP_MAIN = 'resource_id_main';
    public const SEVERITY_GROUP_MAIN = 'severity_main';
    public const ICON_GROUP_MAIN = 'core_icon_main';

    /**
     * @param ResourceServiceInterface $resource
     * @param IconUrlNormalizer $iconUrlNormalizer
     * @param \Traversable<ResourceTypeInterface> $resourceTypes
     * @param \Traversable<HypermediaProviderInterface> $hyperMediaProviders
     */
    public function __construct(
        private ResourceServiceInterface $resource,
        private IconUrlNormalizer $iconUrlNormalizer,
        \Traversable $resourceTypes,
        \Traversable $hyperMediaProviders
    ) {
        $this->hasProviders($resourceTypes);
        $this->resourceTypes = iterator_to_array($resourceTypes);
        $this->hasProviders($hyperMediaProviders);
        $this->hyperMediaProviders = iterator_to_array($hyperMediaProviders);
    }

    /**
     * @param \Traversable<mixed> $providers
     * @return void
     */
    private function hasProviders(\Traversable $providers): void
    {
        if ($providers instanceof \Countable && count($providers) === 0) {
            throw new \InvalidArgumentException(
                _('You must add at least one provider')
            );
        }
    }

    /**
     * List all the resources in real-time monitoring : hosts and services.
     *
     * @param RequestParametersInterface $requestParameters
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityValidator $entityValidator
     * @return View
     */
    public function list(
        RequestParametersInterface $requestParameters,
        Request $request,
        SerializerInterface $serializer,
        EntityValidator $entityValidator
    ): View {
        // ACL check
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        // set default values of filter data
        $filterData = [];
        foreach (self::EXTRA_PARAMETERS_LIST as $param) {
            $filterData[$param] = [];
        }

        $filterData[self::FILTER_RESOURCES_ON_PERFORMANCE_DATA_AVAILABILITY] = false;

        // load filter data with the query parameters
        foreach ($request->query->all() as $param => $data) {
            // skip pagination parameters
            if (in_array($param, ['search', 'limit', 'page', 'sort_by'])) {
                continue;
            }

            $filterData[$param] = json_decode($data, true) ?: $data;
        }

        // validate the filter data
        $errors = $entityValidator->validateEntity(
            ResourceFilter::class,
            $filterData,
            ['Default'],
            false // We don't allow extra fields
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        // Parse the filter data into filter object
        $filter = $serializer->deserialize(
            json_encode($filterData),
            ResourceFilter::class,
            'json'
        );

        $this->validateResourceTypeFilterOrFail($filter);

        $context = (new Context())
            ->setGroups(self::SERIALIZER_GROUPS_LISTING)
            ->enableMaxDepth();

        $context->addExclusionStrategy(new ResourceExclusionStrategy());

        $resources = $this->resource
            ->filterByContact($contact)
            ->findResources($filter);

        foreach ($resources as $resource) {
            if ($resource->getIcon() instanceof Icon) {
                $this->iconUrlNormalizer->normalize($resource->getIcon());
            }

            if ($resource->getParent() !== null && $resource->getParent()->getIcon() instanceof Icon) {
                $this->iconUrlNormalizer->normalize($resource->getParent()->getIcon());
            }

            if ($resource->getSeverity() !== null && $resource->getSeverity()->getIcon() instanceof NewIconModel) {
                $this->iconUrlNormalizer->normalize($resource->getSeverity()->getIcon());
            }

            // add shortcuts
            $this->provideLinks($resource);
        }

        return $this->view([
            'result' => $resources,
            'meta' => $requestParameters->toArray(),
        ])->setContext($context);
    }

    /**
     * Checks that filter types provided in the payload are supported.
     *
     * @param ResourceFilter $filter
     * @throws \InvalidArgumentException
     */
    private function validateResourceTypeFilterOrFail(ResourceFilter $filter): void
    {
        /**
         * Checking types provided in the ResourceFilter entity and check
         * if it is part of the available resourceTypes
         */
        $availableResourceTypes = array_map(
            fn (ResourceTypeInterface $resourceType) => $resourceType->getName(),
            $this->resourceTypes
        );

        foreach ($filter->getTypes() as $resourceType) {
            if (! in_array($resourceType, $availableResourceTypes)) {
                throw new \InvalidArgumentException(
                    sprintf(_('Resource type "%s" is not supported'), $resourceType)
                );
            }
        }
    }

    /**
     * Add internal, external uris and endpoints to the given resource
     *
     * @param ResourceEntity $resource
     * @return void
     */
    private function provideLinks(ResourceEntity $resource): void
    {
        $parameters = [
            'internalId' => $resource->getInternalId(),
            'hostId' => $resource->getHostId(),
            'serviceId' => $resource->getServiceId(),
            'hasGraphData' => $resource->hasGraph()
        ];

        $uris = [];
        $endpoints = [];

        foreach ($this->hyperMediaProviders as $hyperMediaProvider) {
            if (
                $resource->getType() !== null
                && $hyperMediaProvider->isValidFor($resource->getType())
            ) {
                $endpoints = $hyperMediaProvider->createEndpoints($parameters);
                $uris = $hyperMediaProvider->createInternalUris($parameters);
            }
        }

        $resource->getLinks()->getEndpoints()
            ->setDetails($endpoints['details'])
            ->setPerformanceGraph($endpoints['performance_graph'] ?? null)
            ->setStatusGraph($endpoints['status_graph'] ?? null)
            ->setDowntime($endpoints['downtime'])
            ->setAcknowledgement($endpoints['acknowledgement'])
            ->setTimeline($endpoints['timeline']);

        $resource->getLinks()->getUris()
            ->setConfiguration($uris['configuration'])
            ->setReporting($uris['reporting'])
            ->setLogs($uris['logs']);
    }

    /**
     * Generates a resource details redirection link
     *
     * @param string $resourceType
     * @param integer $resourceId
     * @param array<string, integer> $parameters
     * @return string
     */
    private function buildResourceDetailsUri(string $resourceType, int $resourceId, array $parameters): string
    {
        $resourceDetailsEndpoint = null;
        foreach ($this->hyperMediaProviders as $hyperMediaProvider) {
            if ($hyperMediaProvider->isValidFor($resourceType)) {
                $resourceDetailsEndpoint = $hyperMediaProvider->generateResourceDetailsUri($parameters);
            }
        }

        return $this->buildListingUri([
            'details' => json_encode([
                'id' => $resourceId,
                'tab' => self::TAB_DETAILS_NAME,
                'resourcesDetailsEndpoint' => $this->getBaseUri() . $resourceDetailsEndpoint
            ])
        ]);
    }

    /**
     * Build uri to access host panel with details tab
     *
     * @param integer $hostId
     * @return string
     */
    public function buildHostDetailsUri(int $hostId): string
    {
        return $this->buildResourceDetailsUri(
            ResourceEntity::TYPE_HOST,
            $hostId,
            ['hostId' => $hostId]
        );
    }

    /**
     * Build uri to access host panel
     *
     * @param integer $hostId
     * @param string $tab tab name
     * @return string
     */
    public function buildHostUri(int $hostId, string $tab = self::TAB_DETAILS_NAME): string
    {
        if (!in_array($tab, self::ALLOWED_TABS)) {
            throw new ResourceException(sprintf(_('Cannot build uri to unknown tab : %s'), $tab));
        }

        return $this->buildListingUri([
            'details' => json_encode([
                'type' => ResourceEntity::TYPE_HOST,
                'id' => $hostId,
                'tab' => $tab,
                'uuid' => 'h' . $hostId
            ]),
        ]);
    }

    /**
     * Build uri to access service service panel with details tab
     *
     * @param integer $hostId
     * @param integer $serviceId
     * @return string
     */
    public function buildServiceDetailsUri(int $hostId, int $serviceId): string
    {
        return $this->buildResourceDetailsUri(
            ResourceEntity::TYPE_SERVICE,
            $serviceId,
            [
                'hostId' => $hostId,
                'serviceId' => $serviceId
            ]
        );
    }

    /**
     * Build uri to access service panel
     *
     * @param integer $hostId
     * @param integer $serviceId
     * @param string $tab tab name
     * @return string
     */
    public function buildServiceUri(int $hostId, int $serviceId, string $tab = self::TAB_DETAILS_NAME): string
    {
        if (!in_array($tab, self::ALLOWED_TABS)) {
            throw new ResourceException(sprintf(_('Cannot build uri to unknown tab : %s'), $tab));
        }

        return $this->buildListingUri([
            'details' => json_encode([
                'parentType' => ResourceEntity::TYPE_HOST,
                'parentId' => $hostId,
                'type' => ResourceEntity::TYPE_SERVICE,
                'id' => $serviceId,
                'tab' => $tab,
                'uuid' => 'h' . $hostId . '-s' . $serviceId
            ]),
        ]);
    }

    /**
     * Build uri to access meta service panel
     *
     * @param integer $metaId
     * @return string
     */
    public function buildMetaServiceDetailsUri(int $metaId): string
    {
        return $this->buildResourceDetailsUri(
            ResourceEntity::TYPE_META,
            $metaId,
            [
                'metaId' => $metaId
            ]
        );
    }

    /**
     * Build uri to access listing page of resources with specific parameters
     *
     * @param string[] $parameters
     * @return string
     */
    public function buildListingUri(array $parameters): string
    {
        $baseListingUri = $this->getBaseUri() . self::RESOURCE_LISTING_URI;

        if (!empty($parameters)) {
            $baseListingUri .= '?' . http_build_query($parameters);
        }

        return $baseListingUri;
    }
}
