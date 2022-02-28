<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Infrastructure\RealTime\Api\Hypermedia;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Core\Application\RealTime\UseCase\FindService\FindServiceResponse;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaProviderTrait;

class ServiceHypermediaProvider implements HypermediaProviderInterface
{
    use HypermediaProviderTrait;

    public const URI_CONFIGURATION = '/main.php?p=60201&o=c&service_id={serviceId}',
                 URI_EVENT_LOGS = '/main.php?p=20301&svc={hostId}_{serviceId}',
                 URI_REPORTING = '/main.php?p=30702&period=yesterday&start=&end=&host_id={hostId}&item={serviceId}',
                 URI_SERVICEGROUP_CONFIGURATION = '/main.php?p=60203&o=c&sg_id={servicegroupId}';

    public const ENDPOINT_SERVICE_TIMELINE = 'centreon_application_monitoring_gettimelinebyhostandservice',
                 ENDPOINT_PERFORMANCE_GRAPH = 'monitoring.metric.getServicePerformanceMetrics',
                 ENDPOINT_STATUS_GRAPH = 'monitoring.metric.getServiceStatusMetrics';

    /**
     * @param ContactInterface $contact
     * @param UrlGeneratorInterface $router
     */
    public function __construct(
        private ContactInterface $contact,
        protected UrlGeneratorInterface $router
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isValidFor(mixed $data): bool
    {
        return ($data instanceof FindServiceResponse);
    }

    /**
     * Create configuration redirection uri for Service resource
     *
     * @param array<string, int> $parameters
     * @return string|null
     */
    public function createForConfiguration(array $parameters): ?string
    {
        $configurationUri = null;
        if (
            $this->contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_WRITE)
            || $this->contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_READ)
            || $this->contact->isAdmin()
        ) {
            $configurationUri = $this->getBaseUri()
                . str_replace('{serviceId}', (string) $parameters['serviceId'], self::URI_CONFIGURATION);
        }
        return $configurationUri;
    }

    /**
     * Create reporting redirection uri for Service resource
     *
     * @param array<string, int> $parameters
     * @return string|null
     */
    public function createForReporting(array $parameters): ?string
    {
        $reportingUri = null;
        if (
            $this->contact->hasTopologyRole(Contact::ROLE_REPORTING_DASHBOARD_SERVICES)
            || $this->contact->isAdmin()
        ) {
            $reportingUri = str_replace('{hostId}', (string) $parameters['hostId'], self::URI_REPORTING);
            $reportingUri = str_replace('{serviceId}', (string) $parameters['serviceId'], (string) $reportingUri);
            $reportingUri = $this->getBaseUri() . $reportingUri;
        }
        return $reportingUri;
    }

    /**
     * Create event logs redirection uri for Service resource
     *
     * @param array<string, int> $parameters
     * @return string|null
     */
    public function createForEventLog(array $parameters): ?string
    {
        $eventLogsUri = null;
        if (
            $this->contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)
            || $this->contact->isAdmin()
        ) {
            $eventLogsUri = str_replace('{hostId}', (string) $parameters['hostId'], self::URI_EVENT_LOGS);
            $eventLogsUri = str_replace('{serviceId}', (string) $parameters['serviceId'], (string) $eventLogsUri);
            $eventLogsUri = $this->getBaseUri() . $eventLogsUri;
        }
        return $eventLogsUri;
    }

    /**
     * Create Timeline endpoint URI for the Service resource
     *
     * @param array<string, int> $parameters
     * @return string
     */
    public function createForTimelineEndpoint(array $parameters): string
    {
        return $this->router->generate(self::ENDPOINT_SERVICE_TIMELINE, $parameters);
    }

    /**
     * Create Status Graph endpoint URI for the Service resource
     *
     * @param array<string, int> $parameters
     * @return string
     */
    public function createForStatusGraphEndpoint(array $parameters): string
    {
        return $this->router->generate(self::ENDPOINT_STATUS_GRAPH, $parameters);
    }

    /**
     * Create Performance Data endpoint URI for the Service resource
     *
     * @param array<string, int> $parameters
     * @return string
     */
    public function createForPerformanceDataEndpoint(array $parameters): string
    {
        return $this->router->generate(self::ENDPOINT_PERFORMANCE_GRAPH, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function createEndpoints(mixed $response): array
    {
        $parameters = [
            'hostId' => $response->hostId,
            'serviceId' => $response->id,
        ];
        return [
            'timeline' => $this->createForTimelineEndpoint($parameters),
            'status_graph' => $this->createForStatusGraphEndpoint($parameters),
            'performance_graph' => $response->hasGraphData ? $this->createForPerformanceDataEndpoint($parameters) : null
        ];
    }

    /**
     * @inheritDoc
     */
    public function createInternalUris(mixed $response): array
    {
        $parameters = [
            'hostId' => $response->hostId,
            'serviceId' => $response->id,
        ];
        return [
            'configuration' => $this->createForConfiguration($parameters),
            'logs' => $this->createForEventLog($parameters),
            'reporting' => $this->createForReporting($parameters)
        ];
    }

    /**
     * Create servicegroup configuration redirection uri
     *
     * @param array<string, int> $parameters
     * @return string|null
     */
    public function createForGroup(array $parameters): ?string
    {
        return (
            $this->contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_SERVICE_GROUPS_READ_WRITE)
            || $this->contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_SERVICE_GROUPS_READ)
            || $this->contact->isAdmin()
        )
        ? $this->getBaseUri() . str_replace(
            '{servicegroupId}',
            (string) $parameters['servicegroupId'],
            self::URI_SERVICEGROUP_CONFIGURATION
        )
        : null;
    }

    /**
     * @inheritDoc
     */
    public function createInternalGroupsUri(mixed $response): array
    {
        return array_map(
            fn (array $group) => [
                'id' => $group['id'],
                'name' => $group['name'],
                'configuration_uri' => $this->createForGroup(['servicegroupId' => $group['id']])
            ],
            $response->servicegroups
        );
    }
}
