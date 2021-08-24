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

use Traversable;
use FOS\RestBundle\View\View;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use JMS\Serializer\SerializerInterface;
use Centreon\Domain\Entity\EntityValidator;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\MonitoringService;
use Centreon\Application\Normalizer\IconUrlNormalizer;
use JMS\Serializer\Exception\ValidationFailedException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Exception\MonitoringResourceException;
use Centreon\Domain\Monitoring\MonitoringResource\Interfaces\HyperMediaProviderInterface;
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Model\MonitoringResourceFormatter;
use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\FindMonitoringResources\FindMonitoringResources;
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Model\MonitoringResourceHostDetailFormatter;
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Model\MonitoringResourceServiceDetailFormatter;
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Model\MonitoringResourceMetaServiceDetailFormatter;
use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailMetaServiceMonitoringResource as DetailMeta;
use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailServiceMonitoringResource as DetailService;
use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailHostMonitoringResource as DetailHost;

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
     * @var array<int, string>
     */
    public const EXTRA_PARAMETERS_LIST = [
        'types',
        'states',
        'statuses',
        'hostgroup_ids',
        'servicegroup_ids',
        'monitoring_server_ids',
    ];

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

    /**
     * @var MonitoringServiceInterface
     */
    private $monitoring;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var IconUrlNormalizer
     */
    protected $iconUrlNormalizer;

    /**
     * @var HyperMediaProviderInterface[]
     */
    public $hyperMediaProviders = [];

    /**
     * @param UrlGeneratorInterface $router
     * @param IconUrlNormalizer $iconUrlNormalizer
     */
    public function __construct(
        UrlGeneratorInterface $router,
        IconUrlNormalizer $iconUrlNormalizer,
        MonitoringServiceInterface $monitoring
    ) {
        $this->monitoring = $monitoring;
        $this->router = $router;
        $this->iconUrlNormalizer = $iconUrlNormalizer;
    }

    /**
     * Endpoint to get Host Monitoring Resource details
     *
     * @param integer $hostId
     * @param DetailHost\DetailHostMonitoringResource $detailHostMonitoringResource
     * @return View
     */
    public function detailHost(
        int $hostId,
        DetailHost\DetailHostMonitoringResource $detailHostMonitoringResource
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        // Create a specific filter to only get the monitoring resource
        $filter = (new ResourceFilter())
            ->setTypes([ResourceFilter::TYPE_HOST])
            ->setHostIds([$hostId]);

        // Use case to get details of the monitoring resource
        $response = $detailHostMonitoringResource->execute($filter);

        // Add Links to the monitoring resource
        $hostMonitoringResourceLinks = $this->generateMonitoringResourceLinks(
            $response->getHostMonitoringResourceDetail(),
            $contact
        );

        return $this->view(
            MonitoringResourceHostDetailFormatter::createFromResponse($response, $hostMonitoringResourceLinks)
        );
    }

    /**
     * Endpoint to get Service Monitoring Resource details
     *
     * @param int $hostId
     * @param int $serviceId
     * @param DetailService\DetailServiceMonitoringResource $detailServiceMonitoringResource
     * @return View
     */
    public function detailService(
        int $hostId,
        int $serviceId,
        DetailService\DetailServiceMonitoringResource $detailServiceMonitoringResource
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        // Create a specific filter to only get the monitoring resource
        $filter = (new ResourceFilter())
            ->setTypes([ResourceFilter::TYPE_SERVICE])
            ->setHostIds([$hostId])
            ->setServiceIds([$serviceId]);

        // Use case to get details of the monitoring resource
        $response = $detailServiceMonitoringResource->execute($filter);

        $serviceMonitoringResource = $response->getServiceMonitoringResourceDetail();

        // Add Links to the monitoring resource
        $serviceMonitoringResourceLinks = $this->generateMonitoringResourceLinks(
            $serviceMonitoringResource,
            $contact
        );

        // Handle case when monitoring resource has a parent.
        if ($serviceMonitoringResource['parent'] !== null) {
            $serviceMonitoringResourceLinks['parent'] = $this->generateMonitoringResourceLinks(
                $serviceMonitoringResource['parent'],
                $contact
            );
        }

        // Hide password in commandLine
        if (
            $contact->hasRole(Contact::ROLE_DISPLAY_COMMAND) ||
            $contact->isAdmin()
        ) {
            try {
                $service = (new Service())
                    ->setId($serviceMonitoringResource['id'])
                    ->setHost((new Host())->setId($serviceMonitoringResource['parent']['id']))
                    ->setCommandLine($serviceMonitoringResource['command_line']);
                $this->monitoring->hidePasswordInServiceCommandLine($service);
                $serviceMonitoringResource['command_line'] = $service->getCommandLine();
            } catch (\Throwable $ex) {
                $serviceMonitoringResource['command_line'] = sprintf(
                    _('Unable to hide passwords in command (Reason: %s)'),
                    $ex->getMessage()
                );
            }
        } else {
            $serviceMonitoringResource['command_line'] = null;
        }

        return $this->view(
            MonitoringResourceServiceDetailFormatter::createFromResponse($response, $serviceMonitoringResourceLinks)
        );
    }

    /**
     * Endpoint to get Service Monitoring Resource details
     *
     * @param integer $metaId
     * @param DetailMeta\DetailMetaServiceMonitoringResource $detailMetaServiceMonitoringResource
     * @return View
     */
    public function detailMetaService(
        int $metaId,
        DetailMeta\DetailMetaServiceMonitoringResource $detailMetaServiceMonitoringResource
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        // Create a specific filter to only get the monitoring resource
        $filter = (new ResourceFilter())
            ->setTypes([ResourceFilter::TYPE_META])
            ->setMetaServiceIds([$metaId]);

        // Use case to get details of the monitoring resource
        $response = $detailMetaServiceMonitoringResource->execute($filter);

        $metaServiceMonitoringResource = $response->getMetaServiceMonitoringResourceDetail();

        // Add Links to the monitoring resource
        $metaServiceMonitoringResourceLinks = $this->generateMonitoringResourceLinks(
            $metaServiceMonitoringResource,
            $contact
        );

        return $this->view(
            MonitoringResourceMetaServiceDetailFormatter::createFromResponse(
                $response,
                $metaServiceMonitoringResourceLinks
            )
        );
    }

    /**
     * Endpoint to get the list of Monitoring Resources
     *
     * @param RequestParametersInterface $requestParameters
     * @param Request $request
     * @param FindMonitoringResources $findMonitoringResources
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @return View
     */
    public function findMonitoringResources(
        RequestParametersInterface $requestParameters,
        Request $request,
        FindMonitoringResources $findMonitoringResources,
        EntityValidator $entityValidator,
        SerializerInterface $serializer
    ): View {
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

        $filterData[static::FILTER_RESOURCES_ON_PERFORMANCE_DATA_AVAILABILITY] = false;

        // @todo REMOVE ALL SERIALIZER AND ENTITY VALIDATOR
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

        $filterDataContent = json_encode($filterData);

        if ($filterDataContent === false) {
            throw new MonitoringResourceException('Failed to encode filter data sent');
        }

        // Parse the filter data into filter object
        $filter = $serializer->deserialize(
            $filterDataContent,
            ResourceFilter::class,
            'json'
        );

        $response = $findMonitoringResources->execute($filter);

        // Loop on monitoring resources to add the links
        $monitoringResourceWithLinks = [];
        foreach ($response->getMonitoringResources() as $index => $monitoringResource) {
            $monitoringResourceWithLinks[$index] = $this->generateMonitoringResourceLinks(
                $monitoringResource,
                $contact
            );
            // Handle case when monitoring resource has a parent.
            if ($monitoringResource['parent'] !== null) {
                $monitoringResourceWithLinks[$index]['parent'] = $this->generateMonitoringResourceLinks(
                    $monitoringResource['parent'],
                    $contact
                );
            }
        }

        return $this->view(
            [
                'result' => MonitoringResourceFormatter::createFromResponse($response, $monitoringResourceWithLinks),
                'meta' => $requestParameters->toArray()
            ]
        );
    }

    /**
     * Generate the links for the provided Monitoring Resource
     *
     * @param array<string, mixed> $monitoringResource
     * @param Contact $contact
     * @return array<string, array<string, string>>
     */
    private function generateMonitoringResourceLinks(
        array $monitoringResource,
        Contact $contact
    ): array {
        /**
         * loop foreach on providers to identify the one to call.
         * HyperMedia provider will add the internal uris and endpoints
         * for each resources.
         */
        $links = [];
        foreach ($this->hyperMediaProviders as $provider) {
            if ($monitoringResource['type'] === $provider->getType()) {
                $links = [
                    'uris' => $provider->generateUris($monitoringResource, $contact),
                    'endpoints' => $provider->generateEndpoints($monitoringResource)
                ];
            }
        }

        return $links;
    }

    /**
     * @param iterable<HyperMediaProviderInterface> $providers
     * @return void
     */
    public function setHyperMediaProviders(iterable $providers): void
    {
        $providers = $providers instanceof \Traversable
            ? iterator_to_array($providers)
            : $providers;

        if (count($providers) === 0) {
            throw new \InvalidArgumentException(
                _('You must at least add one hyper media provider')
            );
        }

        $this->hyperMediaProviders = $providers;
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
        if (!in_array($tab, static::ALLOWED_TABS)) {
            throw new MonitoringResourceException(sprintf(_('Cannot build uri to unknown tab : %s'), $tab));
        }

        return $this->buildListingUri([
            'details' => json_encode([
                'type' => MonitoringResource::TYPE_HOST,
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
        if (!in_array($tab, static::ALLOWED_TABS)) {
            throw new MonitoringResourceException(sprintf(_('Cannot build uri to unknown tab : %s'), $tab));
        }

        return $this->buildListingUri([
            'details' => json_encode([
                'parentType' => MonitoringResource::TYPE_HOST,
                'parentId' => $hostId,
                'type' => MonitoringResource::TYPE_SERVICE,
                'id' => $serviceId,
                'tab' => $tab,
                'uuid' => 's' . $serviceId
            ]),
        ]);
    }

    /**
     * Build uri to access listing page of resources with specific parameters
     *
     * @param array<string, mixed> $parameters
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
