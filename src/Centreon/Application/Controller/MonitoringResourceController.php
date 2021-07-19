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

namespace Centreon\Application\Controller;

use Centreon\Application\Normalizer\IconUrlNormalizer;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Exception\ValidationFailedException;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Centreon\Domain\Monitoring\Serializer\ResourceExclusionStrategy;
use Centreon\Domain\Monitoring\Icon;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\Resources;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\Model\ResourceDetailsHost;
use Centreon\Domain\Monitoring\Model\ResourceDetailsService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Domain\Contact\Contact;

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
     * @var array
     */
    public const EXTRA_PARAMETERS_LIST = [
        'types',
        'states',
        'statuses',
        'hostgroup_ids',
        'servicegroup_ids',
    ];

    private const HOST_CONFIGURATION_URI = '/main.php?p=60101&o=c&host_id={resource_id}';
    private const SERVICE_CONFIGURATION_URI = '/main.php?p=60201&o=c&service_id={resource_id}';
    private const HOST_LOGS_URI = '/main.php?p=20301&h={resource_id}';
    private const SERVICE_LOGS_URI = '/main.php?p=20301&svc={parent_resource_id}_{resource_id}';
    private const HOST_REPORTING_URI = '/main.php?p=307&host={resource_id}';
    private const SERVICE_REPORTING_URI =
        '/main.php?p=30702&period=yesterday&start=&end=&host_id={parent_resource_id}&item={resource_id}';

    // Groups for serialization
    public const SERIALIZER_GROUP_MAIN = 'resource_id_main';

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
        foreach (static::EXTRA_PARAMETERS_LIST as $param) {
            $filterData[$param] = [];
        }

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
            ->setGroups(Resources::contextGroupsForListing())
            ->enableMaxDepth();

        $context->addExclusionStrategy(new ResourceExclusionStrategy());

        $resources = $this->resource->filterByContact($contact)
            ->findResources($filter);

        $resourcesGraphData = $this->resource->getListOfResourcesWithGraphData($resources);

        foreach ($resources as $resource) {
            if ($resource->getIcon() instanceof Icon) {
                $this->iconUrlNormalizer->normalize($resource->getIcon());
            }

            if ($resource->getParent() !== null && $resource->getParent()->getIcon() instanceof Icon) {
                $this->iconUrlNormalizer->normalize($resource->getParent()->getIcon());
            }

            // add shortcuts
            $this->provideInternalUris($resource, $contact);

            // set paths to endpoints
            $routeNameAcknowledgement = 'centreon_application_acknowledgement_addhostacknowledgement';
            $routeNameDowntime = 'monitoring.downtime.addHostDowntime';
            $routeNameDetails = 'centreon_application_monitoring_resource_details_host';
            $routeNameTimeline = 'centreon_application_monitoring_gettimelinebyhost';

            $parameters = [
                'hostId' => $resource->getId(),
            ];

            if ($resource->getType() === Resources::TYPE_SERVICE && $resource->getParent()) {
                $parameters['hostId'] = $resource->getParent()->getId();

                $resource->getParent()->setDetailsEndpoint($this->router->generate($routeNameDetails, $parameters));
                $resource->getParent()->setTimelineEndpoint($this->router->generate($routeNameTimeline, $parameters));

                $routeNameAcknowledgement = 'centreon_application_acknowledgement_addserviceacknowledgement';
                $routeNameDowntime = 'monitoring.downtime.addServiceDowntime';
                $routeNameDetails = 'centreon_application_monitoring_resource_details_service';
                $routeNameTimeline = 'centreon_application_monitoring_gettimelinebyhostandservice';

                $parameters['serviceId'] = $resource->getId();
            }

            $resource->setAcknowledgementEndpoint(
                $this->router->generate($routeNameAcknowledgement, array_merge($parameters, ['limit' => 1]))
            );
            $resource->setDowntimeEndpoint($this->router->generate($routeNameDowntime, array_merge($parameters, [
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
                    ],
                ]),
            ])));
            $resource->setDetailsEndpoint($this->router->generate($routeNameDetails, $parameters));
            $resource->setTimelineEndpoint($this->router->generate($routeNameTimeline, $parameters));

            if (
                $resource->getParent() != null && in_array([
                    'host_id' => $resource->getParent()->getId(),
                    'service_id' => $resource->getId(),
                ], $resourcesGraphData)
            ) {
                $parameters = [
                    'hostId' => $resource->getParent()->getId(),
                    'serviceId' => $resource->getId(),
                ];

                // set service performance graph endpoint from metrics controller
                $resource->setPerformanceGraphEndpoint(
                    $this->router->generate(
                        'monitoring.metric.getServicePerformanceMetrics',
                        $parameters
                    )
                );

                // set service status graph endpoint from metrics controller
                $resource->setStatusGraphEndpoint(
                    $this->router->generate(
                        'monitoring.metric.getServiceStatusMetrics',
                        $parameters
                    )
                );

                $resource->getParent()
                    ->setDetailsEndpoint($this->router->generate(
                        'centreon_application_monitoring_resource_details_host',
                        [
                            'hostId' => $resource->getParent()->getId(),
                        ]
                    ));
            }
        }

        return $this->view([
            'result' => $resources,
            'meta' => $requestParameters->toArray(),
        ])->setContext($context);
    }

    /**
     * Get resource details related to the host
     *
     * @return View
     */
    public function detailsHost(int $hostId): View
    {
        // ACL check
        $this->denyAccessUnlessGrantedForApiRealtime();

        $host = $this->monitoring
            ->filterByContact($this->getUser())
            ->findOneHost($hostId);

        try {
            $this->monitoring->hidePasswordInHostCommandLine($host);
        } catch (\Throwable $ex) {
            $host->setCheckCommand(
                sprintf(_('Unable to hide passwords in command (Reason: %s)'), $ex->getMessage())
            );
        }

        if ($host === null) {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }

        $context = (new Context())
            ->setGroups(array_merge([
                ResourceDetailsHost::SERIALIZER_GROUP_DETAILS,
                ResourceStatus::SERIALIZER_GROUP_MAIN,
                Service::SERIALIZER_GROUP_MIN,
                Acknowledgement::SERIALIZER_GROUP_FULL,
            ], Downtime::SERIALIZER_GROUPS_SERVICE))
            ->enableMaxDepth();

        return $this
            ->view($this->resource->enrichHostWithDetails($host))
            ->setContext($context);
    }

    /**
     * Get resource details related to the service
     *
     * @return View
     */
    public function detailsService(int $hostId, int $serviceId): View
    {
        // ACL check
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Service $service
         */
        $service = $this->monitoring
            ->filterByContact($this->getUser())
            ->findOneService($hostId, $serviceId);
        try {
            $this->monitoring->hidePasswordInServiceCommandLine($service);
        } catch (\Throwable $ex) {
            $service->setCommandLine(
                sprintf(_('Unable to hide passwords in command (Reason: %s)'), $ex->getMessage())
            );
        }


        if ($service === null) {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }

        $context = (new Context())
            ->setGroups(array_merge([
                ResourceDetailsService::SERIALIZER_GROUP_DETAILS,
                ResourceStatus::SERIALIZER_GROUP_MAIN,
                Acknowledgement::SERIALIZER_GROUP_FULL,
            ], Downtime::SERIALIZER_GROUPS_SERVICE))
            ->enableMaxDepth();

        return $this
            ->view($this->resource->enrichServiceWithDetails($service))
            ->setContext($context);
    }

    /**
     * Add internal uris (configuration, logs, reporting) to the given resource
     *
     * @param Resources $resource
     * @param Contact $contact
     * @return void
     */
    private function provideInternalUris(Resources $resource, Contact $contact): void
    {
        if ($resource->getType() === Resources::TYPE_SERVICE && $resource->getParent()) {
            $this->provideHostInternalUris($resource->getParent(), $contact);
            $this->provideServiceInternalUris($resource, $contact);
        } else {
            $this->provideHostInternalUris($resource, $contact);
        }
    }

    /**
     * Add host internal uris (configuration, logs, reporting) to the given resource
     *
     * @param Resources $resource
     * @param Contact $contact
     * @return void
     */
    private function provideHostInternalUris(Resources $resource, Contact $contact): void
    {
        if (
            $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_HOSTS_WRITE)
            || $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_HOSTS_READ)
        ) {
            $resource->setConfigurationUri(
                $this->generateResourceUri($resource, static::HOST_CONFIGURATION_URI)
            );
        }

        if ($contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)) {
            $resource->setLogsUri(
                $this->generateResourceUri($resource, static::HOST_LOGS_URI)
            );
        }

        if ($contact->hasTopologyRole(Contact::ROLE_REPORTING_DASHBOARD_HOSTS)) {
            $resource->setReportingUri(
                $this->generateResourceUri($resource, static::HOST_REPORTING_URI)
            );
        }
    }

    /**
     * Add service internal uris (configuration, logs, reporting) to the given resource
     *
     * @param Resources $resource
     * @param Contact $contact
     * @return void
     */
    private function provideServiceInternalUris(Resources $resource, Contact $contact): void
    {
        if (
            $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_WRITE)
            || $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_READ)
        ) {
            $resource->setConfigurationUri(
                $this->generateResourceUri($resource, static::SERVICE_CONFIGURATION_URI)
            );
        }

        if ($contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)) {
            $resource->setLogsUri(
                $this->generateResourceUri($resource, static::SERVICE_LOGS_URI)
            );
        }

        if ($contact->hasTopologyRole(Contact::ROLE_REPORTING_DASHBOARD_SERVICES)) {
            $resource->setReportingUri(
                $this->generateResourceUri($resource, static::SERVICE_REPORTING_URI)
            );
        }
    }

    /**
     * Generate full uri from relative path
     *
     * @param Resources $resource
     * @param string $relativeUri
     * @return string
     */
    private function generateResourceUri(Resources $resource, string $relativeUri): string
    {
        $uri = str_replace('{resource_id}', $resource->getId(), $relativeUri);

        if ($resource->getParent() !== null) {
            $uri = str_replace('{parent_resource_id}', $resource->getParent()->getId(), $uri);
        }

        return $this->getBaseUri() . $uri;
    }
}
