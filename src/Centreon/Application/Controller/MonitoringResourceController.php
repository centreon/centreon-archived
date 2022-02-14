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
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Icon;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Monitoring\Service;
use JMS\Serializer\SerializerInterface;
use Centreon\Domain\Entity\EntityValidator;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\ResourceStatus;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Application\Normalizer\IconUrlNormalizer;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Monitoring\Exception\ResourceException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Centreon\Domain\Monitoring\Serializer\ResourceExclusionStrategy;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\ResourceGroup;
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
        'status_types',
    ];

    public const FILTER_RESOURCES_ON_PERFORMANCE_DATA_AVAILABILITY = 'only_with_performance_data';


    private const META_SERVICE_CONFIGURATION_URI = '/main.php?p=60204&o=c&meta_id={resource_id}';
    private const META_SERVICE_DETAILS_ROUTE = 'centreon_application_monitoring_resource_details_meta_service';
    private const META_SERVICE_TIMELINE_ROUTE = 'centreon_application_monitoring_gettimelinebymetaservices';
    private const META_SERVICE_DOWNTIME_ROUTE = 'monitoring.downtime.addMetaServiceDowntime';
    private const META_SERVICE_ACKNOWLEDGEMENT_ROUTE =
        'centreon_application_acknowledgement_addmetaserviceacknowledgement';
    private const META_SERVICE_STATUS_GRAPH_ROUTE = 'monitoring.metric.getMetaServiceStatusMetrics';
    private const META_SERVICE_METRIC_LIST_ROUTE = 'centreon_application_find_meta_service_metrics';
    private const META_SERVICE_PERFORMANCE_GRAPH_ROUTE = 'monitoring.metric.getMetaServicePerformanceMetrics';

    private const HOST_CONFIGURATION_URI = '/main.php?p=60101&o=c&host_id={resource_id}';
    private const SERVICE_CONFIGURATION_URI = '/main.php?p=60201&o=c&service_id={resource_id}';
    private const HOST_LOGS_URI = '/main.php?p=20301&h={resource_id}';
    private const SERVICE_LOGS_URI = '/main.php?p=20301&svc={parent_resource_id}_{resource_id}';
    private const META_SERVICE_LOGS_URI = '/main.php?p=20301&svc={host_id}_{service_id}';
    private const HOST_REPORTING_URI = '/main.php?p=307&host={resource_id}';
    private const SERVICE_REPORTING_URI =
        '/main.php?p=30702&period=yesterday&start=&end=&host_id={parent_resource_id}&item={resource_id}';

    private const HOSTGROUP_CONFIGURATION_URI = '/main.php?p=60102&o=c&hg_id={resource_group_id}';
    private const SERVICEGROUP_CONFIGURATION_URI = '/main.php?p=60203&o=c&sg_id={resource_group_id}';

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

    private const HOST_ACKNOWLEDGEMENT_ROUTE = 'centreon_application_acknowledgement_addhostacknowledgement';
    private const SERVICE_ACKNOWLEDGEMENT_ROUTE = 'centreon_application_acknowledgement_addserviceacknowledgement';
    private const HOST_DOWNTIME_ROUTE = 'monitoring.downtime.addHostDowntime';
    private const SERVICE_DOWNTIME_ROUTE = 'monitoring.downtime.addServiceDowntime';
    private const HOST_DETAILS_ROUTE = 'centreon_application_monitoring_resource_details_host';
    private const SERVICE_DETAILS_ROUTE = 'centreon_application_monitoring_resource_details_service';
    private const HOST_TIMELINE_ROUTE = 'centreon_application_monitoring_gettimelinebyhost';
    private const SERVICE_TIMELINE_ROUTE = 'centreon_application_monitoring_gettimelinebyhostandservice';
    private const SERVICE_STATUS_GRAPH_ROUTE = 'monitoring.metric.getServiceStatusMetrics';
    private const SERVICE_PERFORMANCE_GRAPH_ROUTE = 'monitoring.metric.getServicePerformanceMetrics';

    // Groups for serialization
    public const SERIALIZER_GROUPS_LISTING = [
        ResourceEntity::SERIALIZER_GROUP_MAIN,
        ResourceEntity::SERIALIZER_GROUP_PARENT,
        Icon::SERIALIZER_GROUP_MAIN,
        ResourceStatus::SERIALIZER_GROUP_MAIN,
    ];

    // Groups for validation
    public const VALIDATION_GROUP_MAIN = 'resource_id_main';

    /**
     * @var MonitoringServiceInterface
     */
    private $monitoring;

    /**
     * @var ResourceServiceInterface
     */
    protected $resource;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var IconUrlNormalizer
     */
    protected $iconUrlNormalizer;

    /**
     * @param MonitoringServiceInterface $monitoringService
     * @param ResourceServiceInterface $resource
     * @param UrlGeneratorInterface $router
     * @param IconUrlNormalizer $iconUrlNormalizer
     */
    public function __construct(
        MonitoringServiceInterface $monitoringService,
        ResourceServiceInterface $resource,
        UrlGeneratorInterface $router,
        IconUrlNormalizer $iconUrlNormalizer
    ) {
        $this->monitoring = $monitoringService;
        $this->resource = $resource;
        $this->router = $router;
        $this->iconUrlNormalizer = $iconUrlNormalizer;
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

        $context = (new Context())
            ->setGroups(self::SERIALIZER_GROUPS_LISTING)
            ->enableMaxDepth();

        $context->addExclusionStrategy(new ResourceExclusionStrategy());

        $resources = $this->resource
            ->filterByContact($contact)
            ->findResources($filter);

        $this->providePerformanceGraphEndpoint($resources);

        foreach ($resources as $resource) {
            if ($resource->getIcon() instanceof Icon) {
                $this->iconUrlNormalizer->normalize($resource->getIcon());
            }

            if ($resource->getParent() !== null && $resource->getParent()->getIcon() instanceof Icon) {
                $this->iconUrlNormalizer->normalize($resource->getParent()->getIcon());
            }

            // add shortcuts
            $this->provideLinks($resource, $contact);
        }

        return $this->view([
            'result' => $resources,
            'meta' => $requestParameters->toArray(),
        ])->setContext($context);
    }

    /**
     * Add performance graph endpoint on resources which have performance data
     *
     * @param ResourceEntity[] $resources
     * @return void
     */
    private function providePerformanceGraphEndpoint(array $resources)
    {
        $resourcesWithGraphData = $this->resource->extractResourcesWithGraphData($resources);

        foreach ($resources as $resource) {
            foreach ($resourcesWithGraphData as $resourceWithGraphData) {
                if (
                    $resource->getType() === ResourceEntity::TYPE_SERVICE
                    && $resourceWithGraphData->getType() === ResourceEntity::TYPE_SERVICE
                    && $resource->getParent()->getId() === $resourceWithGraphData->getParent()->getId()
                    && $resource->getId() === $resourceWithGraphData->getId()
                ) {
                    // set service performance graph endpoint from metrics controller
                    $resource->getLinks()->getEndpoints()->setPerformanceGraph(
                        $this->router->generate(
                            self::SERVICE_PERFORMANCE_GRAPH_ROUTE,
                            [
                                'hostId' => $resource->getParent()->getId(),
                                'serviceId' => $resource->getId(),
                            ]
                        )
                    );
                } elseif (
                    $resource->getType() === ResourceEntity::TYPE_META
                    && $resource->getId() === $resourceWithGraphData->getId()
                ) {
                    // set service performance graph endpoint from metrics controller
                    $resource->getLinks()->getEndpoints()->setPerformanceGraph(
                        $this->router->generate(
                            self::META_SERVICE_PERFORMANCE_GRAPH_ROUTE,
                            ['metaId' => $resource->getId()]
                        )
                    );
                }
            }
        }
    }

    /**
     * Add internal, external uris and endpoints to the given resource
     *
     * @param ResourceEntity $resource
     * @param Contact $contact
     * @return void
     */
    private function provideLinks(ResourceEntity $resource, Contact $contact): void
    {
        $this->provideEndpoints($resource);
        $this->provideInternalUris($resource, $contact);
    }

    /**
     * Add endpoints to the given resource
     *
     * @param ResourceEntity $resource
     * @return void
     */
    private function provideEndpoints(ResourceEntity $resource): void
    {
        $acknowledgementFilter = ['limit' => 1];
        $downtimeFilter = [
            'search' => json_encode([
                RequestParameters::AGGREGATE_OPERATOR_AND => [
                    [
                        'start_time' => [
                            RequestParameters::OPERATOR_LESS_THAN => time(),
                        ],
                        'end_time' => [
                            RequestParameters::OPERATOR_GREATER_THAN => time(),
                        ],
                        [
                            RequestParameters::AGGREGATE_OPERATOR_OR => [
                                'is_cancelled' => [
                                    RequestParameters::OPERATOR_NOT_EQUAL => 1,
                                ],
                                'deletion_time' => [
                                    RequestParameters::OPERATOR_GREATER_THAN => time(),
                                ],
                            ],
                        ]
                    ]
                ]
            ])
        ];

        $hostResource = null;

        if ($resource->getType() === ResourceEntity::TYPE_HOST) {
            $hostResource = $resource;
        } elseif ($resource->getType() === ResourceEntity::TYPE_SERVICE && $resource->getParent()) {
            $hostResource = $resource->getParent();

            $parameters = [
                'hostId' => $resource->getParent()->getId(),
                'serviceId' => $resource->getId(),
            ];

            $resource->getLinks()->getEndpoints()->setDetails(
                $this->router->generate(
                    self::SERVICE_DETAILS_ROUTE,
                    $parameters
                )
            );

            $resource->getLinks()->getEndpoints()->setTimeline(
                $this->router->generate(
                    self::SERVICE_TIMELINE_ROUTE,
                    $parameters
                )
            );

            $resource->getLinks()->getEndpoints()->setAcknowledgement(
                $this->router->generate(
                    self::SERVICE_ACKNOWLEDGEMENT_ROUTE,
                    array_merge($parameters, $acknowledgementFilter)
                )
            );

            $resource->getLinks()->getEndpoints()->setDowntime(
                $this->router->generate(
                    self::SERVICE_DOWNTIME_ROUTE,
                    array_merge($parameters, $downtimeFilter)
                )
            );

            $resource->getLinks()->getEndpoints()->setStatusGraph(
                $this->router->generate(
                    self::SERVICE_STATUS_GRAPH_ROUTE,
                    $parameters
                )
            );
        } elseif ($resource->getType() === ResourceEntity::TYPE_META) {
            $parameters = [
                'metaId' => $resource->getId(),
            ];

            $resource->getLinks()->getEndpoints()->setDetails(
                $this->router->generate(
                    self::META_SERVICE_DETAILS_ROUTE,
                    $parameters
                )
            );

            $resource->getLinks()->getEndpoints()->setTimeline(
                $this->router->generate(
                    self::META_SERVICE_TIMELINE_ROUTE,
                    $parameters
                )
            );

            $resource->getLinks()->getEndpoints()->setAcknowledgement(
                $this->router->generate(
                    self::META_SERVICE_ACKNOWLEDGEMENT_ROUTE,
                    array_merge($parameters, $acknowledgementFilter)
                )
            );

            $resource->getLinks()->getEndpoints()->setDowntime(
                $this->router->generate(
                    self::META_SERVICE_DOWNTIME_ROUTE,
                    array_merge($parameters, $downtimeFilter)
                )
            );

            $resource->getLinks()->getEndpoints()->setStatusGraph(
                $this->router->generate(
                    self::META_SERVICE_STATUS_GRAPH_ROUTE,
                    $parameters
                )
            );

            $resource->getLinks()->getEndpoints()->setMetrics(
                $this->router->generate(
                    self::META_SERVICE_METRIC_LIST_ROUTE,
                    $parameters
                )
            );
        }

        if ($hostResource !== null) {
            $parameters = [
                'hostId' => $hostResource->getId(),
            ];

            $hostResource->getLinks()->getEndpoints()->setDetails(
                $this->router->generate(
                    self::HOST_DETAILS_ROUTE,
                    $parameters
                )
            );

            $hostResource->getLinks()->getEndpoints()->setTimeline(
                $this->router->generate(
                    self::HOST_TIMELINE_ROUTE,
                    $parameters
                )
            );

            $hostResource->getLinks()->getEndpoints()->setAcknowledgement(
                $this->router->generate(
                    self::HOST_ACKNOWLEDGEMENT_ROUTE,
                    array_merge($parameters, $acknowledgementFilter)
                )
            );

            $hostResource->getLinks()->getEndpoints()->setDowntime(
                $this->router->generate(
                    self::HOST_DOWNTIME_ROUTE,
                    array_merge($parameters, $downtimeFilter)
                )
            );
        }
    }

    /**
     * Add internal uris (configuration, logs, reporting) to the given resource
     *
     * @param ResourceEntity $resource
     * @param Contact $contact
     * @return void
     */
    private function provideInternalUris(ResourceEntity $resource, Contact $contact): void
    {
        if ($resource->getType() === ResourceEntity::TYPE_SERVICE && $resource->getParent()) {
            $this->provideHostInternalUris($resource->getParent(), $contact);
            $this->provideServiceInternalUris($resource, $contact);
        } elseif ($resource->getType() === ResourceEntity::TYPE_META) {
            $this->provideMetaServiceInternalUris($resource, $contact);
        } else {
            $this->provideHostInternalUris($resource, $contact);
        }
    }

    /**
     * Add host internal uris (configuration, logs, reporting) to the given resource
     *
     * @param ResourceEntity $resource
     * @param Contact $contact
     * @return void
     */
    private function provideHostInternalUris(ResourceEntity $resource, Contact $contact): void
    {
        if (
            $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_HOSTS_WRITE)
            || $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_HOSTS_READ)
        ) {
            $resource->getLinks()->getUris()->setConfiguration(
                $this->generateResourceUri($resource, self::HOST_CONFIGURATION_URI)
            );
        }

        if ($contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)) {
            $resource->getLinks()->getUris()->setLogs(
                $this->generateResourceUri($resource, self::HOST_LOGS_URI)
            );
        }

        if ($contact->hasTopologyRole(Contact::ROLE_REPORTING_DASHBOARD_HOSTS)) {
            $resource->getLinks()->getUris()->setReporting(
                $this->generateResourceUri($resource, self::HOST_REPORTING_URI)
            );
        }
    }

    /**
     * Add service internal uris (configuration, logs, reporting) to the given resource
     *
     * @param ResourceEntity $resource
     * @param Contact $contact
     * @return void
     */
    private function provideServiceInternalUris(ResourceEntity $resource, Contact $contact): void
    {
        if (
            $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_WRITE)
            || $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_READ)
        ) {
            $resource->getLinks()->getUris()->setConfiguration(
                $this->generateResourceUri($resource, self::SERVICE_CONFIGURATION_URI)
            );
        }

        if ($contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)) {
            $resource->getLinks()->getUris()->setLogs(
                $this->generateResourceUri($resource, self::SERVICE_LOGS_URI)
            );
        }

        if ($contact->hasTopologyRole(Contact::ROLE_REPORTING_DASHBOARD_SERVICES)) {
            $resource->getLinks()->getUris()->setReporting(
                $this->generateResourceUri($resource, self::SERVICE_REPORTING_URI)
            );
        }
    }

    /**
     * Add service internal uris (configuration, logs, reporting) to the given resource
     *
     * @param ResourceEntity $resource
     * @param Contact $contact
     * @return void
     */
    private function provideMetaServiceInternalUris(ResourceEntity $resource, Contact $contact): void
    {
        if (
            $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_META_SERVICES_WRITE)
            || $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_META_SERVICES_READ)
            || $contact->isAdmin()
        ) {
            $resource->getLinks()->getUris()->setConfiguration(
                $this->generateResourceUri($resource, self::META_SERVICE_CONFIGURATION_URI)
            );
        }

        if ($contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)) {
            $resource->getLinks()->getUris()->setLogs(
                $this->generateResourceUri($resource, self::META_SERVICE_LOGS_URI)
            );
        }
    }

    /**
     * This function adds to the group the redirection URI to the configuration
     *
     * @param ResourceEntity $resource
     * @param Contact $contact
     * @return void
     */
    private function provideResourceGroupInternalUris(ResourceEntity $resource, Contact $contact): void
    {
        if ($resource->getType() === ResourceEntity::TYPE_HOST) {
            if (
                $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_HOSTS_HOST_GROUPS_READ_WRITE)
                || $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_HOSTS_HOST_GROUPS_READ)
                || $contact->isAdmin()
            ) {
                foreach ($resource->getGroups() as $group) {
                    $group->setConfigurationUri(
                        $this->generateResourceGroupConfigurationUri(
                            $group,
                            self::HOSTGROUP_CONFIGURATION_URI
                        )
                    );
                }
            }
        } elseif ($resource->getType() === ResourceEntity::TYPE_SERVICE) {
            if (
                $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_SERVICE_GROUPS_READ_WRITE)
                || $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_SERVICE_GROUPS_READ)
                || $contact->isAdmin()
            ) {
                foreach ($resource->getGroups() as $group) {
                    $group->setConfigurationUri(
                        $this->generateResourceGroupConfigurationUri(
                            $group,
                            self::SERVICEGROUP_CONFIGURATION_URI
                        )
                    );
                }
            }
        }
    }

    /**
     * Generate full uri from relative path
     *
     * @param ResourceEntity $resource
     * @param string $relativeUri
     * @return string
     */
    private function generateResourceUri(ResourceEntity $resource, string $relativeUri): string
    {
        $relativeUri = str_replace('{resource_id}', (string) $resource->getId(), $relativeUri);
        $relativeUri = str_replace('{host_id}', (string) $resource->getHostId(), $relativeUri);
        $relativeUri = str_replace('{service_id}', (string) $resource->getServiceId(), $relativeUri);

        if ($resource->getParent() !== null) {
            $relativeUri = str_replace('{parent_resource_id}', (string) $resource->getParent()->getId(), $relativeUri);
        }

        return $this->getBaseUri() . $relativeUri;
    }

    /**
     * Generate full uri from relative path for ResourceGroup
     *
     * @param ResourceGroup $group
     * @param string $relativeUri
     * @return string
     */
    private function generateResourceGroupConfigurationUri(ResourceGroup $group, string $relativeUri): string
    {
        $relativeUri = str_replace('{resource_group_id}', (string) $group->getId(), $relativeUri);
        return $this->getBaseUri() . $relativeUri;
    }

    /**
     * Build uri to access host panel with details tab
     *
     * @param integer $hostId
     * @return string
     */
    public function buildHostDetailsUri(int $hostId): string
    {
        return $this->buildHostUri($hostId, self::TAB_DETAILS_NAME);
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
        return $this->buildServiceUri($hostId, $serviceId, self::TAB_DETAILS_NAME);
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
                'uuid' => 's' . $serviceId
            ]),
        ]);
    }

    /**
     * Build uri to access meta service panel
     *
     * @param integer $metaId
     * @param string $tab tab name
     * @return string
     */
    public function buildMetaServiceDetailsUri(int $metaId, string $tab = self::TAB_DETAILS_NAME): string
    {
        if (!in_array($tab, self::ALLOWED_TABS)) {
            throw new ResourceException(sprintf(_('Cannot build uri to unknown tab : %s'), $tab));
        }

        return $this->buildListingUri([
            'details' => json_encode([
                'parentType' => null,
                'parentId' => null,
                'type' => ResourceEntity::TYPE_META,
                'id' => $metaId,
                'tab' => $tab,
                'uuid' => 'm' . $metaId
            ]),
        ]);
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
